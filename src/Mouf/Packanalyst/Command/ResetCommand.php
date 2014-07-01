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
The <info>reset</info> command deletes all data from the Neo4J and the ElasticSearch database. Use with caution!
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$fetchDataService = \Mouf::getFetchDataService();
    	$fetchDataService->reset();
    	$elasticSearchService = \Mouf::getElasticSearchService();
    	$elasticSearchService->deleteIndex();
    }
}
