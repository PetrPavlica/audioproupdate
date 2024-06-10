<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 */
class NewsletterEmail extends ABaseEntity {

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * FORM type="hidden"
	 *
	 * GRID type='number'
	 * GRID title="Id"
	 * GRID sortable='true'
	 * GRID visible='true'
	 * GRID align='left'
	 * GRID inline-type='id'
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=true, unique=true)
	 * FORM type='email'
	 * FORM title="Email"
	 * FORM attribute-placeholder='Email'
	 * FORM attribute-class='form-control input-md'
	 *
	 * GRID type='text'
	 * GRID title="Email"
	 * GRID sortable='true'
	 * GRID filter='single'
	 * GRID visible='true'
	 * GRID inline-type='text'
	 */
	protected $email;

	/**
	 * @ORM\Column(type="boolean")
	 * FORM type='checkbox'
	 * FORM title=" Aktivní (zobrazen v eshopu)"
	 * FORM default-value='true'
	 *
	 * GRID type='bool'
	 * GRID title="Aktivní"
	 * GRID sortable='true'
	 * GRID filter=select #['' > 'Vše'|'0' > 'Neaktivní'|'1' > 'Aktivní']
	 * GRID visible='true'
	 * GRID align='center'
	 * GRID inline-type='checkbox'
	 */
	protected $active;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * FORM type='date'
	 * FORM title="Datum založení"
	 * FORM attribute-class='form-control input-md'
	 *
	 * GRID type='datetime'
	 * GRID title="Datum založení"
	 * GRID sortable='true'
	 * GRID filter='date-range'
	 * GRID visible='true'
	 */
	protected $foundedDate;

	public function __construct($data = null) {
		$this->foundedDate = new DateTime();
		parent::__construct($data);
	}

}

?>