<?php
namespace Mouf\Packanalyst\Entities;

use HireVoice\Neo4j\Annotation as OGM;

/**
 * 
 * @author david
 * @OGM\Entity(labels="Package", repositoryClass="Mouf\Packanalyst\Repositories\PackageRepository")
 */
class PackageEntity
{
	/**
	 * The internal node ID from Neo4j must be stored. Thus an Auto field is required
	 * @OGM\Auto
	 */
	protected $id;
	
	/**
	 * @OGM\Property
	 * @OGM\Index(name="packageNameIdx")
	 */
	protected $packageName;
	
	/**
	 * @OGM\Property
	 * @var int
	 */
	protected $nbDownloads;
	
	/**
	 * @OGM\Property
	 * @var int
	 */
	protected $nbStars;
		
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
	public function getPackageName() {
		return $this->packageName;
	}
	
	/**
	 *
	 * @param string $packageName        	
	 */
	public function setPackageName($packageName) {
		$this->packageName = $packageName;
		return $this;
	}
	
	/**
	 *
	 * @return int
	 */
	public function getNbDownloads() {
		return $this->nbDownloads;
	}
	
	/**
	 *
	 * @param int $nbDownloads        	
	 */
	public function setNbDownloads($nbDownloads) {
		$this->nbDownloads = $nbDownloads;
		return $this;
	}
	
	/**
	 *
	 * @return int
	 */
	public function getNbStars() {
		return $this->nbStars;
	}
	
	/**
	 *
	 * @param int $nbStars        	
	 */
	public function setNbStars($nbStars) {
		$this->nbStars = $nbStars;
		return $this;
	}
	
	
	
}
