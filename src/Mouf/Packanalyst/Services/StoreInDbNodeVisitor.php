<?php
namespace Mouf\Packanalyst\Services;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Mouf\Packanalyst\Entities\ItemEntity;
use Mouf\Packanalyst\Entities\PackageEntity;
use Mouf\Packanalyst\Entities\ItemNameEntity;
use HireVoice\Neo4j\Repository;
use Mouf\Packanalyst\Entities\PackageVersionEntity;
use Mouf\Packanalyst\Repositories\ItemNameRepository;
use Mouf\Packanalyst\Dao\ItemDao;

/**
 * This package stores classes / interfaces / functions / traits each time it gets a chance.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class StoreInDbNodeVisitor extends NodeVisitorAbstract
{
	private $package;
	private $itemDao;
	
	public function __construct($package, ItemDao $itemDao) {
		$this->package = $package;
		$this->itemDao = $itemDao;
	}
	
	public function leaveNode(Node $node) {
		/*if ($node instanceof Node\Name) {
			return new Node\Name($node->toString('_'));
		}*/
		if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Interface_ 
			|| $node instanceof Stmt\Trait_ || $node instanceof Stmt\Function_) {

			$item = [];
			$itemName = $node->namespacedName->toString();

			$item['name'] = $itemName;
			$comment = $node->getDocComment();
			if ($comment) {
				$item['phpDoc'] = $node->getDocComment()->getText();
			}
			
			$item['packageName'] = $this->package['packageName'];
			$item['packageVersion'] = $this->package['packageVersion'];
			
				
			if ($node instanceof Stmt\Class_) {
				$item['type'] = ItemDao::TYPE_CLASS;
				
				$inherits = [];
				if ($node->extends) {
					$inherits[] = $node->extends->toString();
				}
				
				foreach ($node->implements as $implement) {
					$inherits[] = $implement->toString();
				}
				$item['inherits'] = $inherits;
			} elseif ($node instanceof Stmt\Interface_) {
				$item['type'] = ItemDao::TYPE_INTERFACE;
				
				$inherits = [];
				
				foreach ($node->extends as $extend) {
					$inherits[] = $extend->toString();
				}
				$item['inherits'] = $inherits;
			} elseif ($node instanceof Stmt\Trait_) {
				$item['type'] = ItemDao::TYPE_TRAIT;
			} elseif ($node instanceof Stmt\Function_) {
				$item['type'] = ItemDao::TYPE_FUNCTION;
			}
			
			$this->itemDao->save($item);
		}
	}
}
