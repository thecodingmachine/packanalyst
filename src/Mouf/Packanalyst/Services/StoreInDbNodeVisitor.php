<?php
namespace Mouf\Packanalyst\Services;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
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
	private $fileName;
	
	public function __construct($package, ItemDao $itemDao) {
		$this->package = $package;
		$this->itemDao = $itemDao;
	}
	
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}
	
	public function leaveNode(Node $node) {
		/*if ($node instanceof Node\Name) {
			return new Node\Name($node->toString('_'));
		}*/
		if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Interface_ 
			|| $node instanceof Stmt\Trait_ || $node instanceof Stmt\Function_) {

			$item = [];
			$itemName = $node->namespacedName->toString();

			$item['name'] = $this->ensureUtf8($itemName);
			$comment = $node->getDocComment();
			if ($comment) {
				$item['phpDoc'] = $this->ensureUtf8($node->getDocComment()->getText());
			}
			
			$item['packageName'] = $this->package['packageName'];
			$item['packageVersion'] = $this->package['packageVersion'];
			$item['fileName'] = $this->fileName;
				
			if ($node instanceof Stmt\Class_) {
				$item['type'] = ItemDao::TYPE_CLASS;
				
				$inherits = [];
				if ($node->extends) {
					$inherits[] = $this->ensureUtf8($node->extends->toString());
				}
				
				foreach ($node->implements as $implement) {
					$inherits[] = $this->ensureUtf8($implement->toString());
				}
				$item['inherits'] = $inherits;
			} elseif ($node instanceof Stmt\Interface_) {
				$item['type'] = ItemDao::TYPE_INTERFACE;
				
				$inherits = [];
				
				foreach ($node->extends as $extend) {
					$inherits[] = $this->ensureUtf8($extend->toString());
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
	
	private function ensureUtf8($str) {
		if (preg_match('%^(?:
      [\x09\x0A\x0D\x20-\x7E]            # ASCII
    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
)*$%xs', $str))
			return $str;
		else
			return iconv('CP1252', 'UTF-8', $str);
	}
}
