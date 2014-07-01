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
		
		$entity = $this->getOneByName($name);
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
	 * @return ItemNameEntity
	 */
	public function getOneByName($name) {
		// FIXME: All the options below do not work. Find why:
		/*$entity = \Mouf::getItemRepository()->findOneBy(["name"=>$q]);
		$entity = $this->itemNameRepository->findOneBy(["name"=>$q]);
		$entity = $this->itemNameRepository->findOneBy(["itemNameIdx"=>$q]);
		$entity = $this->itemNameRepository->getIndex()->findOne('itemNameIdx', $q);
		$entity = $this->itemNameRepository->getIndex()->findOne('name', $q);*/
		
		$result = $this->getEntityManager()->createCypherQuery()
		->match('(i:ItemName { name: "'.addslashes($name).'" })')
		->end('i')
		->getList();
	
		if (count($result)) {
			return $result[0];
		} else {
			return null;
		}
	}
	
	/**
	 * Finds a graph of dependency from a, ItemName
	 * 
	 * @param $name string
	 * @return \Everyman\Neo4j\Query\ResultSet
	 */
	public function findItemGraph($name) {
		
		//$this->getEntityManager()->getClient()->
		
		
		$queryString = "START n=node:itemNameIdx(name=\"".addslashes($name)."\") OPTIONAL MATCH n<-[r:`is-a-reverse`|`inherits`*]-(x:Item)-[r2:`belongs-to`]->(y:PackageVersion)
		  RETURN n,r,x,r2,y";
		
		$query = new \Everyman\Neo4j\Cypher\Query($this->getEntityManager()->getClient(), $queryString);
		$result = $query->getResultSet();
		return $result;
		
		$list = $this->getEntityManager()->createCypherQuery()
			->startWithNode("n", $name)
			->optionalMatch('n<-[r:`is-a-reverse`|`inherits`*]-(x:Item)-[:`belongs-to`]->(y:PackageVersion)')
			//->end("n,r,x,y")
			->end("x")
			->limit(2000)
			->getList();
		
		return $list;
	}
}
