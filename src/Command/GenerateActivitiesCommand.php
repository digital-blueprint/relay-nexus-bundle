<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Command;

use Dbp\Relay\NexusBundle\Service\ConfigurationService;
use Dbp\Relay\NexusBundle\Typesense\Connection;
use Http\Client\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Typesense\Client;
use Typesense\Exceptions\TypesenseClientError;

class GenerateActivitiesCommand extends Command
{
    private array $urls;
    private Client $client;
    private string $aliasName;

    public function __construct(ConfigurationService $config)
    {
        parent::__construct();

        $connection = new Connection(
            $config->getTypesenseApiKey(),
            $config->getTypesenseHost(),
            $config->getTypesensePort(),
            $config->getTypesenseProt()
        );
        $this->client = $connection->getClient();

        $this->urls = $config->getTopics();

        $this->aliasName = $config->getAliasName();
    }

    protected function configure(): void
    {
        $this->setName('dbp:relay:nexus:generate:activities');
        $this->setDescription('Read metadata from frontend repositories and feed the search engine.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbose = !$input->getOption('quiet');
        $data = [];

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
                    $output->error($e->getMessage());
                    continue;
                }

                $data[] = [
                    'activityName' => $activity['name']['en'],
                    'activityPath' => str_replace('.metadata.json', '', $a['path']),
                    'activityDescription' => $activity['description']['en'],
                    'activityRoutingName' => $activity['routing_name'],
                    'activityModuleSrc' => $activity['module_src'],
                    'activityTag' => ['pdf', 'signature'],
                    'activityIcon' => $activity['routing_name'].'-icon',
                ];
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

                return self::FAILURE;
            }
        } else {
            $output->writeln('<error>No documents to upsert</error>');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Remove collections, which names start with self::collectionPrefix(), but keep at least some.
     *
     * @param int $keep number of collections to keep
     * @return int number of collections deleted
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
            sort($nexusCollectionNames);
            foreach ($nexusCollectionNames as $index => $name) {
                if ($keep <= $index) {
                    $this->client->collections[$name]->delete();
                    ++$removed;
                }
            }
        }

        return $removed;
    }

    /**
     * Get the last part of the URL when separated by slashes.
     *
     * @param string $url
     * @return string
     */
    private static function last(string $url): string
    {
        $parts = explode('/', $url);
        $parts = array_reverse($parts);

        return $parts[0] ?? '';
    }

    /**
     * Get the collection prefix for all collections created for this bundle.
     *
     * @return string
     */
    private static function collectionPrefix(): string
    {
        return 'nexus--';
    }
}
