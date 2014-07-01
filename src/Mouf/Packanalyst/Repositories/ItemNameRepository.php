<?php
namespace Mouf\Packanalyst\Repositories;

use HireVoice\Neo4j\Repository as BaseRepository;
use Mouf\Packanalyst\Entities\PackageEntity;
use Mouf\Packanalyst\Entities\ItemNameEntity;

class ItemNameRepository extends BaseRepository
{
	// TODO: a local cache (otherwise, we must flush each time we insert!)
	private $itemNames = [];
	
	function findOrCreateItemName($name)
	{
		if (isset($this->itemNames[$name])) {
			return $this->itemNames[$name];
		}
		
		$entity = $this->findOneBy(array('name'=>$name));
		if ($entity == null) {
			$entity = new ItemNameEntity();
			$entity->setName($name);
			$this->getEntityManager()->persist($entity);
			
			// We need to flush to get an ID, so we can store everything in ElasticSearch
			// Todo: improve this with a service that flushes in both ElasticSearch and Neo4J
			$this->getEntityManager()->flush();
			\Mouf::getElasticSearchService()->storeItemName($entity);
		}
		$this->itemNames[$name] = $entity;
		return $entity;
	}
	
	/**
	 * Applies function $callback for each item passed in parameter.
	 * 
	 * @param callable $callback
	 */
	public function applyOnAllItemName(callable $callback) {
		
		$BATCH_SIZE = 1000;
		
		// Let's retrieve everything by batch of 1000.
		$count = $this->getEntityManager()->createCypherQuery()
		->match('(i:ItemName)')
		->end('count(*) as cnt')
		->getResult();
		
		$count = $count[0]['cnt'];
		
		for ($i=0; $i<ceil($count/$BATCH_SIZE); $i++) {
			$list = $this->getEntityManager()->createCypherQuery()
			->match('(i:ItemName)')
			->end('i')
			->skip($BATCH_SIZE*$i)
			->limit($BATCH_SIZE)
			->getList();
			
			foreach ($list as $item) {
				$callback($item);
			}
		}
	}
	
	/**
	 * Finds a graph of dependency from a, ItemName
	 * 
	 * @param ItemNameEntity $name
	 */
	public function findItemGraph(ItemNameEntity $name) {
		
		$list = $this->getEntityManager()->createCypherQuery()
			->startWithNode(n, $name)
			->optionalMatch('n<-[r:`is-a-reverse`|`inherits`*]-(x:Item)-[:`belongs-to`]->(y:PackageVersion)')
			->end("n,r,x,y")
			->limit(200)
			->getList();
		
		return $list;
	}
}
