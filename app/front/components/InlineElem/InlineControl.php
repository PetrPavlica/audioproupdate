<?php

namespace Front\Components\InlineElem;

use Nette\Application\UI\Control;

class InlineControl extends Control {

    public function render($htmlTag, $name, $allowEdit, $resource, $id, $class = "", $removeTags = "") {
        $template = $this->template;
        $template->setFile(__DIR__ . '/templates/inline.latte');

        $res = isset($resource[$name . '_' . $id]) ? $resource[$name . '_' . $id]->text : "text";
        if ($removeTags != "") {
            $res = str_replace('<' . $removeTags . '>', "", $res);
            $res = str_replace('</' . $removeTags . '>', "", $res);
        }

        // vložíme do šablony nějaké parametry
        $template->htmlTag = $htmlTag;
        $template->allowEdit = $allowEdit;
        $template->resource = $res;
        $template->id = $id;
        $template->name = $name;
        $template->class = $class;

        // a vykreslíme ji
        $template->render();
    }

}
