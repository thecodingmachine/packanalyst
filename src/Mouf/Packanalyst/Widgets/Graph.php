<?php
namespace Mouf\Packanalyst\Widgets;

use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * An object representing a node in the displayed class graph
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
	
	public function __construct(\Everyman\Neo4j\Node $rootNode) {
		$node = $this->getNode($rootNode);
		$this->rootNode = $node;
	}
	
	public function registerRelations(\Everyman\Neo4j\Query\Row $relations, \Everyman\Neo4j\Node $package) {
		// Let's get the complete list of items.
		// Note: relations are the other way around so we start with end.
		$chain = [];
		foreach ($relations as $relation) {
			/* @var $relation \Everyman\Neo4j\Relationship */
			if (empty($chain)) {
				$chain[] = $relation->getEndNode();
			}
			$chain[] = $relation->getStartNode();
		}
		
		// Now, let's keep only the nodes that are Item, and let's get rid of ItemName
		$filteredChain = array_values(array_filter($chain, function(\Everyman\Neo4j\Node $node) {
			//if (array_search('Item', $node->getLabels())!== false) {
			if ($node->getProperty('class') == 'Mouf\\Packanalyst\\Entities\\ItemEntity') {
				return true;
			} else {
				return false;
			}
		}));
		
		$this->rootNode->addChild($this->getNode($filteredChain[0]));
		
		// Now, $chain is a chain of items.
		for ($i=0; $i<count($filteredChain)-1; $i++) {
			$parent = $this->getNode($filteredChain[$i]);
			$child = $this->getNode($filteredChain[$i+1]);
			$parent->addChild($child);
		}
		
		$lastNode = $this->getNode($filteredChain[count($filteredChain)-1]);
		$lastNode->registerPackage($package);
		
		
	}
	
	/**
	 * 
	 * @param \Everyman\Neo4j\Node $node
	 * @return Node
	 */
	protected function getNode(\Everyman\Neo4j\Node $node) {
		$className = $node->getProperty('name');
		if (!isset($this->nodesList[$className])) {
			$this->nodesList[$className] = new Node($node);
			
		}
		return $this->nodesList[$className];
	}
	
	public function toHtml() {
		echo "<ul class='classgraph'>";
		$this->rootNode->toHtml();
		echo "</ul>";
	}
}
