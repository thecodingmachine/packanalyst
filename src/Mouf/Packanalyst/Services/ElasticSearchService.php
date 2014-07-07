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
use Everyman\Neo4j\Exception;
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
		
		// Mapping as both a suggester and a not_analyzed field (to be searchable via filters)
		/*$itemNameMapping = array(
				'properties' => array(
						"name" => [ "type" => "multi_field",
						"fields" => [
							"name" => ["type" => "string"],
							"untouched" => ["type" => "string", "index" => "not_analyzed"]
						], "suggest" => [
								"type" => "completion",
								"index_analyzer" => "simple",
								"search_analyzer" => "simple",
								//"payloads" => true
								]],
				)
		);*/
		$itemNameMapping = array(
				'properties' => array(
						"name" => [ "type" => "string", "index" => "not_analyzed" ],
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
	 * @param string $item
	 */
	public function storeItemName($itemName) {
		
		// FIXME: we must be sure we don't import the same item TWICE!!!!
		// FIXME: we must import all the inherited names (like Exception)!!!!
		// TODO: a local "cache" array that contain all the classes we know that exist in ElasticSearch.
		// Or find a way in ElasticSearch to have unique indexes.
		
		// Before inserting itemName, let's make sure it is not ALREADY in the index.
		if ($this->checkItemNameExists($itemName)) {
			return;
		}
		
		$store = explode('\\', $itemName);
		if (count($store) != 1) {
			$store[] = $itemName;
		}
		
		$params = array();
		$params['body']  = array(
				'name' => $itemName,
				'suggest' => [
					"input" => $store,
					"output" => $itemName,
				]
		);
		$params['index'] = 'packanalyst';
		$params['type']  = 'itemname';
		$ret = $this->elasticSearchClient->index($params);
	}
	
	private function checkItemNameExists($itemName) {
		$params = array();
		$params['body']  = [
			"filter" => [
				"term" => [
					"name" => $itemName
				]
			]
		];
		$params['index'] = 'packanalyst';
		$params['type']  = 'itemname';
		$ret = $this->elasticSearchClient->search($params);
		
		if ($ret['hits']['total'] > 0) {
			return true;
		} else {
			return false;
		}
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
		
		if (!isset($suggestions['itemname'])) {
			throw new \Exception("Error while querying autocomplete: ".json_encode($suggestions));
		}
		
		return array_map(function($item) {
			return [
				'value' => $item['text']
			];
		}, $suggestions['itemname'][0]['options']);
	}
}
