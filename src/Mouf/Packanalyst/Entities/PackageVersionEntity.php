<?php
namespace Mouf\Packanalyst\Entities;

use HireVoice\Neo4j\Annotation as OGM;

/**
 * 
 * @author david
 * @OGM\Entity(labels="PackageVersion", repositoryClass="Mouf\Packanalyst\Repositories\PackageVersionRepository")
 */
class PackageVersionEntity
{
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
	protected $packageName;
	
	/**
	 * @OGM\Property
	 * @OGM\Index
	 * @var string
	 */
	protected $version;

	/**
	 * @OGM\Property(format="date")
	 * @var \DateTime
	 */
	protected $releaseDate;
	
	/**
	 * @OGM\ManyToOne(relation="version-of")
	 * @var PackageEntity
	 */
	protected $package;
	
	/**
	 * @OGM\Property
	 * @OGM\Index
	 * @var bool
	 */
	protected $onError;
	
	/**
	 * @OGM\Property
	 * @var string
	 */
	protected $errorMsg;
	
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
	 * @return string
	 */
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 *
	 * @param string $version        	
	 */
	public function setVersion($version) {
		$this->version = $version;
		return $this;
	}
	
	/**
	 *
	 * @return \DateTime
	 */
	public function getReleaseDate() {
		return $this->releaseDate;
	}
	
	/**
	 *
	 * @param \DateTime $releaseDate        	
	 */
	public function setReleaseDate(\DateTime $releaseDate) {
		$this->releaseDate = $releaseDate;
		return $this;
	}
	
	/**
	 *
	 * @return PackageEntity
	 */
	public function getPackage() {
		return $this->package;
	}
	
	/**
	 *
	 * @param PackageEntity $package        	
	 */
	public function setPackage(PackageEntity $package) {
		$this->package = $package;
		return $this;
	}
	
	/**
	 *
	 * @return bool
	 */
	public function getOnError() {
		return $this->onError;
	}
	
	/**
	 *
	 * @param bool $onError        	
	 */
	public function setOnError($onError) {
		$this->onError = $onError;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getErrorMsg() {
		return $this->errorMsg;
	}
	
	/**
	 *
	 * @param string $errorMsg        	
	 */
	public function setErrorMsg($errorMsg) {
		$this->errorMsg = $errorMsg;
		return $this;
	}
	
	
	
	
}
