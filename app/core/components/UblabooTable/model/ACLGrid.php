<?php

namespace App\Core\Components\UblabooTable\Model;

use Nette\Application\UI;
use Ublaboo\DataGrid\DataGrid;
use Nette\ComponentModel\IContainer;
use App\Core\Model\Database\Entity\PermisionItem;

class ACLGrid extends DataGrid {

	/** mixed entity */
	public $entity;

	/** @var mixed */
	public $presenter;

	/** messages on success form */
	public $messageOk;

	/** messages on error form */
	public $messageEr;

	/** array of foreign entity for save method */
	public $arrayForeignEntity = NULL;

	/** @var array */
	public $inlineSettings;

	/** @var Identity */
	private $user;

	/** @var string  */
	private $namePresenter;

	/** @var ACLMapper  */
	private $mapper;

	/** @var string */
	private $nameGrid;

	/**
	 * Construct for ACL Grid
	 * @param Identity $user actual user
	 * @param string $presenter name of actual presenter
	 * @param string $function name fo actual function
	 * @param ACLMapper $mapper
	 * @param IContainer $parent
	 * @param type $name
	 */
	public function __construct($user, $presenter, $function, $mapper, IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->user = $user;
		$this->namePresenter = $presenter;
		$this->nameGrid = $function;
		$this->mapper = $mapper;
	}

	//TODO phpdoc
	public function addColumnText($key, $name, $column = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnText($key, $name, $column);
			return $item;
		}
		return NULL;
	}

	/**
	 * Add column to grid. column contend is translate by translator
	 * @param type $key
	 * @param type $name
	 * @param type $column
	 * @return type
	 */
	public function addColumnTranslateText($key, $name, $column = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$this->addColumnCheck($key);
			$column = $column ? : $key;
			$item = $this->addColumn($key, new ColumnTextTranslate($this, $key, $column, $name));
			return $item;
		}
		return NULL;
	}

	//TODO phpdoc
	public function addColumnNumber($key, $name, $column = NULL) {
		if (strtolower($key) == 'id') //for Id dont mapping
			return parent::addColumnNumber($key, $name, $column);

		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnNumber($key, $name, $column);
			return $item;
		}
		return NULL;
	}

	//@TODO phpdoc
	public function addColumnDateTime($key, $name, $column = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnDateTime($key, $name, $column);
			return $item;
		}
		return NULL;
	}

	//@TODO phpDoc
	public function addColumnLink($key, $name, $href = NULL, $column = NULL, array $params = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnLink($key, $name, $href, $column, $params);
			return $item;
		}
		return NULL;
	}

	//@TODO phpDoc
	public function addAction($key, $name, $href = NULL, array $params = NULL, $linkRow = false) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name == '' ? 'Akce: ' . $key : $name);

		/* if ($linkRow) {
		  $this->setRowCallback(function($item, $tr) {
		  $tr->addClass('clickable-row');
		  $par = [];
		  if ($params != NULL) {
		  $par = $params;
		  }
		  $par['id'] = $item->id;
		  $link = $this->link($href, $par);
		  $tr->addAttribute('data-href', $link);
		  });
		  } */

		if ($action == 'write' || $action == 'read') {
			$item = parent::addAction($key, $name, $href, $params);
			return $item;
		}
		return NULL;
	}

	//@TODO phpDoc
	public function addColumnStatus($key, $name, $column = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnStatus($key, $name, $column);
			return $item;
		}
		return NULL;
	}

	//@TODO phpDoc
	public function addColumnBoolean($key, $name, $column = NULL) {
		$action = $this->mapper->mapInput($this->user, $this->namePresenter, $this->nameGrid, $key, $name);

		if ($action == 'write' || $action == 'read') {
			$item = parent::addColumnStatus($key, $name, $column);
			$item->setTemplate(__DIR__ . '/../templates/column_boolean.latte');


			return $item;
		}
		return NULL;
	}

	/**
	 * Set entity in grid
	 * @param type $entity
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
	}

	/**
	 * Return entity in grid
	 * @return type
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Return name of grid
	 * @return type
	 */
	public function getNameGrid() {
		return $this->nameGrid;
	}

	/**
	 * Return name of grid
	 * @return type
	 */
	public function getQB() {
		return $this->dataModel->getDataSource();
	}

	/**
	 * Return name parent presenter
	 * @return type
	 */
	public function getNamePresenter() {
		return $this->namePresenter;
	}

	/**
	 * Return mapper
	 * @return type
	 */
	public function getMapper() {
		return $this->mapper;
	}

	/**
	 * Return user
	 * @return type
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * Set messages on success form - succ save and err save
	 * @param array $messageOk [text, type]
	 * @param array $messageEr [text, type]
	 */
	public function setMessages($messageOk, $messageEr, $presenter) {
		$this->messageOk = $messageOk;
		$this->messageEr = $messageEr;
		$this->presenter = $presenter;
	}

}
