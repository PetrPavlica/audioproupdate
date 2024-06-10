<?php

namespace HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse;

use HomeCredit\OneClickApi\AEntity;

class Build extends AEntity
{

	/**
	 * ATL version
	 *
	 * @var string
	 * @required
	 */
	private $version;

	/**
	 * Maven artifact name
	 *
	 * @var string
	 * @required
	 */
	private $artifact;

	/**
	 * Official artifact name (human readable)
	 *
	 * @var string
	 * @required
	 */
	private $name;

	/**
	 * Artifact group name
	 *
	 * @var string
	 * @required
	 */
	private $group;

	/**
	 * Artifact (build) creation time (timestamp)
	 *
	 * @var float
	 * @required
	 */
	private $time;

	/**
	 * @param string $version
	 * @param string $artifact
	 * @param string $name
	 * @param string $group
	 * @param float $time
	 */
	public function __construct(
		$version,
		$artifact,
		$name,
		$group,
		$time
	)
	{
		$this->setVersion($version);
		$this->setArtifact($artifact);
		$this->setName($name);
		$this->setGroup($group);
		$this->setTime($time);
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function getArtifact()
	{
		return $this->artifact;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @return float
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @param string $version
	 * @return $this
	 */
	public function setVersion($version)
	{
		$this->assertNotNull($version);
		$this->version = $version;
		return $this;
	}

	/**
	 * @param string $artifact
	 * @return $this
	 */
	public function setArtifact($artifact)
	{
		$this->assertNotNull($artifact);
		$this->artifact = $artifact;
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->assertNotNull($name);
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string $group
	 * @return $this
	 */
	public function setGroup($group)
	{
		$this->assertNotNull($group);
		$this->group = $group;
		return $this;
	}

	/**
	 * @param float $time
	 * @return $this
	 */
	public function setTime($time)
	{
		$this->assertNotNull($time);
		$this->time = $time;
		return $this;
	}

}
