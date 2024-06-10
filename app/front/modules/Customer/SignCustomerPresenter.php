<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

use Nette\Security\Passwords;

class SignCustomerPresenter extends BaseFrontPresenter
{

    public function renderLogin($back)
    {
        if ($back) {
            $this->sess->backUrl = $back;
            $this->template->backUrl = $back;
        } else {
            $this->sess->backUrl = null;
        }
    }

    protected function createComponentSignInForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('username', 'Email:')
            ->setRequired('Prosím zadejte email, který jste vyplnili při registraci')
            ->setAttribute('placeholder', 'Email');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo')
            ->setAttribute('placeholder', 'Heslo');

        $form->addSubmit('send', 'Přihlásit se');

        $form->onSuccess[] = array($this, 'signInddFormSucceededd');
        return $form;
    }

    public function signInddFormSucceededd($form, $values)
    {
        try {
            $this->getUser()->login([$values->username, true], $values->password);
            $this->getUser()->setExpiration('14 days', false);
            $this->basketFacade->addCustomerToOrder($this->getUser(), $this->sess);
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
            $this->flashMessage('Přihlášení bylo úspěšné!', 'success');
            if ($this->sess->backUrl != null) {
                $this->redirect($this->sess->backUrl);
            } else {
                $this->redirect(':RegistrationCustomer:profil', ['id' => $this->getUser()->id]);
            }
        } catch (AuthenticationException $e) {
            $this->flashMessage('Špatné přihlašovací údaje', 'danger');
        }
    }

    public function renderFBLogin()
    {

        $user = $this->getHttpRequest()->getPost();

        if (isset($user[ "userId" ]) && isset($user[ "key" ])) {

            $customer = $this->facade->gEMCustomer()->findOneBy(["fbId" => $user[ "userId" ]]);

            if ($customer) {
                $this->redirect('loginCustomer!', ["fbId" => $user[ "userId" ]]);
            } else {
                $this->sess->FBUser = $user;
                $this->redirect(':SignCustomer:FBCallback');
            }

        } else {
            $this->flashMessage('Nebyla předána správná data', 'danger');
        }

        $this->payload->completed = 1;

        die;
    }

    public function createComponentEmailRequireForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addText('email', 'Email:')
            ->setRequired('Prosím zadejte váš email')
            ->setAttribute('placeholder', 'Email');
        $form->addSubmit('send', 'Pokračovat');

        $form->onSuccess[] = array($this, 'emailRequireFormSucceededd');
        return $form;
    }

    public function renderFBCallback()
    {

    }

    public function emailRequireFormSucceededd($form, $values)
    {

        $email = $this->getHttpRequest()->getPost("email");
        $this->sess->FBUser[ "email" ] = $email;

        $customer = $this->facade->gEMCustomer()->findOneBy(["email" => $email]);

        if ($customer) {
            $this->redirect(':SignCustomer:accountsMerge');
        } else {
            $this->handleFinishFBLogin();
        }

        $this->redirect(':SignCustomer:FBCallback');
    }

    public function createComponentMergeAccountsForm()
    {
        $form = new Form;
        $form->setTranslator($this->translator);
        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím vyplňte své heslo')
            ->setAttribute('placeholder', 'Heslo');

        $form->addSubmit('send', 'Pokračovat');

        $form->onSuccess[] = array($this, 'mergeAccountsFormSucceededd');
        return $form;
    }

    public function renderAccountsMerge()
    {

        $user = $this->sess->FBUser;

        $template = $this->template;
        $template->email = $user[ "email" ];

        return $template;
    }

    public function mergeAccountsFormSucceededd($form, $values)
    {

        $pass = $this->getHttpRequest()->getPost("password");
        $user = $this->sess->FBUser;

        $customer = $this->facade->gEMCustomer()->findOneBy(["email" => $user[ "email" ]]);

        if (Passwords::verify($pass, $customer->password)) {
            $this->sess->FBUser[ "id" ] = $customer->id;
            $this->redirect('finishFBLogin!');
        } else {
            $this->flashMessage("Špatně zadané heslo", "warning");
        }

        $this->redirect(':SignCustomer:accountsMerge');
    }

    public function handleFinishFBLogin()
    {

        $user = $this->sess->FBUser;

        $fb = new Facebook([
            'app_id' => '165692553980302',
            'app_secret' => '3715c79d68307234b31a434e2181cab5',
            'default_graph_version' => 'v2.10',
            //'default_access_token' => '{access-token}', // optional
        ]);

        $response = $fb->get('/' . $user[ "userId" ] . '', $user[ "key" ]);
        $userData = $response->getGraphUser();

        $customer = $this->customerFac->createFromFb($user, $userData);

        unset($this->sess->FBUser);

        $this->redirect('loginCustomer!', ["fbId" => $customer->fbId]);

    }

    public function handleLoginCustomer($fbId)
    {

        try {
            $this->getUser()->login([$fbId, true, true], "");
            $this->getUser()->setExpiration('14 days', false);
            $this->basketFacade->addCustomerToOrder($this->getUser(), $this->sess);
            $this->basketFacade->changeCurrencyOnOrder($this->sess, $this->sess->actualCurrency);
            $this->flashMessage('Přihlášení bylo úspěšné!', 'success');
            if ($this->sess->backUrl != null) {
                $this->redirect($this->sess->backUrl);
            } else {
                $this->redirect(':RegistrationCustomer:profil', ['id' => $this->getUser()->id]);
            }
        } catch (AuthenticationException $e) {
            $this->flashMessage('Špatné přihlašovací údaje', 'danger');
        }

    }

}
