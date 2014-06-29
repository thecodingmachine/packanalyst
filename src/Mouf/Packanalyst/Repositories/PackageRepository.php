<?php
namespace Mouf\Packanalyst\Repositories;

use HireVoice\Neo4j\Repository as BaseRepository;
use Mouf\Packanalyst\Entities\PackageEntity;

class PackageRepository extends BaseRepository
{
	function findOrCreatePackage($name)
	{
		//$packageEntity = $this->findOneByPackageName($name);
		$node = $this->getIndex()->findOne('packageName', $name);
		if ($node) {
			return $this->getEntityManager()->load($node);
		} else {
			$packageEntity = new PackageEntity();
			$packageEntity->setPackageName($name);
			$this->getEntityManager()->persist($packageEntity);
		}
		return $packageEntity;
	}
	
	/**
	 * Deletes all data related to this package.
	 * 
	 * @param PackageEntity $package
	 */
	public function deleteRelatedPackageVersions(PackageEntity $package) {
		$list = $this->getEntityManager()->createCypherQuery()
			->startWithLookup('n', 'packageNameIdx', 'packageName', $package->getPackageName())
			->match('n-[r1]-(z:PackageVersion)-[r2]-(x:Item)-[r3]-(y:ItemName)')
			->end('z,x,y,r1,r2,r3')
			->getList();
		
		foreach ($list as $entity) {
			$this->getEntityManager()->remove($entity);
		}
		$this->getEntityManager()->flush();
	}
}
