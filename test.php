<?php
use Composer\Package\Package;
require_once 'mouf/Mouf.php'; 
/*require_once 'vendor/autoload.php';

use Composer\Repository\CompositeRepository;
use Composer\Repository\PlatformRepository;
use Composer\Factory;
// init repos
            $repos = new CompositeRepository(array_merge(
                array(new PlatformRepository()),
                Factory::createDefaultRepositories($this->getIO())
            ));
*/

$package = new Package('test/test', '1.0.0', '1.0.0');
$package->setReleaseDate(new DateTime());

Mouf::getPackageVersionRepository()->findOrCreatePackageVersion($package);
//Mouf::getPackageRepository()->findOrCreatePackage('mouf/mouf');
Mouf::getNeo4jEntityManager()->flush();