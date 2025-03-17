<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Command;

use Dbp\Relay\NexusBundle\Typesense\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Typesense\Client;

class GenerateActivitiesCommand extends Command
{
    private array $urls;
    private Client $client;

    public function __construct()
    {
        parent::__construct();

        $connection = new Connection('apikey','localhost', 8108, 'http');
        $this->client = $connection->getClient();

        // TODO
        $this->urls = [
            'https://dbp-dev.tugraz.at/apps/activity-showcase/dbp-activity-showcase.topic.metadata.json',
            'https://esign.tugraz.at/dbp-signature.topic.metadata.json',
            'https://dbp-demo.tugraz.at/tugapps/formalize/dbp-formalize.topic.metadata.json',
        ];
    }

    protected function configure(): void
    {
        $this->setName('dbp:relay:nexus:generate:activities');
        $this->setDescription('Read metadata from frontend repositories and feed the search engine.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [];

        $output->writeln('<info>Input URLs</info>');
        foreach ($this->urls as $url) {
            $output->writeln($url);
            $topicJson = file_get_contents($url);
            try {
                $topic = json_decode($topicJson, true, 32, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $output->error($e->getMessage());
                continue;
            }
            // $output->writeln(print_r($topic, true));
            // $output->writeln($topic['name']['en']);
            // $output->writeln($topic['short_name']['en']);
            // $output->writeln($topic['description']['en']);
            // $output->writeln($topic['routing_name']);
            $last = self::last($url);
            foreach ($topic['activities'] as $a) {
                // $output->writeln(print_r($a, true));

                $activityUrl = str_replace($last, $a['path'], $url);
                // $output->writeln($activityUrl);

                $activityJson = file_get_contents($activityUrl);
                try {
                    $activity = json_decode($activityJson, true, 32, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    $output->error($e->getMessage());
                    continue;
                }
                // $output->writeln($activity['name']['en']);
                // $output->writeln($activity['short_name']['en']);
                // $output->writeln($activity['description']['en']);
                // $output->writeln($activity['element']);
                // $output->writeln($activity['module_src']);
                // $output->writeln($activity['routing_name']);
                // $output->writeln($activity['subscribe']);
                // $output->writeln($activity['required_roles'] ?? '<no-roles-required/>');

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

        $output->writeln('<info>document to upsert in typesense</info>');
        $output->writeln(json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $collectionName = 'nexus-' . date('YmdHis');
        $fields = '';
        $sortFieldName = '';
        $this->client->collections->create(
            [
                'name' => $collectionName,
                'fields' => $fields,
                'default_sorting_field' => $sortFieldName,
            ]
        );

        $info = $this->client->collections[$collectionName]->retrieve();

        //echo print_r($info, true) . "\n\n";
        $output->writeln("name:  {$info['name']}");
        $output->writeln("count: {$info['num_documents']}");

        return 0;
    }

    private static function last(string $url): string
    {
        $parts = explode('/', $url);
        $parts = array_reverse($parts);

        return $parts[0] ?? '';
    }
}
