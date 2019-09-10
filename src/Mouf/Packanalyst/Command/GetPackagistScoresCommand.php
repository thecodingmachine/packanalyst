<?php

namespace Mouf\Packanalyst\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * @author david
 */
class GetPackagistScoresCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('get-scores')
            ->setDescription('Retrieve scores of all packages on Packagist.')
            ->setHelp(<<<EOT
The <info>get-scores</info> command loads all packages scores from Packagist.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Mouf::getPackagistStatsLock()->acquireLock();
        $packagistScoreService = \Mouf::getPackagistScoreService();
        $packagistScoreService->updateAllScores();
    }
}
