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

/**
 * This package stores classes / interfaces / functions / traits each time it gets a chance.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class StoreInDbNodeVisitor extends NodeVisitorAbstract
{
	private $package;
	private $itemNameRepository;
	private $itemRepository;
	
	public function __construct(PackageVersionEntity $package, ItemNameRepository $itemNameRepository, Repository $itemRepository) {
		$this->package = $package;
		$this->itemNameRepository = $itemNameRepository;
		$this->itemRepository = $itemRepository;
	}
	
	public function leaveNode(Node $node) {
		/*if ($node instanceof Node\Name) {
			return new Node\Name($node->toString('_'));
		}*/
		if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Interface_ 
			|| $node instanceof Stmt\Trait_ || $node instanceof Stmt\Function_) {

			$item = new ItemEntity();
			$itemName = $node->namespacedName->toString();

			$item->setName($itemName);
			$item->setItemName($this->itemNameRepository->findOrCreateItemName($itemName));
			$comment = $node->getDocComment();
			if ($comment) {
				$item->setPhpDoc($node->getDocComment()->getText());
			}
			
			$item->setPackageVersion($this->package);
			
				
			if ($node instanceof Stmt\Class_) {
				$item->setType(ItemEntity::TYPE_CLASS);
				
				$inherits = [];
				if ($node->extends) {
					$inherits[] = $this->itemNameRepository->findOrCreateItemName($node->extends->toString());
				}
				
				foreach ($node->implements as $implement) {
					$inherits[] = $this->itemNameRepository->findOrCreateItemName($implement->toString());
				}
				$item->setInherits($inherits);
			} elseif ($node instanceof Stmt\Interface_) {
				$item->setType(ItemEntity::TYPE_INTERFACE);
				
				$inherits = [];
				
				foreach ($node->extends as $extend) {
					$inherits[] = $this->itemNameRepository->findOrCreateItemName($extend->toString());
				}
				$item->setInherits($inherits);
			} elseif ($node instanceof Stmt\Trait_) {
				$item->setType(ItemEntity::TYPE_TRAIT);
			} elseif ($node instanceof Stmt\Function_) {
				$item->setType(ItemEntity::TYPE_FUNCTION);
			}
			
			$this->itemRepository->getEntityManager()->persist($item);
		}
	}
}
