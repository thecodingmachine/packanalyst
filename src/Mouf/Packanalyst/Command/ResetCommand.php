<?php

namespace Mouf\Packanalyst\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * @author david
 */
class ResetCommand extends Command
{
    private $gitConfig;
    private $repos;
    private $downloadManager;

    protected function configure()
    {
        $this
            ->setName('reset')
            ->setDescription('Deletes all data.')
            ->setHelp(<<<EOT
The <info>reset</info> command deletes all data from the MongoDB and the ElasticSearch database. Use with caution! It also creates the MongoDB collections with the indexes.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $itemDao = \Mouf::getItemDao();
        $packageDao = \Mouf::getPackageDao();
        $itemDao->drop();
        $itemDao->createIndex();
        $packageDao->drop();
        $packageDao->createIndex();

        //$fetchDataService = \Mouf::getFetchDataService();
        //$fetchDataService->reset();
        $elasticSearchService = \Mouf::getElasticSearchService();
        $elasticSearchService->deleteIndex();
        $elasticSearchService->createIndex();
    }
}
