<?php
namespace Mouf\Packanalyst\Services;

use HireVoice\Neo4j\EntityManager;
use Composer\Repository\ComposerRepository;
use Composer\Downloader\DownloadManager;
use Mouf\Packanalyst\ClassesDetector;
use Composer\Package\Package;
use Mouf\Packanalyst\Repositories\PackageRepository;
use Mouf\Packanalyst\Repositories\PackageVersionRepository;
use Mouf\Packanalyst\Repositories\ItemNameRepository;
use Mouf\Packanalyst\Repositories\ItemRepository;
use Mouf\Packanalyst\Entities\ItemEntity;
use Elasticsearch\Client;
use Mouf\Packanalyst\Entities\ItemNameEntity;
/**
 * This package is in charge of indexing data into elastic search.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class ElasticSearchService
{
	private $itemNameRepository;
	private $elasticSearchClient;
	
	public function __construct(ItemNameRepository $itemNameRepository, Client $elasticSearchClient) {
		$this->itemNameRepository = $itemNameRepository;
		$this->elasticSearchClient = $elasticSearchClient;
	}
	
	/**
	 * Reindex everything in elastic search.
	 */
	public function reindexAll() {
		$this->deleteIndex();
		$this->createIndex();
		$this->itemNameRepository->applyOnAllItemName(function(ItemNameEntity $itemName) {
			echo "Indexing ".$itemName->getName()."\n";
			$this->storeItemName($itemName);
		});
	}
	
	/**
	 * Delete index
	 */
	public function deleteIndex() {
		try {
			$deleteParams = array();
			$deleteParams['index'] = 'packanalyst';
			$this->elasticSearchClient->indices()->delete($deleteParams);
		} catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) { //Elasticsearch\Common\Exceptions\Missing404Exception $ex) {
			// Ignore 404: if the index does not exist, it's ok.
		}
	}

	/**
	 * Create index
	 */
	public function createIndex() {
		$indexParams = array();
		$indexParams['index'] = 'packanalyst';
		//$indexParams['body']['settings']['number_of_shards'] = 2;
		//$indexParams['body']['settings']['number_of_replicas'] = 0;
		
		// Example Index Mapping
		$itemNameMapping = array(
				'properties' => array(
						"name" => [ "type" => "string" ],
			            "suggest" => [ 
							"type" => "completion",
			                "index_analyzer" => "simple",
			                "search_analyzer" => "simple",
			                //"payloads" => true
			            ]
				)
		);
		$indexParams['body']['mappings']['itemname'] = $itemNameMapping;
		
		
		$this->elasticSearchClient->indices()->create($indexParams);
	}
	
	/**
	 * Stores an item name in elastic search.
	 * 
	 * @param ItemNameEntity $item
	 */
	public function storeItemName(ItemNameEntity $itemName) {
		
		if (!$itemName->getId()) {
			throw new \Exception("An item name must have an ID to be stored in Elastic Search.");
		}
		
		$store = explode('\\', $itemName->getName());
		if (count($store) != 1) {
			$store[] = $itemName->getName();
		}
		
		$params = array();
		$params['body']  = array(
				'name' => $itemName->getName(),
				'suggest' => [
					"input" => $store,
					"output" => $itemName->getName(),
					//"payload" => [ "artistId" : 2321 ],
					//"weight" => 34
				]
		);
		$params['index'] = 'packanalyst';
		$params['type']  = 'itemname';
		$params['id']    = $itemName->getId();
		$ret = $this->elasticSearchClient->index($params);
	}
	
	public function suggestItemName($input) {
		$params = [
			"body" => [
				"itemname" => [
					"text" => $input,
					"completion" => [
						"field" => "suggest",
						"fuzzy"=> true,
						"size"=> 10
					]
				]
			]
		];
		
		$suggestions = $this->elasticSearchClient->suggest($params);
		
		return array_map(function($item) {
			return [
				'value' => $item['text']
			];
		}, $suggestions['itemname'][0]['options']);
	}
}
