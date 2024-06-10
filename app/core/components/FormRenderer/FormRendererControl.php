<?php

namespace App\Core\Components\FormRenderer;

use Nette;
use Nette\Application\UI;
use Nette\Application\UI\Control;

class FormRendererControl extends UI\Control {

    public function render($item, $type = 'bootstrap', $dataSource = '', $dataSend = "", $dataSucc = "") {
        $template = $this->template;
        $template->dataSource = $dataSource;
        $template->dataSend = $dataSend;
        $template->dataSucc = $dataSucc;

        switch ($type) {
            case 'bootstrap':
                $template->setFile(__DIR__ . '/templates/bootstrap.latte');
                $template->renderForm = $item;
                break;
            case 'sidebar':
                $template->setFile(__DIR__ . '/templates/rulesidebar.latte');
                $template->renderForm = $item;
                break;
            default :

                if (!isset($item[$type])) {
                    throw new \Exception("Try render in form undeclared type or undefinded input. type/input: " . $type);
                }
                $template->setFile(__DIR__ . '/templates/bootstrapElement.latte');
                $template->item = $item[$type];
                break;
        }
        $template->render();
    }

    public function renderLow($id, $name, $type, $value = "", $class = "", $attrs = "") {
        $template = $this->template;
        $template->id = $id;
        $template->type = $type;
        $template->name = $name;
        $template->class = $class;
        $template->value = $value;
        $template->attrs = $attrs;
        if (!isset($type)) {
            throw new \Exception("Try render in form undeclared type or undefinded input. type/input: " . $type);
        }
        $template->setFile(__DIR__ . '/templates/bootstrapElementLow.latte');
        $template->render();
    }

    public function renderLowSelect($id, $name, $selected, $data, $class = "") {
        $template = $this->template;
        $template->data = $data;
        $tmp = str_replace('[', '', $name);
        $tmp = str_replace(']', '', $tmp);
        $template->id = $id . '_' . $tmp;
        $template->name = $name;
        $template->selected = $selected;
        $template->class = $class;

        $template->setFile(__DIR__ . '/templates/bootstrapLowSelect.latte');
        $template->render();
    }

    public function renderLowAutocomplete($id, $name, $value, $valueCmp = "", $dataSource = '', $attrs = NULL, $dataSend = "", $dataSucc = "") {
        $template = $this->template;
        $template->dataSource = $dataSource;
        $template->dataSend = $dataSend;
        $template->dataSucc = $dataSucc;
        $template->id = $id;
        $template->name = $name;
        $template->value = $value;
        $template->valueCmp = $valueCmp;
        $template->attrs = $attrs;
        $template->setFile(__DIR__ . '/templates/bootstrapLowAutocomplete.latte');
        $template->render();
    }

    public function handleDataSource($dataSource) {
        $presenter = $this->parent;
        if (strpos(get_class($presenter), 'Presenter') == false) {
            $presenter = $this->parent->parent;
        }
        $term = $presenter->request->getParameters()['term'];
        $dataSource = 'handle' . ucfirst($dataSource);
        return $presenter->$dataSource($term);
    }

}
