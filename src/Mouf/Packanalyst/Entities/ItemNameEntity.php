<?php
namespace Mouf\Packanalyst\Entities;

use HireVoice\Neo4j\Annotation as OGM;

/**
 * An object representing a the name of a PHP class, interface, trait or function.
 * 
 * @author david
 * @OGM\Entity(labels="ItemName", repositoryClass="Mouf\Packanalyst\Repositories\ItemNameRepository")
 */
class ItemNameEntity
{
	/**
	 * The internal node ID from Neo4j must be stored. Thus an Auto field is required
	 * @OGM\Auto
	 * @var string
	 */
	protected $id;
	
	/**
	 * @OGM\Property
	 * @OGM\Index(name="itemNameIdx")
	 * @var string
	 */
	protected $name;
	
	/**
	 * The inverse relationship of "is-a".
	 * We need a relationship in both way to write queries more easily.
	 * 
	 * @OGM\ManyToMany(relation="is-a-reverse")
	 * @var array<ItemEntity>
	 */
	protected $items = array();
	
	
	public function getId() {
		return $this->id;
	}
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	public function getName() {
		return $this->name;
	}
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	public function getItems() {
		return $this->items;
	}
	public function setItems($items) {
		$this->items = $items;
		return $this;
	}
	/**
	 * Adds the item to the list if items that share this itemname.
	 * Called by ItemEntity::setItemName
	 * 
	 * @param ItemEntity $item
	 */
	public function addItem(ItemEntity $item) {
		foreach ($this->items as $itemBean) {
			if ($itemBean == $item) {
				return;
			}
		}
		$this->items[] = $item;
	}
	
	
	
	
}
