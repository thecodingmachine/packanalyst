<?php
namespace Mouf\Packanalyst\Dao;


use Mouf\Packanalyst\Services\ElasticSearchService;
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
	
	private $elasticSearchService;
	
	public function __construct(\MongoCollection $collection, ElasticSearchService $elasticSearchService) {
		$this->collection = $collection;
		$this->elasticSearchService = $elasticSearchService;
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
		//$this->collection->save($item);
		$this->recomputeGlobalInherits($item);
		
		// Let's store all possible class names in ElasticSearch.
		$this->elasticSearchService->storeItemName($item['name']);
		if (isset($item['inherits'])) {
			foreach ($item['inherits'] as $itemName) {
				$this->elasticSearchService->storeItemName($itemName);
			}
		}
	}
	
	/**
	 * Returns the list of items whose name is $itemName.
	 * @param string $itemName
	 * @return array
	 */
	public function getItemsByName($itemName) {
		return $this->collection->find([ "name" => $itemName ]);
	}
	
	/**
	 * For the item passed in parameter, compute the "globalInherits" array from the "inherits" array
	 * and the "globalInherits" array of all inherited items, then, impact recursively all children
	 * implementing this item.  
	 * 
	 * @param array $item
	 * @param array $antiLoopList A list of already visited item names.
	 */
	protected function recomputeGlobalInherits(array $item, array $antiLoopList = array()) {
		
		// Let's prevent any infinite loops.
		if (isset($antiLoopList[$item['name']." ".$item['packageName']." ".$item['packageVersion']])) {
			return;
		}
		
		$inherits = isset($item['inherits'])?$item['inherits']:array();
		$globalInherits = $inherits;
		
		foreach ($inherits as $inheritedItemName) {
			foreach ($this->getItemsByName($inheritedItemName) as $parentItem) {
				$globalInherits = array_merge($globalInherits, isset($parentItem['globalInherits'])?$parentItem['globalInherits']:array());
			}
		}
		
		// Let's remove duplicates
		$globalInherits = array_keys(array_flip($globalInherits));
		
		$item['globalInherits'] = $globalInherits;
		
		$this->collection->save($item);
		
		$antiLoopList[$item['name']." ".$item['packageName']." ".$item['packageVersion']] = true;
		
		// Now, let's find the list of all items directly implementing this item
		// TODO: there COULD be an infinite loop!
		// FIXME: do something about it!!!!
		$children = $this->collection->find([ "inherits" => $item['name'] ]);
		foreach ($children as $child) {
			$this->recomputeGlobalInherits($child, $antiLoopList);
		}
	}
	
	/**
	 * Find the list of items that inherit in a way or another $itemName
	 * 
	 * @param string $itemName
	 */
	public function findItemsInheriting($itemName) {
		return $this->collection->find([ "globalInherits" => $itemName ]);
	}
}
