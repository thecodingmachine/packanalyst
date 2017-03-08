<?php

namespace Mouf\Packanalyst\Services;

use Mouf\Packanalyst\Dao\PackageDao;
use Psr\Log\LoggerInterface;
use GuzzleHttp;

/**
 * This class is in charge of retrieving nb of downloads from Packagist and applying them in the Mongo database.
 *
 * @author David Négrier <david@mouf-php.com>
 */
class PackagistScoreService
{
    private $packageDao;
    private $logger;

    // Number of seconds to wait between requests
    const WAIT_TIME_BETWEEN_REQUESTS = 10;

    public function __construct(PackageDao $packageDao, LoggerInterface $logger)
    {
        $this->packageDao = $packageDao;
        $this->logger = $logger;
    }

    public function updateAllScores()
    {
        $i = 1;

        do {
            $this->logger->notice('Downloading scores for page {page}', ['page' => $i]);

            $result = $this->request($i);

            foreach ($result['results'] as $packageResult) {
                $packageResult = (array) $packageResult;
                $packages = $this->packageDao->getPackagesByName($packageResult['name']);
                foreach ($packages as $package) {
                    $package = (array) $package;
                    $package['downloads'] = $packageResult['downloads'];
                    $package['favers'] = $packageResult['favers'];
                    $this->packageDao->save($package);
                }
            }

            sleep(self::WAIT_TIME_BETWEEN_REQUESTS);

            ++$i;
        } while (isset($result['next']));
    }

    /**
     * Performs a request to the API, returns.
     *
     * @param number $page
     */
    private function request($page = 1)
    {
        $client = new GuzzleHttp\Client();
        $response = $client->get('https://packagist.org/search.json?q=&page='.$page);

        return $response->json();
    }
}
