<?php

namespace HomeCredit\OneClickApi\Entity\MarkOrderAsDeliveredResponse\Items;

use HomeCredit\OneClickApi\AEntity;

class Image extends AEntity
{

	/**
	 * File name.
	 *
	 * @var string
	 * @required
	 */
	private $filename;

	/**
	 * File URL.
	 *
	 * @var string|null
	 */
	private $url;

	/**
	 * File content (Base64 encoded, maximum size of encoded string is 500kB)
	 *
	 * @var string|null
	 */
	private $content;

	/**
	 * @param string $filename
	 * @param string|null $url
	 * @param string|null $content
	 */
	public function __construct(
		$filename,
		$url = null,
		$content = null
	)
	{
		$this->setFilename($filename);
		$this->setUrl($url);
		$this->setContent($content);
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @return string|null
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @return string|null
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param string $filename
	 * @return $this
	 */
	public function setFilename($filename)
	{
		$this->assertNotNull($filename);
		$this->filename = $filename;
		return $this;
	}

	/**
	 * @param string|null $url
	 * @return $this
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * @param string|null $content
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

}
