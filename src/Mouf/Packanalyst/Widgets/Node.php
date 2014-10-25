<?php
namespace Mouf\Packanalyst\Widgets;

use Mouf\Html\Renderer\Renderable;
use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * An object representing a node in the displayed class graph
 * 
 */
class Node implements HtmlElementInterface
{
	use Renderable;
	
	private $name;
	private $type;
	
	/**
	 * 
	 * @var Node[]
	 */
	private $children = array();
	
	/**
	 * An array of arrays:
	 * [
	 * 	"packageName"=>[0.1,0.2,0.3],
	 * 	"packageName2"=>[0.1,0.2,0.3]
	 * ]
	 * 
	 * @var array
	 */
	private $packages = [];
	
	private $packagesScores = [];
	
	public function __construct($className, $type) {
		$this->name = $className;
		$this->type = $type;
	}
	
	public function registerPackage($packageName, $version, $downloads, $favers) {
		if (!isset($this->packages[$packageName])) {
			$this->packages[$packageName] = [];
			$this->packagesScores[$packageName] = 1 + $downloads + $favers * 100;
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
	 * ]
	 *
	 * @var array
	 */
	private $importantPackages = null;
	
	/**
	 * An array of arrays of not so important packages (the ones that have the lower score):
	 * [
	 * 	"packageName"=>[0.1,0.2,0.3],
	 * 	"packageName2"=>[0.1,0.2,0.3]
	 * ]
	 *
	 * @var array
	 */
	private $notImportantPackages = null;
	
	public function getImportantPackages() {
		if ($this->importantPackages !== null) {
			return $this->importantPackages;
		}
		$this->sortPackages();
		return $this->importantPackages;
	}
	
	public function getNotImportantPackages() {
		if ($this->notImportantPackages !== null) {
			return $this->notImportantPackages;
		}
		$this->sortPackages();
		return $this->notImportantPackages;
	}
	
	private function sortPackages() {
		$this->importantPackages = [];
		$this->notImportantPackages = [];
		
		if (empty($this->packagesScores)) {
			return;
		}
		$maxScore = max($this->packagesScores);
				
		$threshold = (int) $maxScore/100;
		
		foreach ($this->packages as $packageName => $versions) {
			if ($this->packagesScores[$packageName] >= $threshold) {
				$this->importantPackages[$packageName] = $versions;
			} else {
				$this->notImportantPackages[$packageName] = $versions;
			}
		}
	}
	
	public function addChild(Node $node) {
		if (array_search($node, $this->children) === false) {
			$this->children[] = $node;
		}
	}
	
}
