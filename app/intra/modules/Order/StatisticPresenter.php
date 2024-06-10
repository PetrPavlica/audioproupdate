<?php

namespace App\Presenters;

use Intra\Model\Utils\DPHCounter;
use Nette;
use App\Core\Model\Database\Entity\PermisionItem;
use Intra\Model\Facade\ProductFacade;
use Nette\Application\UI\Form;

class StatisticPresenter extends BaseIntraPresenter {

    /** @var ProductFacade @inject */
    public $prodFacade;

    /**
     * ACL name='Domovská stránka'
     */
    public function startup() {
        parent::startup();
        $this->acl->mapFunction(NULL, $this->user, get_class(), __FUNCTION__, PermisionItem::TYPE_PRESENTER);
    }

    /**
     * ACL name='Zobrazení - default - zisk'
     */
    public function renderDefault($dateFrom = null, $dateTo = null) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($dateFrom && $dateTo) {
            $this->template->items = $this->prodFacade->getProfitByTime($dateFrom, $dateTo);

            $this[ 'rangeForm' ]->setDefaults([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]);
        }

        $this[ 'rangeForm' ]->setDefaults([
            'type' => 'default']);

        $this->template->dphCounter = new DPHCounter();
        $this->template->defaultVat = $this->prodFacade->gEMVat()->findOneBy(['defaultVal' => 1]);
    }


    /**
     * ACL name='Zobrazení - cash - cashFlow'
     */
    public function renderCash($dateFrom = null, $dateTo = null) {
        $this->acl->mapFunction($this, $this->user, get_class(), __FUNCTION__);
        if ($dateFrom && $dateTo) {
            $this->template->cashFlow = $this->prodFacade->getCashFlowByTime($dateFrom, $dateTo);

            $this[ 'rangeForm' ]->setDefaults([
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo
            ]);
        }

        $this[ 'rangeForm' ]->setDefaults([
            'type' => 'cash']);
    }


    public function createComponentRangeForm() {
        $form = new Form();
        $form->addText('dateFrom', 'Datum od')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setRequired(true, 'Toto pole je povinné!')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control');

        $form->addText('dateTo', 'Datum do')
            ->setAttribute('data-provide', 'datepicker')
            ->setAttribute('data-date-orientation', 'bottom')
            ->setAttribute('data-date-format', 'd. m. yyyy')
            ->setAttribute('data-date-today-highlight', 'true')
            ->setAttribute('data-date-autoclose', 'true')
            ->setRequired(true, 'Toto pole je povinné!')
            ->addRule(Form::PATTERN, 'Datum musí být ve formátu 15. 10. 2011', '([0-9]{1,2})(\.|\.\s)([0-9]{1,2})(\.|\.\s)([0-9]{4})')
            ->setAttribute('class', 'form-control');

        $form->addHidden('type');

        $form->addSubmit('send', 'Vybrat')
            ->setAttribute('class', 'btn btn-default');

        $form->onSuccess[] = [$this, 'rangeSuccess'];

        return $form;
    }

    public function rangeSuccess($form, $values) {
        $this->redirect('Statistic:' . $values->type, [
            'dateFrom' => $values->dateFrom,
            'dateTo' => $values->dateTo
        ]);
    }
}
