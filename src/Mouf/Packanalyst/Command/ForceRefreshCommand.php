<?php

namespace Mouf\Packanalyst\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Composer\Command\Command;

/**
 * @author david
 */
class ForceRefreshCommand extends Command
{
    private $gitConfig;
    private $repos;
    private $downloadManager;

    protected function configure()
    {
        $this
            ->setName('force-refresh')
            ->setDescription('Force refreshing all packages from packages.')
            ->setHelp(<<<EOT
The <info>force-refresh</info> command sets a flag on all packages for refreshing them.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $itemDao = \Mouf::getItemDao();
        $packageDao = \Mouf::getPackageDao();
        $itemDao->createIndex();
        $packageDao->createIndex();

        $packageDao->refreshAllPackages();
    }
}
