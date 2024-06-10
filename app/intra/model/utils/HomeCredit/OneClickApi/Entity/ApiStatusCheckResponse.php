<?php

namespace HomeCredit\OneClickApi\Entity;

use HomeCredit\OneClickApi\AEntity;

class ApiStatusCheckResponse extends AEntity
{

	protected static $associations = [
		'build' => \HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build::class,
	];

	/**
	 * @var \HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build
	 * @required
	 */
	private $build;

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build $build
	 */
	public function __construct(
		\HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build $build
	)
	{
		$this->setBuild($build);
	}

	/**
	 * @return \HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build
	 */
	public function getBuild()
	{
		return $this->build;
	}

	/**
	 * @param \HomeCredit\OneClickApi\Entity\ApiStatusCheckResponse\Build $build
	 * @return $this
	 */
	public function setBuild($build)
	{
		$this->assertNotNull($build);
		$this->build = $build;
		return $this;
	}

}
