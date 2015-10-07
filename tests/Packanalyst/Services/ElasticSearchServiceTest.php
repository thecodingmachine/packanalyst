<?php

namespace Mouf\Packanalyst\Services;

class ElasticSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testStoreItemName()
    {
        $elasticSearchService = \Mouf::getElasticSearchService();

        try {
            $elasticSearchService->createIndex();
        } catch (\Exception $e) {
            // Ignore if index already exist.
        }

        try {
            $elasticSearchService->deleteItemName('unique_test_case');
        } catch (\Exception $e) {
            // Ignore if key does not exists.
        }

        $elasticSearchService->storeItemName('unique_test_case');

        $results = $elasticSearchService->suggestItemName2('unique_test_case');

        $this->assertEquals(1, $results['total']);
        $this->assertEquals(null, $results['hits'][0]['_source']['type']);

        $elasticSearchService->storeItemName('unique_test_case', 'class');
        $results = $elasticSearchService->suggestItemName2('unique_test_case');

        $this->assertEquals(1, $results['total']);
        $this->assertEquals('class', $results['hits'][0]['_source']['type']);

        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        // TODO: write test to test ID, update, and so on!!!
        $elasticSearchService->deleteItemName('unique_test_case');
    }
}
