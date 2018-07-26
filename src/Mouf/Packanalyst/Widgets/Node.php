<?php

namespace Mouf\Packanalyst\Widgets;

use Mouf\Html\Renderer\Renderable;
use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * An object representing a node in the displayed class graph.
 */
class Node implements HtmlElementInterface
{
    use Renderable;

    private $name;
    private $type;
    /**
     * The score of a node is the sum of the score of the packages implementing it.
     *
     * @var int
     */
    private $score = 0;

    /**
     * @var Node[]
     */
    private $children = array();

    /**
     * An array of arrays:
     * [
     * 	"packageName"=>[0.1,0.2,0.3],
     * 	"packageName2"=>[0.1,0.2,0.3]
     * ].
     *
     * @var array
     */
    private $packages = [];

    private $packagesScores = [];

    public function __construct($className, $type)
    {
        $this->name = $className;
        $this->type = $type;
    }

    public function registerPackage($packageName, $version, $downloads, $favers)
    {
        if (!isset($this->packages[$packageName])) {
            $this->packages[$packageName] = [];
            $this->packagesScores[$packageName] = 1 + $downloads + $favers * 100;
            $this->score += $this->packagesScores[$packageName];
        }

        if (array_search($version, $this->packages[$packageName]) === false) {
            $this->packages[$packageName][] = $version;
        }
    }

    /**
     * An array of arrays of important packages (the ones that have the higher score):
     * [
     * 	"packageName"=>[0.1,0.2,0.3],
     * 	"packageName2"=>[0.1,0.2,0.3]
     * ].
     *
     * @var array
     */
    private $importantPackages = null;

    /**
     * An array of arrays of not so important packages (the ones that have the lower score):
     * [
     * 	"packageName"=>[0.1,0.2,0.3],
     * 	"packageName2"=>[0.1,0.2,0.3]
     * ].
     *
     * @var array
     */
    private $notImportantPackages = null;

    public function getImportantPackages()
    {
        if ($this->importantPackages !== null) {
            return $this->importantPackages;
        }
        $this->sortPackages();

        return $this->importantPackages;
    }

    public function getNotImportantPackages()
    {
        if ($this->notImportantPackages !== null) {
            return $this->notImportantPackages;
        }
        $this->sortPackages();

        return $this->notImportantPackages;
    }

    private function sortPackages()
    {
        $this->importantPackages = [];
        $this->notImportantPackages = [];

        if (empty($this->packagesScores)) {
            return;
        }
        $maxScore = max($this->packagesScores);

        $threshold = (int) $maxScore / 100;

        foreach ($this->packages as $packageName => $versions) {
            if ($this->packagesScores[$packageName] >= $threshold) {
                $this->importantPackages[$packageName] = $versions;
            } else {
                $this->notImportantPackages[$packageName] = $versions;
            }
        }
    }

    public function addChild(Node $node)
    {
        if (array_search($node, $this->children) === false) {
            $this->children[] = $node;
        }
    }

    /**
     * The score of a node is the sum of the score of the packages implementing it.
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Returns the list of children, sorted by reverse score order.
     */
    public function getChildrenSortedByScore()
    {
        usort($this->children, function ($a, $b) {
            return $b->getScore() - $a->getScore();
        });

        return $this->children;
    }

    public function getNbStars()
    {
        if ($this->score >= 1000000) {
            return 5;
        } elseif ($this->score >= 100000) {
            return 4;
        } elseif ($this->score >= 10000) {
            return 3;
        } elseif ($this->score >= 1000) {
            return 2;
        } elseif ($this->score >= 100) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the depth of the node (0 if the node has no child).
     *
     * @return int
     */
    public function getDepth()
    {
        if (empty($this->children)) {
            return 0;
        } else {
            return max(array_map(function ($item) { return $item->getDepth() + 1; }, $this->children));
        }
    }

    private $reverseDepth = null;

    /**
     * Returns the depth of the node compared to the max depth of the parent (for reverse display).
     */
    public function getRevertDepth()
    {
        if ($this->reverseDepth !== null) {
            return $this->reverseDepth;
        }
        $this->setReverseDepth($this->getDepth());

        return $this->reverseDepth;
    }

    private function setReverseDepth($depth)
    {
        $this->reverseDepth = $depth;
        foreach ($this->children as $child) {
            $child->setReverseDepth($depth - 1);
        }
    }

    /**
     * Renders the tree, in reverse order!
     */
    public function getHtmlRevert(): string
    {
        ob_start();
        \Mouf::getDefaultRenderer()->render($this, 'revert');

        return ob_get_clean();
    }

    protected $replacementNode;

    /**
     * Replaces this node rendering with another HtmlElementInterface.
     * Used for the root node in htmlrevert mode.
     */
    public function replaceNodeRenderingWith(HtmlElementInterface $graph): void
    {
        $this->replacementNode = $graph;
    }

    /**
     * @var bool
     */
    protected $highlight = false;

    /**
     * Sets whether we should highlight or not the class (in yellow).
     */
    public function setHighlight(bool $highlight): void
    {
        $this->highlight = $highlight;
    }
}
