<?php

namespace App\Presenters;

use Intra\Model\Facade\BalikobotShopFacade;
use Nette;
use Nette\Application\UI\Form;
use Front\Model\Facade\CustomerFrontFacade;
use Intra\Components\MailSender\MailSender;
use Intra\Components\PDFPrinter\PDFPrinterControl;

class RegistrationCustomerPresenter extends BaseFrontPresenter
{
    /** @var CustomerFrontFacade @inject */
    public $cusFacade;

    /** @var BalikobotShopFacade @inject */
    public $balikobotShopFacade;

    /** @var MailSender @inject */
    public $mailSender;

    /** @var PDFPrinterControl @inject */
    public $printer;

    public function renderDefault() {
        $this->redirect(':Front:default');
    }

    public function renderProfil()
    {
        // Pojistka proti tomu, aby profil si mohl upravovat každý pouze svůj :)
        if (!$this->user->loggedIn) {
            $this->flashMessage('Pokud chcete upravovat své údaje, tak se prosím přihlaste.',
                'warning');
            $this->redirect(':Front:default');
        }
        $entity = $this->cusFacade->get()->find($this->user->getIdentity()->id);
        if (!$entity) {
            $this->flashMessage('Jako administrátor nemůžete měnit svůj profil.',
                'warning');
            $this->redirect(':Front:default');
        }
        $arr = $entity->toArray();
        unset($arr['password']);
        $this->template->customerArr = $arr;
        $this['ownRegistrationForm']->setDefaults($arr);
        $this['backOutForm']->setDefaults($arr);

        $this['reclamationForm']->setDefaults($arr);

        $form = $this->createComponentReclamationForm();
        $this->template->radios = $form['reclamation_way'];

        $this->template->orders = $orders = $this->facade->gEMOrders()->findBy([
            'email' => $entity->email,
            'currency not' => null,
            'orderState !=' => 9
        ], ['foundedDate' => 'DESC']);
        $allowTrace = [];
        $dpdTrack = [];
        $existShipments = [];
        foreach ($orders as $order) {
            $allowTrace[$order->id] = false;
            $balikobotPackages = $this->cusFacade->gEMBalikobotPackage()->findBy([
                'orders' => $order->id,
                'carrier_id !=' => null
            ]);
            if ($balikobotPackages && count($balikobotPackages)) {
                $allowTrace[$order->id] = true;
            }

            $dpdPackages = $this->cusFacade->gEMDpdPackage()->findBy([
                'order' => $order->id,
                'trackingId !=' => null
            ]);
            if ($dpdPackages) {
                foreach ($dpdPackages as $p) {
                    if (!in_array($p->shipmentId, $existShipments)) {
                        $dpdTrack[$order->id][] = $p;
                        $existShipments[] = $p->shipmentId;
                    }
                }
            }
        }
        $this->template->allowTrace = $allowTrace;
        $this->template->dpdTrack = $dpdTrack;

        $this->template->menuForDownload = $menuForDownload = $this->facade->gEMWebMenu()->findOneBy([
            'visible' => '1',
            'forDownload' => 1
        ]);

        if (count($menuForDownload)) {
            $this->template->resource = $this->facade->gEMWebResources()->findAssoc(['pageId' => $menuForDownload->id],
                'divId');
        }

    }

    public function renderTrace($id)
    {
        if ($id) {
            if ($this->user->loggedIn && $this->getUser()->roles[ 0 ] == 'visitor') {
                $this->template->packages = $packages = $this->cusFacade->gEMBalikobotPackage()->findBy(['orders' => $id]);
                $this->template->orderId = $id;
                $this->template->tracks = [];
                foreach ($packages as $package) {
                    $this->template->tracks[ $package->id ] = $this->balikobotShopFacade->track($package);
                }
            }
        } else {
            $this->flashMessage("Nepodařilo se vybrat objednávku.", 'warning');
            $this->redirect('Homepage:default');
        }

    }

    public function renderResetPassword($email)
    {
        $this[ 'resetForm' ]->setDefaults(['username' => $email]);
    }

    public function renderRegistration()
    {
        // Dohledání stránek pro obchodní podmínky a zásady ochrany osob. údajů
        $this->template->pageTerms = $this->facade->gEMWebMenu()->findOneBy(['visible' => '1', 'forTerms' => 1]);
        $this->template->pagePrinciples = $this->facade->gEMWebMenu()->findOneBy([
            'visible' => '1',
            'forPrinciples' => 1
        ]);
    }

    protected function createComponentOwnRegistrationForm()
    {
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
        $form->addText('country')
            ->setAttribute('placeholder', 'Stát')
            ->setValue('Česká repbulika')
            ->setRequired('Toto pole je povinné.');
        $form->addCheckbox('isCompany')
            ->setAttribute('id', 'typ_podnikatele')
            ->setAttribute('value', '1')
            ->setAttribute('style', 'display:none');
        $form->addText('company')
            ->setAttribute('placeholder', 'Název firmy');
        $form->addText('idNo')
            ->setAttribute('placeholder', 'IČO');
        $form->addText('vatNo')
            ->setAttribute('placeholder', 'DIČ');
        $form->addText('phone')
            ->setRequired('Prosím zadejte své telefonní číslo. V případě problému s objednávkou Vás na něm můžeme kontaktovat.')
            ->setAttribute('placeholder', 'Telefon');
        $form->addText('nameDelivery')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surnameDelivery')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('streetDelivery')
            ->setAttribute('placeholder', 'Ulice a č. p.');
        $form->addText('cityDelivery')
            ->setAttribute('placeholder', 'Město');
        $form->addText('zipDelivery')
            ->setAttribute('placeholder', 'PSČ');
        $form->addText('countryDelivery', 'Stát:')
            ->setValue('Česká repbulika')
            ->setAttribute('placeholder', 'Stát');
        $form->addText('phoneDelivery')
            ->setAttribute('placeholder', 'Telefon');
        $form->addCheckbox('deliveryToOther')
            ->setAttribute('id', 'dodaci_adresa_enable')
            ->setAttribute('class', 'scroll-to icheck icheck_blue')
            ->setAttribute('value', '1')
            ->setAttribute('data-scroll-to', '.dodaci_adresa_udaje');
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->addRule(Form::EMAIL, 'Prosím zadejte platný formát emailu.')
            ->setAttribute('placeholder', 'Email');
        $form->addPassword('password')
            ->setRequired(false)
            ->addRule(Form::MIN_LENGTH, 'Zadané heslo je příliš krátké, zvolte si heslo alespoň o %d znacích', 3)
            ->setAttribute('placeholder', 'Uživatelské heslo');
        $form->addPassword('password2')
            ->setRequired(false)
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form[ 'password' ])
            ->setAttribute('placeholder', 'Potvrzení hesla');
        $form->addCheckbox('vatPay')
            ->setAttribute('class', 'icheck icheck_blue');
        $form->addSubmit('send', 'Změnit údaje');
        $form->addHidden('id');
        $form->onSuccess[] = array($this, 'registrationOwnFormSucceededd');
        return $form;
    }

    public function registrationOwnFormSucceededd($form, $values)
    {
        try {
            $pass = null;
            if (isset($values[ 'password' ])) {
                if ($values[ 'password' ] != '') {
                    $pass = $values[ 'password' ];
                    $values[ 'password' ] = $this->cusFacade->hash($values[ 'password' ]);
                } else {
                    unset($values[ 'password' ]);
                }
            }
            $res = $this->cusFacade->saveCustomer($values, false);

            if (!$res[ 1 ]) {
                $this->flashMessage('Účet byl úspěšně upraven.', 'success');
            } else {
                $this->flashMessage('Účet byl úspěšně vytvořen. Na uvedený email Vám byly zaslány přístupové údaje.',
                    'success');
                $this->mailSender->sendCustomerCredential($res[ 0 ], $pass);
            }
        } catch (\Exception $e) {
            \Tracy\Debugger::log($e);
            $this->flashMessage('Nepodařilo se uložit registraci. Zřejmě email, který jste zadali již používá jiný uživatel.',
                'warning');
            return;
        }

        if ($this->sess->backUrl != null) {
            $this->redirect($this->sess->backUrl);
        } else {
            $this->redirect('this');
        }
    }

    protected function createComponentRegistrationForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('name')
            ->setRequired('Prosím zadejte své jméno')
            ->setAttribute('placeholder', 'Jméno');
        $form->addText('surname')
            ->setAttribute('placeholder', 'Příjmení');
        $form->addText('email')
            ->setRequired('Prosím zadejte svůj email.')
            ->addRule(Form::EMAIL, 'Prosím zadejte platný formát emailu.')
            ->setAttribute('placeholder', 'Email');
        $form->addPassword('password')
            ->setRequired('Toto pole je povinné')
            ->addRule(Form::MIN_LENGTH, 'Zadané heslo je příliš krátké, zvolte si heslo alespoň o %d znacích', 5)
            ->setAttribute('placeholder', 'Uživatelské heslo');
        $form->addPassword('password2')
            ->setRequired('Toto pole je povinné')
            ->addRule(Form::EQUAL, 'Zadané hesla se neshodují', $form[ 'password' ])
            ->setAttribute('placeholder', 'Potvrzení hesla');
        $form->addCheckbox('agree')
            ->setRequired('Toto pole je povinné.')
            ->setAttribute('class', 'icheck icheck_blue');
        $form->addCheckbox('personalData')
            ->setRequired('Toto pole je povinné.')
            ->setAttribute('class', 'icheck icheck_blue');
        $form->addCheckbox('newsletter')
            ->setAttribute('class', 'icheck icheck_blue');
        $form->addSubmit('send', 'registrovat');

        $form->addInvisibleReCaptcha('captcha', true, 'Nejste robot?');

        $form->onError[] = [$this, 'registrationFormError'];
        $form->onSuccess[] = [$this, 'registrationFormSucceededd'];
        return $form;
    }

    public function registrationFormError(Form $form)
    {
        if ($form->hasErrors()) {
            foreach ($form->getErrors() as $e) {
                $this->flashMessage($e, 'warning');
            }
        }
    }

    public function registrationFormSucceededd($form, $values)
    {
        try {
            $pass = null;
            if (isset($values[ 'password' ])) {
                if ($values[ 'password' ] != '') {
                    $pass = $values[ 'password' ];
                    $values[ 'password' ] = $this->cusFacade->hash($values[ 'password' ]);
                } else {
                    unset($values[ 'password' ]);
                }
            }
            $res = $this->cusFacade->saveCustomer($values, false);

            if ($values->newsletter) // pokud je newsletter, tak přidám email do db newsletteru
            {
                $this->facade->addNewsletterEmail(['email' => $values->email]);
            }

            if (!$res[ 1 ]) {
                $this->flashMessage('Účet byl úspěšně upraven.', 'success');
            } else {
                $this->flashMessage('Účet byl úspěšně vytvořen. Na uvedený email Vám byly zaslány přístupové údaje.',
                    'success');
                $this->mailSender->sendCustomerCredential($res[ 0 ], $pass);
            }

            // Přihlášení právě zaregistrovaného zákazníka
            $this->getUser()->login([$values->email, true], $pass);
            $this->getUser()->setExpiration('14 days', false);
        } catch (\Exception $e) {
            if (strpos($e, 'SQLSTATE[23000]')) {
                $this->flashMessage('Nepodařilo se uložit registraci. Zřejmě email, který jste zadali již používá jiný uživatel. Zkuste obnovu hesla.',
                    'warning');
            } else {
                \Tracy\Debugger::log($e);
                $this->flashMessage('Nepodařilo se uložit registraci. Nastala nečekaná chyba. Pokud se bude opakovat prosím kontaktujte nás.',
                    'error');
            }
            return;
        }
        if ($this->sess->backUrl != null) {
            $this->redirect($this->sess->backUrl);
        } else {
            $this->redirect(':Front:default');
        }
    }

    protected function createComponentResetForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Email:')
            ->setRequired('Prosím zadejte email, který jste vyplnili při registraci')
            ->setAttribute('placeholder', 'Email');

        $form->addSubmit('send', 'Obnovit heslo');

        $form->onSuccess[] = array($this, 'resetFormSucceeded');
        return $form;
    }

    public function resetFormSucceeded($form, $values)
    {
        $customer = $this->facade->gEMCustomer()->findOneBy(['email' => $values->username]);
        if (count($customer)) {
            $this->mailSender->sendCustomerCredential($customer);
        }
        $this->flashMessage('Na uvedený email Vám byly zaslány nové přístupové údaje.', 'success');
        $this->redirect(':SignCustomer:login');
    }


    protected function createComponentBackOutForm()
    {
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

    public function backOutFormSucceededd($form, $values)
    {

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
    }

    public function backOutFormError($form)
    {

        $this->flashMessage('Formulář odstoupení od smluvy se nepodařilo odeslat!', 'warning');
        foreach ($form->getErrors() as $err) {
            $this->flashMessage($err, 'warning');
        }

    }


    protected function createComponentReclamationForm()
    {
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

        $form->addRadioList("reclamation_way", null, [
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

    public function reclamationFormSucceededd($form, $values)
    {

        $this->mailSender->sendReclamation($values, $this->sess->actualCurrency);
        $this->flashMessage('Reklamační formulář byl úspěšně odeslán.', 'success');

        $this->redirect('this');

    }

    public function handlePrintInvoice($idInvoice, $isProforma = false, $isCreditNote = false)
    {
        if ($this->user->loggedIn && $this->getUser()->roles[ 0 ] == 'visitor') {

            $order = $this->facade->gEMOrders()->find($idInvoice);
            if (isset($order->customer->id) && $order->customer->id == $this->user->id) {
                $this->printer->handleCreateInvoice($idInvoice, $isProforma, $isCreditNote);
            } else {
                $this->flashMessage('K tomuto nemáte přístup!', 'error');
            }
        }
    }

    public function handlePrintReceipt($idRefund)
    {
        if ($this->user->loggedIn && $this->getUser()->isInRole('visitor')) {
            $refund = $this->facade->gEMOrderRefund()->find($idRefund);
            if ($refund && $refund->orders && $refund->orders->customer) {
                if ($refund->orders->customer->id == $this->user->id) {
                    $this->printer->handlePrintReceipt($refund);
                } else {
                    $this->flashMessage('K tomuto nemáte přístup!', 'error');
                }
            } else {
                $this->flashMessage('Úhrada nebyla nalezena!', 'warning');
            }
        }
    }

}
