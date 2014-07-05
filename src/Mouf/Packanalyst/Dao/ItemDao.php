<?php
namespace Mouf\Packanalyst\Dao;


class ItemDao
{
	const TYPE_CLASS = "class";
	const TYPE_INTERFACE = "interface";
	const TYPE_TRAIT = "trait";
	const TYPE_FUNCTION = "function";
	
	
	/**
	 * @var \MongoCollection $collection
	 */
	private $collection; 
	
	public function __construct(\MongoCollection $collection) {
		$this->collection = $collection;
	}
	
	public function createIndex() {
		$this->collection->createIndex([
			"inherits" => 1
		]);
		$this->collection->createIndex([
			"name" => 1
		]);
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
	
	public function save($item) {
		$this->collection->save($item);
	}
	
}
