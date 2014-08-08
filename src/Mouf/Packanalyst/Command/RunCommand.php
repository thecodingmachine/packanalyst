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
class RunCommand extends Command
{
    private $gitConfig;
    private $repos;
    private $downloadManager;
    
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Runs Packanalyst update.')
            ->setDefinition(array(
            		new InputOption('package', null, InputOption::VALUE_REQUIRED, 'Name of the package to analyze'),
            		new InputOption('retry', null, InputOption::VALUE_NONE, 'Retry packages previously in error'),
            		new InputOption('force', null, InputOption::VALUE_NONE, 'Forces packages to update even if the package has not been updated'),
            ))
            /*->setDefinition(array(
                new InputOption('name', null, InputOption::VALUE_REQUIRED, 'Name of the package'),
                new InputOption('description', null, InputOption::VALUE_REQUIRED, 'Description of package'),
                new InputOption('author', null, InputOption::VALUE_REQUIRED, 'Author name of package'),
                // new InputOption('version', null, InputOption::VALUE_NONE, 'Version of package'),
                new InputOption('homepage', null, InputOption::VALUE_REQUIRED, 'Homepage of package'),
                new InputOption('require', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Package to require with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0 or "foo/bar 1.0.0"'),
                new InputOption('require-dev', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Package to require for development with a version constraint, e.g. foo/bar:1.0.0 or foo/bar=1.0.0 or "foo/bar 1.0.0"'),
                new InputOption('stability', 's', InputOption::VALUE_REQUIRED, 'Minimum stability (empty or one of: '.implode(', ', array_keys(BasePackage::$stabilities)).')'),
                new InputOption('license', 'l', InputOption::VALUE_REQUIRED, 'License of package'),
            ))*/
            ->setHelp(<<<EOT
The <info>run</info> command loads all new packages from Composer and uploads them in database.
EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	
    	\Mouf::getDownloadLock()->acquireLock();
    	
    	$fetchDataService = \Mouf::getFetchDataService();
    	$fetchDataService->setDownloadManager($this->getDownloadManager());
    	$fetchDataService->setPackagistRepository($this->getPackagistRepository());
    	
    	$package = $input->getOption('package');
    	$retry = $input->getOption('retry');
    	$force = $input->getOption('force');
    	
    	if ($package) {
    		$fetchDataService->setForcedPackage($package);
    	}
    	if ($retry) {
    		$fetchDataService->setRetryOnError(true);
    	}
    	if ($force) {
    		$fetchDataService->setForce(true);
    	}
    	
    	$fetchDataService->run();
    }

    /**
     * 
     * @return ComposerRepository
     */
    private function getPackagistRepository() {
    	if (!$this->repos) {
    		$this->repos = Factory::createDefaultRepositories($this->getIO());
    	}
    	return $this->repos['packagist'];
    }
    
    private function getDownloadManager() {
    	
    	if (!$this->downloadManager) {
	    	$config = Factory::createConfig();
	    	$factory = new Factory;
	    	 
	    	$this->downloadManager = $factory->createDownloadManager($this->getIO(), $config);
    	}
    	return $this->downloadManager;
    }
}
