<?php
namespace Mouf\Packanalyst\Widgets;

use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * A graph of dependencies.
 * 
 * Each node represents a class/interface... Each node can potentially belong to one or more packages/versions
 * 
 * 
 */
class Graph implements HtmlElementInterface
{
	private $rootNode;
	/**
	 * A nodes list indexed by classname
	 * @var array<string, Node>
	 */
	private $nodesList;
	
	public function __construct($rootNodeItems, $allItems) {

		foreach ($rootNodeItems as $rootNodeItem) {
			$this->rootNode = $this->registerNode($rootNodeItem);
		}
		
		// First pass, let's register all items.
		foreach ($allItems as $item) {
			$this->registerNode($item);
		}
		
		// Second path, let's register relationships.
		foreach ($allItems as $item) {
			foreach ($item['inherits'] as $parentItemName) {
				if (isset($this->nodesList[$parentItemName])) {
					$this->nodesList[$parentItemName]->addChild($this->nodesList[$item['name']]);
				}
			}
		}
		
	}

	
	private function registerNode($item) {
		$className = $item['name'];
		if (!isset($this->nodesList[$className])) {
			$this->nodesList[$className] = new Node($className, isset($item['type'])?$item['type']:null);
		}
		if (isset($item['packageName'])) {
			$this->nodesList[$className]->registerPackage($item['packageName'], $item['packageVersion'], isset($item['package']['downloads'])?$item['package']['downloads']:null, isset($item['package']['favers'])?$item['package']['favers']:null);
		}
		return $this->nodesList[$className];
	}
	
	
	public function toHtml() {
		echo "<ul class='classgraph'>";
		$this->rootNode->toHtml();
		echo "</ul>";
	}
}
