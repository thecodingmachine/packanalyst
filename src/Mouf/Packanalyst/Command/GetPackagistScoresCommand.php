<?php
namespace Mouf\Packanalyst\Command;

use Composer\Json\JsonFile;
use Composer\Factory;
use Composer\Package\BasePackage;
use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Package\Version\VersionParser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;
use Composer\Command\Command;
use Composer\Repository\RepositoryInterface;
use Composer\Repository\ComposerRepository;
use Composer\Package\PackageInterface;
use Mouf\Packanalyst\ClassesDetector;

/**
 * 
 * @author david
 *
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
