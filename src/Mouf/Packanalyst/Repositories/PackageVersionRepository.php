<?php
namespace Mouf\Packanalyst\Repositories;

use HireVoice\Neo4j\Repository as BaseRepository;
use Mouf\Packanalyst\Entities\PackageEntity;
use Composer\Package\Package;
use Mouf\Packanalyst\Entities\PackageVersionEntity;

class PackageVersionRepository extends BaseRepository
{
	function findOrCreatePackageVersion(Package $package)
	{
		// TODOOOOOOOOOOOOOOOOOOOO:
		// CachedReader: ArrayCacheReader => pas glop.
		// Utiliser ApcCacheReader, puis FileCacheReader sauf si DEBUG=true => arrayCacheReader
		// Intégrer le cache avec Mouf pour bénéficier du bouton purge cache.
		
		$packageVersionEntity = $this->findOneBy(array('packageName' => $package->getName(), 'version' => $package->getPrettyVersion()));
		if ($packageVersionEntity == null) {
			$packageVersionEntity = new PackageVersionEntity();
			$packageVersionEntity->setPackageName($package->getName());
			$packageVersionEntity->setVersion($package->getPrettyVersion());
			$packageDao = \Mouf::getPackageRepository();
			$packageEntity = $packageDao->findOrCreatePackage($package->getName());
			$packageVersionEntity->setPackage($packageEntity);
		}
		$packageVersionEntity->setReleaseDate($package->getReleaseDate());
		$this->getEntityManager()->persist($packageVersionEntity);
		$this->getEntityManager()->flush();
		return $packageVersionEntity;
	}
	
	/**
	 * 
	 * @param Package $package
	 * @return PackageVersionEntity
	 */
	function findPackage(Package $package) {
		return $this->findOneBy(array('packageName' => $package->getName(), 'version' => $package->getPrettyVersion()));
	}
	
	/**
	 * Deletes all data related to this package.
	 *
	 * @param PackageEntity $package
	 */
	public function deletePackageVersion($packageName, $version) {
		$packageVersionEntity = $this->findOneBy(array('packageName' => $packageName, 'version' => $version));
		//var_dump($packageVersionEntity); exit;
		if ($packageVersionEntity) {
			$list = $this->getEntityManager()->createCypherQuery()
			->startWithNode('n', array($packageVersionEntity))
			->match('n-[r2]-(x:Item)-[r3]-(y:ItemName)')
			->end('n,x,y,r2,r3')
			->getList();
		
			if (count($list)) {
				foreach ($list as $entity) {
					$this->getEntityManager()->remove($entity);
				}
			} else {
				// If the package did not contain any class, the n node is not returned. Let's delete it.
				$this->getEntityManager()->remove($packageVersionEntity);
			}
			$this->getEntityManager()->flush();
		}
	}
}
