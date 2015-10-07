<?php

namespace Mouf\Packanalyst\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\Command;

/**
 * @author david
 */
class ReindexCommand extends Command
{
    private $gitConfig;
    private $repos;
    private $downloadManager;

    protected function configure()
    {
        $this
            ->setName('reindex-el')
            ->setDescription('Reindexes all elastic search records.')
            ->setHelp(<<<EOT
The <info>reindex-el</info> command reindexes all elastic search records from the Neo4J database.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $elasticSearchService = \Mouf::getElasticSearchService();
        $elasticSearchService->reindexAll();
    }
}
