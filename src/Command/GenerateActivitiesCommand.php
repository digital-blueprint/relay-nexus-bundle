<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Command;

use Dbp\Relay\NexusBundle\Service\NexusSync;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateActivitiesCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private NexusSync $sync)
    {
        parent::__construct();
        $this->logger = new NullLogger();
    }

    protected function configure(): void
    {
        $this->setName('dbp:relay:nexus:generate:activities');
        $this->setDescription('Read metadata from frontend repositories and feed the search engine.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->sync->execute($input, $output);
    }
}
