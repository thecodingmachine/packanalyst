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
    	$fetchDataService = \Mouf::getElasticSearchService();
    	$fetchDataService->reindexAll();
    }
}
