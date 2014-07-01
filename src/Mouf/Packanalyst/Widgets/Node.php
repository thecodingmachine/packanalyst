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
	
	private $node;
	
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
	private $packages;
	
	public function __construct(\Everyman\Neo4j\Node $node) {
		$this->node = $node;
		$this->name = $node->getProperty('name');
		$this->type = $node->getProperty('type');
	}
	
	public function registerPackage(\Everyman\Neo4j\Node $node) {
		$packageName = $node->getProperty('packageName');
		$version = $node->getProperty('version');
		
		if (!isset($this->packages[$packageName])) {
			$this->packages[$packageName] = [];
		}
		
		if (array_search($version, $this->packages[$packageName]) === false) {
			$this->packages[$packageName][] = $version;
		}
	}
	
	public function addChild(Node $node) {
		if (array_search($node, $this->children) === false) {
			$this->children[] = $node;
		}
	}
	
}
