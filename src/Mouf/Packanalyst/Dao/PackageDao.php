<?php

namespace Mouf\Packanalyst\Dao;

use Composer\Package\Package;
use Composer\Package\CompletePackage;
use MongoDB\Collection;
use Mouf\Packanalyst\Services\ElasticSearchService;

class PackageDao
{
    /**
     * @var Collection
     */
    private $collection;

    private $elasticSearchService;

    private $itemDao;

    public function __construct(Collection $collection, ElasticSearchService $elasticSearchService, ItemDao $itemDao)
    {
        $this->collection = $collection;
        $this->elasticSearchService = $elasticSearchService;
        $this->itemDao = $itemDao;
    }

    public function createIndex()
    {
        $this->collection->createIndex([
            'packageName' => 1,
            'packageVersion' => 1,
        ]);
    }

    /**
     * Drops the complete collection.
     */
    public function drop()
    {
        $this->collection->drop();
    }

    /**
     * Deletes all items relative to this package version.
     *
     * @param string $packageName
     * @param string $packageVersion
     */
    public function deletePackage($packageName, $packageVersion)
    {
        $this->collection->deleteMany([
            'packageName' => $packageName,
            'packageVersion' => $packageVersion,
        ]);
    }

    /**
     * Returns a package by name and version.
     *
     * @param string $packageName
     * @param string $packageVersion
     *
     * @return array|null
     */
    public function get($packageName, $packageVersion)
    {
        return (array) $this->collection->findOne([
            'packageName' => $packageName,
            'packageVersion' => $packageVersion,
        ]);
    }

    public function getPackagesByName($packageName)
    {
        return $this->collection->find([
            'packageName' => $packageName,
        ]);
    }

    /**
     * @param string $packageName
     *
     * @return array
     */
    public function getLatestPackage($packageName)
    {
        $packages = $this->getPackagesByName($packageName)->toArray();

        if (count($packages) == 0) {
            return;
        }

        // Is there a dev-master?
        foreach ($packages as $package) {
            if ($package->packageVersion == 'dev-master') {
                return (array) $package;
            }
        }

        $latestVersion = '0.0.0';
        $selectedPackage = reset($packages);

        foreach ($packages as $package) {
            $version = ltrim($package->packageVersion, 'v');
            if (version_compare($version, $latestVersion) > 0) {
                $latestVersion = $version;
                $selectedPackage = $package;
            }
        }

        return (array) $selectedPackage;
    }

    /**
     * Creates or update a package in MongoDB from the Package passed in parameter.
     *
     * @param Package $package
     *
     * @return array
     */
    public function createOrUpdatePackage(Package $package)
    {
        $packageVersion = $this->get($package->getName(), $package->getPrettyVersion());

        if (!$packageVersion) {
            $packageVersion = [
                'packageName' => $package->getName(),
                'packageVersion' => $package->getPrettyVersion(),
            ];
        }

        $packageVersion['releaseDate'] = new \MongoDB\BSON\UTCDateTime($package->getReleaseDate()->getTimestamp()*1000);
        $packageVersion['type'] = $package->getType();
        $packageVersion['sourceUrl'] = $package->getSourceUrl();
        $packageVersion['realVersion'] = $package->getVersion();

        if ($package instanceof CompletePackage) {
            $packageVersion['description'] = $package->getDescription();
        }

        // TODO: check this can replace the old SAVE method
        $this->collection->insertOne($packageVersion);

        // Boost = 1 + download/10 + favers
        // TODO: we could improve the score of packages by the number of times they are referred by other packages.
        $score = 1;
        if (isset($packageVersion['downloads'])) {
            $score += $packageVersion['downloads'] / 10;
        }
        if (isset($packageVersion['favers'])) {
            $score += $packageVersion['favers'];
        }

        $this->itemDao->applyScore($package->getName(), $score);

        $this->elasticSearchService->storeItemName($package->getName(), 'package', $score);

        return $packageVersion;
    }

    public function save(array $packageVersion)
    {
        if (isset($packageVersion['_id'])) {
            $this->collection->findOneAndReplace(['_id'=>$packageVersion['_id']], $packageVersion);
        } else {
            $this->collection->insertOne($packageVersion);
        }
    }

    public function applyOnAllPackages(callable $callback)
    {
        foreach ($this->collection->find() as $item) {
            $callback((array) $item);
        }
    }

    /**
     * Marks all packages for refresh.
     */
    public function refreshAllPackages()
    {
        $this->collection->updateMany(
                array(),
                array('$set' => array('refresh' => true)),
                array('multiple' => true)
        );
    }
}
