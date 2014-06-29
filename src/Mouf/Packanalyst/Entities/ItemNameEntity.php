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
	
	
	
}
