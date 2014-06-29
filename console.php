#!/usr/bin/env php
<?php
require_once 'mouf/Mouf.php';

use Mouf\Packanalyst\Command\RunCommand;
use Symfony\Component\Console\Application;
use Mouf\Packanalyst\Command\ReindexCommand;

$application = new Application();
$application->add(new RunCommand());
$application->add(new ReindexCommand());
$application->run();