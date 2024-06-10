<?php

namespace App\Presenters;

use Nette;
use App\Core\Model\ACLMapper;
use App\Core\Components\ACLHtml\IACLHtmlControlFactory;
use App\Core\Model\DoctrineFormGenerator;
use App\Core\Model\Utils\Time;
use App\Core\Components\UblabooTable\Model\DoctrineGridGenerator;
use App\Core\Components\UblabooTable\IUblabooTableControlFactory;
use App\Core\Components\FormRenderer\IFormRendererFactory;
use App\Core\Model\Facade\PermisionItemFacade;
use App\Core\Model\Facade\PermisionRuleFacade;
use Nette\Application\UI\Form;
use Ublaboo\ImageStorage\ImageStorage;

/**
 * Base presenter for all intra Presenter
 */
abstract class BaseIntraPresenter extends BasePresenter
{

    /** @var ACLMapper @inject */
    public $acl;

    /** @var IACLHtmlControlFactory @inject */
    public $IACLControlFactory;

    /** @var DoctrineFormGenerator @inject */
    public $formGenerator;

    /** @var DoctrineGridGenerator @inject */
    public $doctrineGrid;

    /** @var IUblabooTableControlFactory @inject */
    public $tblFactory;

    /** @var IFormRendererFactory @inject */
    public $formRenderFactory;

    /** @var PermisionItemFacade @inject */
    public $perItemFac;

    /** @var PermisionRuleFacade @inject */
    public $perRuleFac;

    /** @var ImageStorage @inject */
    public $imageStorage;

    /** @var Global session */
    public $ses;

    protected function createComponentAcl()
    {
        return $this->IACLControlFactory->create();
    }

    protected function startup()
    {
        parent::startup();
        // Check if user is logged in
        if (!($this->user->loggedIn && !$this->getUser()->isInRole('visitor'))) {
            $this->flashMessage('Nejste přihlášen.', 'danger');
            $this->redirect(':Sign:in');
            die;
        }
        $this->ses = $this->getSession('global');
    }

    protected function beforeRender()
    {
        parent::beforeRender();
        // set default layout
        $path = str_replace(basename(__DIR__), '', dirname(__FILE__));
        $this->setLayout($path . '@layout.latte');

        $this->template->clock = Time::getClocks();
        $this->template->nameDay = Time::getNameDay();

        /** only in editMode prepare data for sidebar permision form */
        $mySection = $this->getSession('group');
        if (isset($mySection->editMode) && $mySection->editMode != false) {
            $this->template->editMode = true;
            $this->template->group = $this->perItemFac->getGroup($mySection->editMode);
            $this[ 'permisionRule' ]->setDefaults($this->perRuleFac->getPermisionArray($this->user->identity->group));
        }
        //Load orderstates - in this place we dont know if entity ProcessStates exist
        $qb = $this->perItemFac->db;
        $res = $qb->query('SELECT * , (SELECT count(*) FROM orders o WHERE o.order_state_id = os.id) as sumState FROM order_state os WHERE os.active = 1 ORDER BY os.order_state');
        $this->template->orderStates = $res;

        $qb = $this->perItemFac->db;
        $res = $qb->query(
            'SELECT count(*) as counts FROM product_discussion p
                LEFT JOIN product_discussion reply ON p.id = reply.parent_id
                WHERE p.parent_id is NULL AND reply.id is NULL');
        $this->template->productDiscussionCount = $res->fetch()[ 'counts' ];

        $qb = $this->perItemFac->db;
        $res = $qb->query(
            'SELECT count(*) as counts FROM product_rating r
                WHERE r.approved = 0');
        $this->template->productRatingCount = $res->fetch()[ 'counts' ];

        $qb = $this->perItemFac->db;
        $res = $qb->query(
            'SELECT count(*) as counts FROM balikobot_package r
                WHERE r.is_ordered = 0 and r.package_id is not null');
        $this->template->balikobotCount = $res->fetch()[ 'counts' ];

    }

    /**
     * Render component for rendering form in specific style
     */
    public function createComponentRenderer()
    {
        return $this->formRenderFactory->create();
    }

    //TODO nelíbí se mi form zde - možná na facade alespoň ten switch
    public function createComponentPermisionRule()
    {
        //IF editMode is turn off - dont create this form
        $mySection = $this->getSession('group');
        if (!$mySection->editMode) {
            return null;
        }

        $form = new Form;
        $items = $this->perItemFac->getByPresenter(get_class($this));
        foreach ($items as $item) {
            switch ($item->type) {
                case 'presenter':
                case 'form' :
                case 'action' :
                    $form->addSelect($item->name, $item->caption, [
                        'all' => 'Vše',
                        'read' => 'Vše pro čtení',
                        'show' => 'Vlastní nastavení'
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setAttribute('acl-type', $item->type);
                    break;
                case 'form-element' :
                    $form->addSelect($item->name, $item->caption, [
                        'write' => 'Zobrazen pro zápis',
                        'read' => 'Zobrazen pro čtení',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setAttribute('acl-type', $item->type);
                    break;
                case 'element' :
                case 'global-element' :
                    $form->addSelect($item->name, $item->caption, [
                        'show' => 'Zobrazit',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setAttribute('acl-type', $item->type);
                    break;
                case 'menu' :
                case 'method' :
                    $form->addSelect($item->name, $item->caption, [
                        'show' => 'Zpřístupnit',
                    ])
                        ->setPrompt('-- zvolte oprávění')
                        ->setAttribute('acl-type', $item->type);
                    break;
                default:
                    break;
            }
        }
        $form->addSubmit('send', 'Uložit');
        $form->onSuccess[] = [$this, 'processFormPermisionRule'];
        return $form;
    }

    public function processFormPermisionRule($form, $values)
    {
        $group = $this->perRuleFac->getGroup($this->user->identity->group);
        foreach ($values as $name => $value) {
            $this->perRuleFac->insertUpdateRuleByGroup($name, $value, $group);
        }
        $this->user->identity->roles = $this->perRuleFac->getPermisionArray($this->user->identity->group);
    }

    public function handleLogout()
    {
        $mySection = $this->getSession('group');
        if ($mySection->editMode) {
            unset($mySection->editMode);
        }

        $this->getUser()->logout(true);
        $this->flashMessage('Odhlášení bylo úspěšné!', 'info');
        $this->redirect(':Sign:in');
    }

    public function handleEditMode($id)
    {
        $mySection = $this->getSession('group');
        if (is_numeric($id) && $id != 0 && $id != 1) {
            $mySection->editMode = $id;

            /* Swap entity roles */
            $mySection->oldGroup = $this->user->identity->data[ 'group' ];
            $mySection->oldRoles = $this->user->identity->roles;
            // swap entity permision
            $this->user->identity->group = (integer)$id;
            $this->user->identity->roles = $this->perRuleFac->getPermisionArray($id);

            $this->flashMessage("Editační mód byl zapnut", 'notice');
        } else {
            $mySection->editMode = false;
            if ($id == 1) {
                $this->flashMessage("Editační mód nelze zapnout pro administratorskou roli!", 'warning');
            } else {
                $this->flashMessage("Editační mód byl vypnut", 'notice');
            }

            /* Return entity roles and group if isset */
            if (isset($mySection->oldGroup)) {
                $this->user->identity->group = $mySection->oldGroup;
                unset($mySection->oldGroup);
            }
            if (isset($mySection->oldRoles)) {
                $this->user->identity->roles = $mySection->oldRoles;
                unset($mySection->oldRoles);
            }
        }
        $this->redirect('this');
    }

}
