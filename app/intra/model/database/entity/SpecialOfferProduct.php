<?php

namespace Intra\Model\Database\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Core\Model\Database\Utils\ABaseEntity;

/**
 * @ORM\Entity
 */
class SpecialOfferProduct extends ABaseEntity {

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
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Product", inversedBy="id")
	 * FORM type='select'
	 * FORM title='Produkt'
	 * FORM prompt='-- vyberte produkt'
	 * FORM data-entity-values=Intra\Model\Database\Entity\Product[$name$]['active' > '1' | 'saleTerminated' > '0'][]
	 * FORM attribute-class="form-control selectpicker"
	 * FORM attribute-data-live-search="true"
	 *
	 * GRID type='text'
	 * GRID title="Produkt"
	 * GRID entity-link='name'
	 * GRID visible='true'
	 * GRID entity='Intra\Model\Database\Entity\Product'
	 * GRID entity-alias='prod'
	 * GRID filter=single-entity #['name']
	 */
	protected $product;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * FORM type='datetime'
	 * FORM title="Od"
	 * FORM attribute-class='form-control input-md'
	 * FORM attribute-placeholder='od'
	 * FORM required='Toto pole je povinné'
	 *
	 * GRID type='datetime'
	 * GRID title="Od"
	 * GRID sortable='true'
	 * GRID filter='date-range'
	 * GRID visible='true'
	 */
	protected $timeFrom;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * FORM type='datetime'
	 * FORM title="Do"
	 * FORM attribute-class='form-control input-md'
	 * FORM attribute-placeholder='do'
	 * FORM required='Toto pole je povinné'
	 *
	 * GRID type='datetime'
	 * GRID title="Do"
	 * GRID sortable='true'
	 * GRID filter='date-range'
	 * GRID visible='true'
	 */
	protected $timeTo;

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
	 */
	protected $active;

	public function __construct($data = null) {
		parent::__construct($data);
	}

}

?>