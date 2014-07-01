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
/**
 * This package is in charge of loading data from all packagist packages.
 * This is the entry point of the RunCommand. 
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class FetchDataService
{
	private $entityManager;
	
	/**
	 * @var ComposerRepository
	 */
	private $packagistRepository;
	
	private $packageRepository;
	private $packageVersionRepository;
	private $logger;
	
	/**
	 * @var DownloadManager
	 */
	private $downloadManager;
	
	public function __construct(EntityManager $entityManager, ClassesDetector $classesDetector, PackageRepository $packageRepository, PackageVersionRepository $packageVersionRepository, LoggerInterface $logger) {
		$this->entityManager = $entityManager;
		$this->classesDetector = $classesDetector;
		$this->packageRepository = $packageRepository;
		$this->packageVersionRepository = $packageVersionRepository;
		$this->logger = $logger;
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
		
		$i=0;
		foreach ($this->packagistRepository->getProviderNames() as $packageName) {
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
				
				$this->packageVersionRepository->deletePackageVersion($notImportantPackage->getName(), $notImportantPackage->getPrettyVersion());
				$downloadPath = DOWNLOAD_DIR."/".$package->getName()."/".$package->getPrettyVersion();
				$filesystem->removeDirectory($downloadPath);
			}
			
			
			foreach ($importantPackages as $package) {
				/* @var $package PackageInterface */
				try {
					// Let's reset to null (in case an exception happens on first line).
					$packageVersionEntity = null;
					
					// Let's get the update date of each version and let's compare it with the one we stored.
					$packageVersionEntity = $this->packageVersionRepository->findPackage($package);
					if ($packageVersionEntity && $packageVersionEntity->getReleaseDate() == $package->getReleaseDate()) {
						$this->logger->debug("{packageName} {version} has not moved since last run. Ignoring.", array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion()
						));
						continue;
					}
					$this->packageVersionRepository->deletePackageVersion($package->getName(), $package->getPrettyVersion());
					
					
					$this->logger->info("Downloading {packageName} {version}", array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion()
						));
					//var_dump($package->getDistUrls());
					//var_dump($package->getSourceUrls());
					$downloadPath = DOWNLOAD_DIR."/".$package->getName()."/".$package->getPrettyVersion();
					
					$this->downloadManager->download($package, $downloadPath);
					
					$packageVersionEntity = $this->packageVersionRepository->findOrCreatePackageVersion($package);
						
	    			$this->classesDetector->storePackage($downloadPath, $packageVersionEntity);
	    			$packageVersionEntity->setOnError(false);
	    			$packageVersionEntity->setErrorMsg("");
				} catch (\Exception $e) {
					if (!$packageVersionEntity) {
						$packageVersionEntity = $this->packageVersionRepository->findOrCreatePackageVersion($package);
					}
					$this->logger->error("Package {packageName} {version} failed to download. Exception: ".$e->getMessage(),
						array(
								"packageName"=>$package->getPrettyName(),
								"version"=>$package->getPrettyVersion(),
								"exception"=>$e
						)
					);
					$packageVersionEntity->setOnError(true);
					$packageVersionEntity->setErrorMsg($e->getMessage()."\n".$e->getTraceAsString());
				}
			}
			
			$this->entityManager->flush();
			//exit();
				
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
	
	/**
	 * Removes all data from Neo4j!
	 */
	public function reset() {
		$this->entityManager->cypherQuery("START n = node(*) 
OPTIONAL MATCH n-[r]-() 
DELETE n, r;");
	}
}
