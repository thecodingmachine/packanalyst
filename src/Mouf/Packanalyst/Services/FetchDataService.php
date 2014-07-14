<?php
namespace Mouf\Packanalyst\Services;

use HireVoice\Neo4j\EntityManager;
use Composer\Repository\ComposerRepository;
use Composer\Downloader\DownloadManager;
use Mouf\Packanalyst\ClassesDetector;
use Composer\Package\Package;
use Mouf\Packanalyst\Repositories\PackageRepository;
use Mouf\Packanalyst\Repositories\PackageVersionRepository;
use Psr\Log\LoggerInterface;
use Composer\Package\AliasPackage;
use Composer\Util\Filesystem;
use Mouf\Packanalyst\Dao\ItemDao;
use Mouf\Packanalyst\Dao\PackageDao;
/**
 * This package is in charge of loading data from all packagist packages.
 * This is the entry point of the RunCommand. 
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class FetchDataService
{
	
	/**
	 * @var ComposerRepository
	 */
	private $packagistRepository;
	
	private $logger;
	private $itemDao;
	private $packageDao;
	
	/**
	 * @var DownloadManager
	 */
	private $downloadManager;
	
	public function __construct(ClassesDetector $classesDetector, LoggerInterface $logger, ItemDao $itemDao, PackageDao $packageDao) {
		$this->classesDetector = $classesDetector;
		$this->logger = $logger;
		$this->itemDao = $itemDao;
		$this->packageDao = $packageDao;
	}
	
	/**
	 *
	 * @param ComposerRepository $packagistRepository        	
	 */
	public function setPackagistRepository(ComposerRepository $packagistRepository) {
		$this->packagistRepository = $packagistRepository;
		return $this;
	}
	
	/**
	 *
	 * @param DownloadManager $downloadManager        	
	 */
	public function setDownloadManager(DownloadManager $downloadManager) {
		$this->downloadManager = $downloadManager;
		return $this;
	}
	
	/**
	 * Runs the script: connect to packagist, download everything it can!
	 */
	public function run() {		
		// TODO: each package is there twice. Find why.
		$filesystem = new Filesystem();
		
		if (file_exists(DOWNLOAD_DIR."/last_analyzed_package")) {
			$lastAnalyzedPackage = file_get_contents(DOWNLOAD_DIR."/last_analyzed_package");
		} else {
			$lastAnalyzedPackage = "";
		}
		
		$providerNames = $this->packagistRepository->getProviderNames();
		
		// If analyzis is over, let's start from the beginning again.
		if ($lastAnalyzedPackage == $providerNames[count($providerNames)-1]) {
			$lastAnalyzedPackage = '';
		}
		
		foreach ($providerNames as $packageName) {
			
			if ($packageName <= $lastAnalyzedPackage) {
				continue;
			}
			
			//if ($packageName != '10up/wp_mock') continue;
			//var_dump($packagistRepo->findPackages($packageName));
			$packages = $this->packagistRepository->findPackages($packageName);
			
			$importantPackages = $this->getImportantVersions($packages);
			
			// DELETE PACKAGES VERSION BEFORE REINSERTION!
			
			// Only delete packages that are not important anymore.
			$notImportantPackages = array_diff($packages, $importantPackages);
			foreach ($notImportantPackages as $notImportantPackage) {
				$this->logger->info("Removing {packageName} {version}. A newer package is available.", array(
						"packageName"=>$notImportantPackage->getPrettyName(),
						"version"=>$notImportantPackage->getPrettyVersion()
				));
				
				$this->itemDao->deletePackage($notImportantPackage->getName(), $notImportantPackage->getPrettyVersion());
				$this->packageDao->deletePackage($notImportantPackage->getName(), $notImportantPackage->getPrettyVersion());
				
				$downloadPath = DOWNLOAD_DIR."/".$notImportantPackage->getName()."/".$notImportantPackage->getPrettyVersion();
				$filesystem->removeDirectory($downloadPath);
			}
			
			
			foreach ($importantPackages as $package) {
				/* @var $package PackageInterface */
				try {
					// Let's reset to null (in case an exception happens on first line).
					$packageVersionEntity = null;
					
					// Let's get the update date of each version and let's compare it with the one we stored.
					$packageVersion = $this->packageDao->get($package->getName(), $package->getPrettyVersion());
					
					if ($packageVersion && $packageVersion['releaseDate']->sec == $package->getReleaseDate()->getTimestamp()) {
						$this->logger->debug("{packageName} {version} has not moved since last run. Ignoring.", array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion()
						));
						continue;
					}
					
					$this->itemDao->deletePackage($package->getName(), $package->getPrettyVersion());
					$this->packageDao->deletePackage($package->getName(), $package->getPrettyVersion());
				
					$this->logger->info("Downloading {packageName} {version}", array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion()
						));
					//var_dump($package->getDistUrls());
					//var_dump($package->getSourceUrls());
					$downloadPath = DOWNLOAD_DIR."/".$package->getName()."/".$package->getPrettyVersion();
					
					$this->downloadManager->download($package, $downloadPath);
					
					$packageVersion = $this->packageDao->createOrUpdatePackage($package);
					
	    			$this->classesDetector->storePackage($downloadPath, $packageVersion);
	    			$packageVersion['onError'] = false;
	    			$packageVersion['errorMsg'] = '';
				} catch (\Exception $e) {
					if (!$packageVersion) {
						$packageVersion = $this->packageDao->createOrUpdatePackage($package);
					}
					$this->logger->error("Package {packageName} {version} failed to download. Exception: ".$e->getMessage(),
						array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion(),
								"exception"=>$e
						)
					);
	    			$packageVersion['onError'] = true;
	    			$packageVersion['errorMsg'] = $e->getMessage()."\n".$e->getTraceAsString();
				}
				$this->packageDao->save($packageVersion);
			}
			
			// Let's write the name of the last package we analyzed/
			// We will use it to start again from next package.
			file_put_contents(DOWNLOAD_DIR."/last_analyzed_package", $packageName);
		}
		 
		 
		
		//var_dump("Nb packages: ".count($repositories->getPackages()));
		 
	}
	
	/**
	 * From an array of packages:
	 * Analyze the list of packages.
	 * Select: "master" + the latest version of each major version.
	 * 
	 * @param Package[] $packages
	 * @return Package[]
	 */
	private function getImportantVersions(array $packages) {

		$indexedByVersion = [];
		foreach ($packages as $package) {
			// Let's ignore aliases.
			if ($package instanceof AliasPackage) {
				continue;
			}
			
			$indexedByVersion[$package->getPrettyVersion()] = $package;
		}

		uksort($indexedByVersion, "version_compare");
		$indexedByVersion = array_reverse($indexedByVersion);
		
		$keptPackages = array();
		
		$lastMajorVersion = -1;
		
		foreach ($indexedByVersion as $version => $package) {
			if ($version == 'dev-master') {
				$keptPackages[] = $package;
				continue;
			}
			
			$versionItems = explode('.',$version);
			if (isset($versionItems[0]) && is_numeric($versionItems[0]) && $versionItems[0] != $lastMajorVersion) {
				$lastMajorVersion = $versionItems[0];
				$keptPackages[] = $package;
			}
		}
		
		// If no package has been kept, let's grab all of them...
		if (empty($keptPackages)) {
			$keptPackages = $packages;
		}

		return $keptPackages;
	}
	
}
