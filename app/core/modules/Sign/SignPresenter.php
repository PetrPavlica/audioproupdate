<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Minetro\Forms\reCAPTCHA\ReCaptchaField;
use Minetro\Forms\reCAPTCHA\ReCaptchaHolder;

class SignPresenter extends BasePresenter {
    /* Session */

    protected $sess;

    public function startup() {
        parent::startup();
        $this->sess = $this->session->getSection('singIn');
        //if (!isset($this->sess->countLogin)) {
        $this->sess->countLogin = 1;
        //}
    }

    protected function beforeRender() {
        // If user is loggin, redirect to homepage
        if ($this->user->loggedIn && !$this->getUser()->isInRole('visitor')) {
            $this->redirect(':Homepage:default');
        }
    }

    protected function createComponentSignInForm() {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Prosím zadejte své uživatelské jméno.')
                ->setAttribute('placeholder', 'Přihlašovací jméno')
                ->setAttribute('autofocus')
                ->setAttribute('class', 'form-control');

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Prosím vyplňte své heslo')
                ->setAttribute('placeholder', 'Heslo')
                ->setAttribute('autofocus')
                ->setAttribute('class', 'form-control');

        $form->addSubmit('send', 'Přihlásit se')
                ->setAttribute('class', 'btn btn-lg btn-success btn-block');

        $form->onSuccess[] = array($this, 'signInddFormSucceededd');
        return $form;
    }

    public function signInddFormSucceededd($form, $values) {
        $this->sess->countLogin++;
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->getUser()->setExpiration('14 days', FALSE);
            $this->flashMessage('Přihlášení bylo úspěšné!', 'success');
            $_SESSION['editingAllow'] = TRUE;
            unset($this->sess->countLogin);
        } catch (AuthenticationException $e) {
            $this->flashMessage('Špatné přihlašovací údaje', 'danger');
        }
    }

}
