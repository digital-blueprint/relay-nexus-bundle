<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Service;

use Dbp\Relay\NexusBundle\Typesense\Connection;
use Http\Client\Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Typesense\Client;
use Typesense\Exceptions\TypesenseClientError;

class NexusSync implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $urls;
    private Client $client;
    private string $aliasName;

    private ConfigurationService $config;

    public function __construct(ConfigurationService $config)
    {
        $connection = new Connection(
            $config->getTypesenseApiUrl(),
            $config->getTypesenseApiKey()
        );
        $this->client = $connection->getClient();

        $this->urls = $config->getTopics();

        $this->aliasName = $config->getAliasName();
        $this->config = $config;
        $this->logger = new NullLogger();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $verbose = !$input->getOption('quiet');
        $data = [];

        $this->updateProxyApiKey();

        if ($verbose) {
            $output->writeln('<info>Input URLs</info>');
        }
        foreach ($this->urls as $url) {
            if ($verbose) {
                $output->writeln($url);
            }
            $topicJson = file_get_contents($url);
            try {
                $topic = json_decode($topicJson, true, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $output->writeln('<error>'.$e->getMessage().'</error>');
                continue;
            }
            $last = self::last($url);
            foreach ($topic['activities'] as $a) {
                $activityUrl = str_replace($last, $a['path'], $url);
                $activityJson = file_get_contents($activityUrl);
                try {
                    $activity = json_decode($activityJson, true, 32, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $io->error($e->getMessage());
                    continue;
                }

                // Skip duplicate activities
                $activityName = $activity['name']['en'];
                $duplicate = array_filter($data, function ($item) use ($activityName) {
                    return $item['activityName'] === $activityName;
                });

                if (count($duplicate) < 1) {
                    $data[] = [
                        'activityName' => $activity['name']['en'],
                        'activityPath' => $activityUrl,
                        'activityDescription' => $activity['description']['en'],
                        'activityRoutingName' => $activity['routing_name'],
                        'activityModuleSrc' => $activity['module_src'],
                        'activityTag' => ['pdf', 'signature'],
                        'activityIcon' => $activity['routing_name'].'-icon',
                    ];
                }
            }
        }

        if (count($data) > 0) {
            if ($verbose) {
                $output->writeln('<info>Document to upsert</info>');
                $output->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            }

            $schema = json_decode(file_get_contents(__DIR__.'/../../data-definition/schema.json'), true, 16);

            $schema['name'] = $collectionName = self::collectionPrefix().date('Ymd-His');
            $this->client->collections->create($schema);
            $this->client->collections[$collectionName]->documents->import($data, ['action' => 'upsert']);

            $info = $this->client->collections[$collectionName]->retrieve();
            $availableDocuments = $info['num_documents'];

            if ($verbose) {
                $output->writeln('<info>Collection written</info>');
                // echo print_r($info, true) . "\n\n";
                $output->writeln("name:  {$info['name']}");
                $output->writeln("count: {$availableDocuments}");
            }
            if ($availableDocuments > 0) {
                $this->client->aliases->upsert($this->aliasName, ['collection_name' => $collectionName]);
                if ($verbose) {
                    $output->writeln('<info>Aliases written</info>');
                    $output->writeln("alias {$this->aliasName} for $collectionName");
                }
                $removed = $this->removeOldCollections();
                if ($verbose) {
                    $output->writeln('<info>Remove old collections</info>');
                    $output->writeln("{$removed} old collections removed");
                }
            } else {
                $output->writeln('<error>Upsert documents failed.</error>');

                return Command::FAILURE;
            }
        } else {
            $output->writeln('<error>No documents to upsert</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Remove collections, which names start with self::collectionPrefix(), but keep at least some.
     *
     * @param int $keep number of collections to keep
     *
     * @return int number of collections deleted
     *
     * @throws Exception
     * @throws TypesenseClientError
     */
    private function removeOldCollections(int $keep = 3): int
    {
        $removed = 0;
        $nexusCollectionNames = [];
        $collections = $this->client->collections->retrieve();
        foreach ($collections as $collection) {
            $name = $collection['name'];
            echo "name: $name\n";
            if (str_starts_with($name, self::collectionPrefix())) {
                $nexusCollectionNames[] = $name;
            }
        }
        if (count($nexusCollectionNames) > 0) {
            rsort($nexusCollectionNames);
            foreach ($nexusCollectionNames as $index => $name) {
                if ($index >= $keep) {
                    $this->client->collections[$name]->delete();
                    ++$removed;
                }
            }
        }

        return $removed;
    }

    /**
     * Get the last part of the URL when separated by slashes.
     */
    private static function last(string $url): string
    {
        $parts = explode('/', $url);
        $parts = array_reverse($parts);

        return $parts[0];
    }

    /**
     * Get the collection prefix for all collections created for this bundle.
     */
    private static function collectionPrefix(): string
    {
        return 'nexus--';
    }

    private function updateProxyApiKey(): void
    {
        $aliasName = $this->config->getAliasName();
        $schema = [
            'description' => 'nexus read only proxy key',
            'actions' => [
                // allow all read-only operations
                'documents:search',
                'documents:get',
                'documents:export',
            ],
            'collections' => [$aliasName],
            'value' => $this->config->getTypesenseProxyApiKey(),
        ];

        $this->logger->info('Re-creating read-only key if needed');
        $client = $this->client;
        $keys = $client->keys->retrieve();
        $foundId = null;
        foreach ($keys['keys'] as $key) {
            if (in_array($aliasName, $key['collections'], true)) {
                if ($key['description'] === $schema['description']
                    && $key['actions'] === $schema['actions']
                    && $key['collections'] === $schema['collections']
                    && str_starts_with($schema['value'], $key['value_prefix'])) {
                    $this->logger->info('Found existing matching key '.$key['id']);
                    $foundId = $key['id'];
                    break;
                } else {
                    $this->logger->info('Deleting outdated key '.$key['id']);
                    $client->keys[$key['id']]->delete();
                }
            }
        }

        if ($foundId === null) {
            $this->logger->info('No existing key found, creating a new one');
            $key = $client->keys->create($schema);
            $this->logger->info('Created new key '.$key['id']);
        }
    }
}
