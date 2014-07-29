<?php
namespace Mouf\Packanalyst\Services;

use Composer\Repository\ComposerRepository;
use Composer\Downloader\DownloadManager;
use Mouf\Packanalyst\ClassesDetector;
use Composer\Package\Package;
use Elasticsearch\Client;
use Mouf\Packanalyst\Dao\ItemDao;
use Mouf\Packanalyst\Dao\PackageDao;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;

/**
 * This package is in charge of indexing data into elastic search.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class ElasticSearchService
{
	/**
	 * 
	 * @var ItemDao
	 */
	private $itemDao;
	
	/**
	 * 
	 * @var PackageDao
	 */
	private $packageDao;
	
	private $elasticSearchClient;
	
	
	
	public function __construct(Client $elasticSearchClient) {
		$this->elasticSearchClient = $elasticSearchClient;
	}
	
	
	
	/**
	 * Reindex everything in elastic search.
	 */
	public function reindexAll() {
		$this->deleteIndex();
		$this->createIndex();
		$this->itemDao->applyOnAllItemName(function($item) {
			echo "Indexing ".$item['name']."\n";
			$this->storeItemName($item['name'], $item['type']);
		});
		$this->packageDao->applyOnAllPackages(function($item) {
			if (isset($item['packageName'])) {
				echo "Indexing ".$item['packageName']."\n";
				$this->storeItemName($item['packageName'], 'package');
			}
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
						"name" => [ 
							"type" => "multi_field",
							"fields" => [
								"name" => [ "type" => "string", "index" => "not_analyzed" ],
								"nameAuto" => [ "type" => "string", "index" => "analyzed" ],
							],
						],
			            "suggest" => [ 
							"type" => "completion",
			                "index_analyzer" => "simple",
			                "search_analyzer" => "simple",
			                //"payloads" => true
			            ],
			            "boost" => [
							"type" => "float",
							"index" => "not_analyzed"
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
	public function storeItemName($itemName, $type = null, $boost = null) {
		
		// TODO: a local "cache" array that contain all the classes we know that exist in ElasticSearch.
		// When the local cache is set, remove the "refresh"=>true
		
		// Before inserting itemName, let's make sure it is not ALREADY in the index.
		$oldSource = $this->checkItemNameExists($itemName);
		if ($oldSource != false) {
			// item exists.
			if ($type != null && $oldSource['_source']['type'] != $type) {
				// We can update the type with the new type
				$doc = [];
				if ($type) {
					$doc['type'] = $type;
				}
				if ($boost) {
					$doc['boost'] = $boost;
				}
				
				$this->elasticSearchClient->update(array(
					'id'=>$oldSource['_id'],
					'index'=>'packanalyst',
					'type'=>'itemname',
					'body'=>[
						'doc'=>$doc
					],
					'refresh' => true
				));
			}				
			return;
		}
		
		$store = explode('\\', $itemName);
		if (count($store) != 1) {
			$store[] = $itemName;
		}
		
		if ($boost == null) {
			$boost = 1.0;
		}
		
		$params = array();
		$params['body']  = array(
				'name' => $itemName,
				'type' => $type,
				'suggest' => [
					"input" => $store,
					"output" => $itemName,
				],
				'boost' => $boost
		);
		$params['index'] = 'packanalyst';
		$params['type']  = 'itemname';
		$params['refresh']  = true;
		$ret = $this->elasticSearchClient->index($params);
	}
	
	public function deleteItemName($itemName) {
		$source = $this->checkItemNameExists($itemName);
		if ($source != false) {
			$this->elasticSearchClient->delete(array(
						'id'=>$source['_id'],
						'index'=>'packanalyst',
						'type'=>'itemname'
			));
		}
	}
	
	/**
	 * False if the item name does not exist, or the source if the type does exist.
	 * @param string $itemName
	 */
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
		
		try {
			$ret = $this->elasticSearchClient->search($params);
		} catch (ServerErrorResponseException $e) {
			// Note: it seems an error is triggered if the index is empty.
			error_log("Exception in search: ".$e->getMessage()."\n".$e->getTraceAsString());
		}
		
		if ($ret['hits']['total'] == 0) {
			return false;
		} else {
			if (isset($ret['hits']['hits'][0])) {
				return $ret['hits']['hits'][0];
			} else {
				return null;
			}
		}
	}
	
	public function suggestItemName($input, $size=10) {
		$params = [
			"body" => [
				"itemname" => [
					"text" => $input,
					"completion" => [
						"field" => "suggest",
						"fuzzy"=> true,
						"size"=> $size
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

	/**
	 * 
	 * @param unknown $input
	 * @param number $size
	 * @throws \Exception
	 * @return array(["total"=>xxx, "max_score"=>xxxx, hits=>[]]) 
	 */
	public function suggestItemName2($input, $size=10, $offset=0) {
		$params = 
		[
		"body"=>
			[
				"query"=> [
					"function_score" => [
						"query" => [
							"bool"=>[
								"should"=> [ 
									"query_string"=> [
										"fields"=> [
											"nameAuto"
										],
										"query"=> "*".$input."*"
									],
								],
								"should"=> [ 
									"fuzzy_like_this"=> [
										"fields"=> [
											"nameAuto"
										],
										"like_text"=> $input
									],
								]
							]
						],
			            "functions" => [
			            	[
			                	"field_value_factor" => [ 
			                    	"field" => "boost"
				                ]
				            ]
						],
						"score_mode" => "multiply"
					]
				]
			],
		"size" => $size + $offset,
		
		];
		
		
	
		$suggestions = $this->elasticSearchClient->search($params);
	
		//var_dump($suggestions);exit;
		
		if (!isset($suggestions['hits'])) {
			throw new \Exception("Error while querying search: ".json_encode($suggestions));
		}
		
		$hits = $suggestions['hits']['hits'];
		
		for ($i=0; $i<$offset; $i++) {
			array_shift($hits);
		}
		
		$suggestions['hits']['hits'] = $hits;
	
		return $suggestions['hits'];
	}
	
	
	public function setItemDao(ItemDao $itemDao) {
		$this->itemDao = $itemDao;
		return $this;
	}
	
	public function setPackageDao(PackageDao $packageDao) {
		$this->packageDao = $packageDao;
		return $this;
	}
	
	
}
