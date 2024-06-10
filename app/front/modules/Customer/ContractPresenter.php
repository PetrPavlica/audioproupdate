<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Front\Model\Facade\CustomerFrontFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Components\PDFPrinter\PDFPrinterControl;

class ContractPresenter extends BaseFrontPresenter {

    /** @var CustomerFrontFacade @inject */
    public $cusFacade;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var PDFPrinterControl @inject */
    public $printer;

    public function renderBackOut($id) {

        if ($id) {

            // Pojistka proti tomu, aby mohl odstoupit od smlouvy pouze přihlášený a konkrétní uživatel
            if (!($this->user->loggedIn) || $id != $this->user->getIdentity()->id) {
                $this->flashMessage('Vyplňovat formuláře odstoupení od smlouvy a reklamace, může pouze autorizivaný uživatel.', 'warning');
                $this->redirect(':Front:default');
                exit;
            }

            $entity = $this->cusFacade->get()->find($id);
            $arr = $entity->toArray();
            unset($arr[ 'password' ]);
            $this->template->customerArr = $arr;

            $this[ 'backOutForm' ]->setDefaults($arr);

        } else {
            $this->redirect(':Front:default');
            $this->flashMessage('Do této sekce nemáte přístup', 'warning');
        }

    }

    protected function createComponentBackOutForm() {
        $form = new Form;

        $form->setTranslator($this->translator);
        $form->addText('name')
            ->setRequired('Prosím zadejte své jméno')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surname')
            ->setRequired('Prosím zadejte své příjmení')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('street')
            ->setAttribute('placeholder', 'Ulice a č. p.')
            ->setRequired('Prosím vyplňte svou ulici.');
        $form->addText('city')
            ->setAttribute('placeholder', 'Město')
            ->setRequired('Prosím vyplňte své město.');
        $form->addText('zip')
            ->setAttribute('placeholder', 'PSČ')
            ->setRequired('Prosím vyplňte své PSČ.');
        $form->addText('phone')
            ->setRequired('Prosím zadejte své telefonní číslo.')
            ->setAttribute('placeholder', 'Telefon');
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->addRule(Form::EMAIL, 'Prosím zadejte platný formát emailu.')
            ->setAttribute('placeholder', 'Email');

        $form->addText('product')
            ->setRequired('Prosím zadejte označení zboží')
            ->setAttribute('placeholder', 'Označení zboží');

        $form->addText('conclusion_date')
            ->setRequired('Prosím zadejte datum uzavření smlouvy')
            ->setAttribute('class', 'date_picker')
            ->setAttribute('placeholder', 'dd.mm.yyyy');
        //->setType('date');

        $form->addText('taken_date')
            ->setRequired('Prosím zadejte datum převzetí zboží')
            ->setAttribute('class', 'date_picker')
            ->setAttribute('placeholder', 'dd.mm.yyyy');
        // ->setType('date');

        $form->addText('product_price')
            ->setAttribute('placeholder', 'Kupní cena')
            ->setRequired('Prosím zadejte kupní cenu')
            ->setRequired(true);

        $form->addText('postage_price')
            ->setAttribute('placeholder', 'Cena poštovného')
            ->setRequired('Prosím zadejte cenu poštovného')
            ->setRequired(true);

        $form->addText('bank_account', 'Číslo účtu')
            ->setRequired('Prosím zadejte své číslo účtu')
            ->setAttribute('placeholder', 'Číslo účtu');

        $form->addUpload('attachment')
            ->setRequired('Prosím vyberte přílohu koupního dokladu')
            ->addRule(Form::MIME_TYPE, 'Soubor musí být formátu PDF', 'application/pdf')
            ->addRule(Form::MAX_FILE_SIZE, 'Maximální možná velikost šablony je 60 Mb', 60072 * 1024/* v bytech */);

        $form->addSubmit('send', 'Odeslat formulář');
        $form->addHidden('id');

        $form->onSuccess[] = array($this, 'backOutFormSucceededd');
        $form->onError[] = [$this, 'backOutFormError'];

        return $form;
    }

    public function backOutFormSucceededd($form, $values) {

        if (isset($values->attachment) && $values->attachment->error == UPLOAD_ERR_OK) {

            $type = substr($values->attachment->name, strrpos($values->attachment->name, '.'));
            $tmp = 'tmp_files/' . str_replace(".", "", microtime(true)) . $type;
            $values->attachment->move($tmp);

        } else {
            $tmp = null;
        }

        $this->mailSender->sendBackOutOfContract($values, $this->sess->actualCurrency, $tmp);
        $this->flashMessage('Odstoupení od smlouvy bylo úspěšně odesláno.', 'success');

        unlink($tmp);

        $this->redirect('this');
    }

    public function backOutFormError($form) {

        $this->flashMessage('Formulář odstoupení od smluvy se nepodařilo odeslat!', 'warning');
        foreach ($form->getErrors() as $err) {
            $this->flashMessage($err, 'warning');
        }

    }


    protected function createComponentReclamationForm() {
        $form = new Form;

        $form->setTranslator($this->translator);
        $form->addText('name')
            ->setRequired('Prosím zadejte své jméno')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surname')
            ->setRequired('Prosím zadejte své příjmení')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('street')
            ->setAttribute('placeholder', 'Ulice a č. p.')
            ->setRequired('Prosím vyplňte svou ulici.');
        $form->addText('city')
            ->setAttribute('placeholder', 'Město')
            ->setRequired('Prosím vyplňte své město.');
        $form->addText('zip')
            ->setAttribute('placeholder', 'PSČ')
            ->setRequired('Prosím vyplňte své PSČ.');
        $form->addText('phone')
            ->setRequired('Prosím zadejte své telefonní číslo.')
            ->setAttribute('placeholder', 'Telefon');
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->addRule(Form::EMAIL, 'Prosím zadejte platný formát emailu.')
            ->setAttribute('placeholder', 'Email');

        $form->addText('product')
            ->setRequired('Prosím zadejte reklamované zboží')
            ->setAttribute('placeholder', 'Reklamované zboží');

        $form->addText('buy_date')
            ->setRequired('Prosím zadejte datum prodeje')
            ->setAttribute('class', 'date_picker')
            ->setAttribute('placeholder', 'dd.mm.yyyy');
        // ->setType('date');

        $form->addText('purchase_number')
            ->setAttribute('placeholder', 'Číslo kupního dokladu')
            ->setRequired('Prosím zadejte číslo kupního dokladu');

        $form->addTextArea('description')
            ->setAttribute('placeholder', 'Popis závady')
            ->setRequired('Prosím popište závadu zboží');

        $form->addTextArea('package_content')
            ->setAttribute('placeholder', 'Obsah balení při předání')
            ->setRequired('Prosím popište obsah balení');

        $form->addRadioList("reclamation_way", NULL, [
            'oprava' => 'oprava',
            'výměna' => 'výměna',
            'sleva' => 'sleva',
            'odstoupení od smlouvy' => 'odstoupení od smlouvy',
        ])->setRequired('Prosím vyberte způsob reklamace');

        $form->addText('reclamation_date')
            ->setRequired('Prosím zadejte datum vaší reklamace')
            ->setAttribute('class', 'date_picker')
            ->setAttribute('placeholder', 'dd.mm.yyyy');
        //   ->setType('date');

        $form->addSubmit('send', 'Odeslat formulář');
        $form->addHidden('id');
        $form->onSuccess[] = array($this, 'reclamationFormSucceededd');

        return $form;
    }

    public function reclamationFormSucceededd($form, $values) {

        $this->mailSender->sendReclamation($values, $this->sess->actualCurrency);
        $this->flashMessage('Reklamační formulář byl úspěšně odeslán.', 'success');

        $this->redirect('this');
    }

}
