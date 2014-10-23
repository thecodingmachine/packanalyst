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
	 * @var unknown
	 */
	private $packages = [];
	
	private $packagesScores = [];
	
	public function __construct($className, $type) {
		$this->name = $className;
		$this->type = $type;
	}
	
	public function registerPackage($packageName, $version, $downloads, $favers) {
		// TODO: Do something with downloads and favers!!!!
		if (!isset($this->packages[$packageName])) {
			$this->packages[$packageName] = [];
			$this->packagesScores[$packageName] = 1 + $downloads + $favers * 100;
		}
		
		if (array_search($version, $this->packages[$packageName]) === false) {
			$this->packages[$packageName][] = $version;
		}
	}
	
	private $importantPackages = null;
	private $notImportantPackages = null;
	
	public function getImportantPackages() {
		if ($this->importantPackages !== null) {
			return $this->importantPackages;
		}
		$this->sortPackages();
	}
	
	public function getNotImportantPackages() {
		if ($this->notImportantPackages !== null) {
			return $this->notImportantPackages;
		}
		$this->sortPackages();
		
	}
	
	private function sortPackages() {
		$maxScore = max($this->packagesScores);
		
		// TODO
		foreach ($this->packagesScores as $score=>$packageName) {
			$maxScore = $score;
			break;
		}
		$threshold = (int) $maxScore/100;
		
		
	}
	
	public function addChild(Node $node) {
		if (array_search($node, $this->children) === false) {
			$this->children[] = $node;
		}
	}
	
}
