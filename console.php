#!/usr/bin/env php
<?php
require_once 'mouf/Mouf.php';

use Mouf\Packanalyst\Command\RunCommand;
use Symfony\Component\Console\Application;
use Mouf\Packanalyst\Command\ReindexCommand;
use Mouf\Packanalyst\Command\ResetCommand;
use Mouf\Packanalyst\Command\GetPackagistScoresCommand;
use Mouf\Packanalyst\Command\ForceRefreshCommand;

$application = new Application();
$application->add(new RunCommand());
$application->add(new ReindexCommand());
$application->add(new ResetCommand());
$application->add(new GetPackagistScoresCommand());
$application->add(new ForceRefreshCommand());
$application->run();