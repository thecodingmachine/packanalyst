<?php

namespace Mouf\Packanalyst\Services;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;
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

    /**
     * Classes/interfaces/traits being used in currently parsed class/interface/trait/function.
     * The key is the class name.
     *
     * @var array<string, bool>
     */
    private $uses;

    public function __construct($package, ItemDao $itemDao)
    {
        $this->package = $package;
        $this->itemDao = $itemDao;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function enterNode(Node $node)
    {
        // Each time we enter in a class or interface or trait or function, we reset the "uses" array.
        if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Interface_
                || $node instanceof Stmt\Trait_ || $node instanceof Stmt\Function_) {
            $this->uses = [];
        }
    }

    public function leaveNode(Node $node)
    {
        /*if ($node instanceof Node\Name) {
            return new Node\Name($node->toString('_'));
        }*/
        if ($node instanceof Stmt\Class_ || $node instanceof Stmt\Interface_
            || $node instanceof Stmt\Trait_ || $node instanceof Stmt\Function_) {
            $item = [];

            if ($node->name === null) {
                // Anonymous class
                return;
            }
            $itemName = $node->namespacedName->toString();

            $item['name'] = $this->ensureUtf8($itemName);
            $comment = $node->getDocComment();
            if ($comment) {
                $item['phpDoc'] = $this->ensureUtf8($node->getDocComment()->getText());
            }

            $item['packageName'] = $this->package['packageName'];
            $item['packageVersion'] = $this->package['packageVersion'];
            $item['fileName'] = $this->fileName;
            unset($this->uses[$itemName]);
            $item['uses'] = array_keys($this->uses);

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
        /*} elseif ($node instanceof Node\Name) {
            $this->uses[$node->toString()] = true;*/
        } elseif ($node instanceof Stmt\Const_) {
            foreach ($node->consts as $const) {
                $this->uses[$const->namespacedName->toString()] = true;
            }
        } elseif ($node instanceof Expr\StaticCall
                  || $node instanceof Expr\StaticPropertyFetch
                  || $node instanceof Expr\ClassConstFetch
                  || $node instanceof Expr\New_
                  || $node instanceof Expr\Instanceof_
        ) {
            if ($node->class instanceof Name) {
                $className = $node->class->toString();
                $lowerClassName = strtolower($className);
                if ($lowerClassName != 'self' && $lowerClassName != 'parent' && $lowerClassName != 'parent') {
                    $this->uses[$className] = true;
                }
            }
        } elseif ($node instanceof Stmt\Catch_) {
            foreach ($node->types as $type) {
                $this->uses[$type->toString()] = true;
            }
        } /*elseif ($node instanceof Expr\FuncCall) {
            if ($node->name instanceof Name) {
        	echo $node->name->toString()."\n";
            	$this->uses[$node->name->toString()] = true;
            }
        }*/ /* elseif ($node instanceof Expr\ConstFetch) {
        	$this->uses[$node->name->toString()] = true;
        }*/ elseif ($node instanceof Stmt\TraitUse) {
     foreach ($node->traits as $trait) {
         $this->uses[$trait->toString()] = true;
     }
 } elseif ($node instanceof Node\Param
                  && $node->type instanceof Name
        ) {
     $this->uses[$node->type->toString()] = true;
 }
    }

    private function ensureUtf8($str)
    {
        if (preg_match('%^(?:
      [\x09\x0A\x0D\x20-\x7E]            # ASCII
    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
)*$%xs', $str)) {
            return $str;
        } else {
            return iconv('CP1252', 'UTF-8', $str);
        }
    }
}
