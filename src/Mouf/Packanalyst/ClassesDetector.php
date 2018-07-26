<?php

namespace Mouf\Packanalyst;

use Mouf\Packanalyst\Services\ComposerSrcDirectoryFinder;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Composer\Package\Package;
use Mouf\Packanalyst\Services\StoreInDbNodeVisitor;
use PhpParser\Error;
use PhpParser\ParserFactory;
use Psr\Log\LoggerInterface;
use Mouf\Packanalyst\Dao\ItemDao;

/**
 * This package is in charge of detecting classes/interfaces/traits inside a package.
 *
 * @author David Négrier <david@mouf-php.com>
 */
class ClassesDetector extends NodeVisitorAbstract
{
    private $parser;
    private $logger;
    private $itemDao;

    public function __construct(LoggerInterface $logger, ItemDao $itemDao)
    {
        $this->parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->logger = $logger;
        $this->itemDao = $itemDao;
    }

    /**
     * Returns the classes / interfaces / traits / functions of the package.
     *
     * @return array<string, string>
     */
    public function storePackage($basePath, array $packageVersion)
    {
        $traverser = new NodeTraverser();

        $storeInDbNodeVisitor = new StoreInDbNodeVisitor($packageVersion, $this->itemDao);

        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor($storeInDbNodeVisitor);     // our own node visitor


        if (file_exists($basePath.'/composer.json')) {
            $srcDirs = ComposerSrcDirectoryFinder::getComposerSrcDirs($basePath.'/composer.json');

            $files = [];
            foreach ($srcDirs as $dir) {
                $dirFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath.'/'.$dir));
                $dirFiles = new \RegexIterator($dirFiles, '/\.php$/');
                $dirFiles = new \CallbackFilterIterator($dirFiles, function ($file) {
                    return (strpos($file, 'vendor/') === false) && (strpos($file, 'fixtures/') === false);
                });
                foreach ($dirFiles as $file) {
                    $files[] = (string) $file;
                }
            }
            // Last deduplicate:
            $files = array_flip(array_flip($files));
        } else {
            // Composer.json not found... this is a weird case... let's go back to full directory scanning.
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basePath));
            $files = new \RegexIterator($files, '/\.php$/');
            $files = new \CallbackFilterIterator($files, function ($file) {
                return (strpos($file, 'vendor/') === false) && (strpos($file, 'fixtures/') === false);
            });
        }

        foreach ($files as $file) {
            try {
                $relativeFileName = substr($file, strlen($basePath));
                $storeInDbNodeVisitor->setFileName($relativeFileName);

                // read the file that should be converted
                $code = file_get_contents($file);

                // parse

                $stmts = $this->parser->parse($code);

                // traverse
                $stmts = $traverser->traverse($stmts);
            } catch (Error $e) {
                $this->logger->warning('PHP error detected in file {file}. Ignoring file. Error: {errorMsg}', [
                    'file' => $file,
                    'errorMsg' => $e->getMessage(),
                    'exception' => $e,
                ]
                );
            }
        }

        //return $classMap;
    }
}
