<?php
namespace Mouf\Packanalyst\Dao;


use Composer\Package\Package;
class PackageDao
{
	/**
	 * @var \MongoCollection $collection
	 */
	private $collection; 
	
	public function __construct(\MongoCollection $collection) {
		$this->collection = $collection;
	}
	
	public function createIndex() {
		$this->collection->createIndex([
			"packageName" => 1,
			"packageVersion" => 1
		]);
	}
	
	/**
	 * Drops the complete collection.
	 */
	public function drop() {
		$this->collection->drop();
	}
	
	/**
	 * Deletes all items relative to this package version.
	 * @param string $packageName
	 * @param string $packageVersion
	 */
	public function deletePackage($packageName, $packageVersion) {
		$this->collection->remove([
			"packageName" => $packageName,
			"packageVersion" => $packageVersion
		]);
	}
	
	/**
	 * Returns a package by name and version.
	 * @param string $packageName
	 * @param string $packageVersion
	 * @return array|null
	 */
	public function get($packageName, $packageVersion) {
		return $this->collection->findOne([
			"packageName" => $packageName,
			"packageVersion" => $packageVersion
		]);
	}

	/**
	 * Creates or update a package in MongoDB from the Package passed in parameter. 
	 * 
	 * @param Package $package
	 * @return array
	 */
	public function createOrUpdatePackage(Package $package) {
		$packageVersion = $this->get($package->getName(), $package->getPrettyVersion());
		
		if (!$packageVersion) {
			$packageVersion = [
				"packageName" => $package->getName(),
				"packageVersion" => $package->getPrettyVersion()		
			]; 
		}
		
		$packageVersion['releaseDate'] = new \MongoDate($package->getReleaseDate()->getTimestamp());
		$packageVersion['type'] = $package->getType();
		
		$this->collection->save($packageVersion);
		return $packageVersion;
	}
	
	public function save($packageVersion) {
		$this->collection->save($packageVersion);		
	}
}
