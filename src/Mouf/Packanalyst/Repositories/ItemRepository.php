<?php
namespace Mouf\Packanalyst\Repositories;

use HireVoice\Neo4j\Repository as BaseRepository;
use Mouf\Packanalyst\Entities\PackageEntity;
use Mouf\Packanalyst\Entities\ItemNameEntity;

class ItemRepository extends BaseRepository
{
	/**
	 * Applies function $callback for each item passed in parameter.
	 * 
	 * @param callable $callback
	 */
	public function applyOnAllItem(callable $callback) {
		
		$BATCH_SIZE = 1000;
		
		// Let's retrieve everything by batch of 1000.
		$count = $this->getEntityManager()->createCypherQuery()
		->match('(i:Item)')
		->end('count(*) as cnt')
		->getResult();
		
		$count = $count[0]['cnt'];
		
		for ($i=0; $i<ceil($count/$BATCH_SIZE); $i++) {
			$list = $this->getEntityManager()->createCypherQuery()
			->match('(i:Item)')
			->end('i')
			->skip($BATCH_SIZE*$i)
			->limit($BATCH_SIZE)
			->getList();
			
			foreach ($list as $item) {
				$callback($item);
			}
		}
	}
}
