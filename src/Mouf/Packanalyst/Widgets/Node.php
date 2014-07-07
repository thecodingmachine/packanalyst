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
	private $packages;
	
	public function __construct($className, $type) {
		$this->name = $className;
		$this->type = $type;
	}
	
	public function registerPackage($packageName, $version) {
		
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
