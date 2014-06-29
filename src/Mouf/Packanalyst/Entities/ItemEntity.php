<?php
namespace Mouf\Packanalyst\Entities;

use HireVoice\Neo4j\Annotation as OGM;

/**
 * An object representing a PHP class, interface, trait or function.
 * 
 * @author david
 * @OGM\Entity(labels="Item", repositoryClass="Mouf\Packanalyst\Repositories\ItemRepository")
 */
class ItemEntity
{
	const TYPE_CLASS = "class";
	const TYPE_INTERFACE = "interface";
	const TYPE_TRAIT = "trait";
	const TYPE_FUNCTION = "function";
	
	/**
	 * The internal node ID from Neo4j must be stored. Thus an Auto field is required
	 * @OGM\Auto
	 * @var string
	 */
	protected $id;
	
	/**
	 * @OGM\Property
	 * @OGM\Index
	 * @var string
	 */
	protected $name;
	
	/**
	 * The relationship entity
	 * 
	 * @OGM\ManyToOne(relation="is-a")
	 * @var ItemName
	 */
	protected $itemName;
	
	/**
	 * @OGM\Property
	 * @var string
	 */
	protected $type;
	
	/**
	 * @OGM\Property
	 * @var string
	 */
	protected $phpDoc;
	
	/**
	 * @OGM\Property
	 * @var bool
	 */
	protected $isFinal;
	
	/**
	 * @OGM\Property
	 * @var bool
	 */
	protected $isAbstract;
	
	/**
	 * @OGM\ManyToOne(relation="belongs-to")
	 * @var PackageVersionEntity
	 */
	protected $packageVersion;
	
	/**
	 * @OGM\ManyToMany(relation="inherits")
	 * @var array<ItemEntity>
	 */
	protected $inherits = array();
	
	/**
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 *
	 * @param string $id        	
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 *
	 * @param string $name        	
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 *
	 * @return the string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 *
	 * @param string $type        	
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getPhpDoc() {
		return ($this->phpDoc !== null)?$this->phpDoc:"";
	}
	
	/**
	 *
	 * @param string $phpDoc        	
	 */
	public function setPhpDoc($phpDoc) {
		$this->phpDoc = $phpDoc;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getIsFinal() {
		return $this->isFinal;
	}
	
	/**
	 *
	 * @param bool $isFinal        	
	 */
	public function setIsFinal($isFinal) {
		$this->isFinal = $isFinal;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getIsAbstract() {
		return $this->isAbstract;
	}
	
	/**
	 *
	 * @param bool $isAbstract        	
	 */
	public function setIsAbstract($isAbstract) {
		$this->isAbstract = $isAbstract;
		return $this;
	}
	
	/**
	 *
	 * @return PackageVersionEntity
	 */
	public function getPackageVersion() {
		return $this->packageVersion;
	}
	
	/**
	 *
	 * @param PackageVersionEntity $packageVersion        	
	 */
	public function setPackageVersion(PackageVersionEntity $packageVersion) {
		$this->packageVersion = $packageVersion;
		return $this;
	}
	
	/**
	 *
	 * @return array<ItemEntity>
	 */
	public function getInherits() {
		return $this->inherits;
	}
	
	/**
	 *
	 * @param $inherits array<ItemNameEntity>
	 */
	public function setInherits($inherits) {
		$this->inherits = $inherits;
		return $this;
	}
	
	public function getItemName() {
		return $this->itemName;
	}
	
	/**
	 * 
	 * @param ItemNameEntity $itemName
	 */
	public function setItemName(ItemNameEntity $itemName) {
		$this->itemName = $itemName;
		return $this;
	}
}
