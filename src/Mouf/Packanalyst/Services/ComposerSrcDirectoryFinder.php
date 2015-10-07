<?php
namespace Mouf\Packanalyst\Services;

/**
 * Looks into a composer.json file and retrieves all directory that could contain autoloadable PHP code.
 *
 * @author David Négrier <david@mouf-php.com>
 */
class ComposerSrcDirectoryFinder
{

    /**
     *
     * @param string $composerJsonPath Path to the composer file
     * @param bool $useAutoloadDev Include files for autoload dev?
     * @return array The directories containing PHP code.
     */
    public static function getComposerSrcDirs($composerJsonPath, $useAutoloadDev = false) {
        $composer = json_decode(file_get_contents($composerJsonPath), true);

        if (!$composer) {
            return [];
        }

        $srcDirs = [];

        $autoloadTags = ['autoload'];
        if ($useAutoloadDev) {
            $autoloadTags[] = ["autoload-dev"];
        }

        foreach ($autoloadTags as $autoload) {
            foreach (['psr-0', 'psr-4'] as $psr) {
                if (isset($composer[$autoload][$psr])) {
                    $map = $composer[$autoload][$psr];
                    foreach ($map as $namespace => $paths) {
                        if (!is_array($paths)) {
                            $paths = [$paths];
                        }

                        $paths = array_map(function($path) {
                            return trim($path, '\\/');
                        }, $paths);

                        $srcDirs = array_merge($srcDirs, $paths);
                    }
                }
            }

            if (isset($composer[$autoload]['classmap'])) {
                foreach ($composer[$autoload]['classmap'] as $classMapDir) {
                    if (strrpos($classMapDir, '.php') !== strlen($classMapDir)-4) {
                        $srcDirs[] = $classMapDir;
                    }
                }
            }
        }

        // Remove duplicates:
        $srcDirs = array_flip(array_flip($srcDirs));

        $srcDirs = array_filter($srcDirs, function($path) {
            return strpos($path, '..') === false;
        });

        return $srcDirs;
    }
}