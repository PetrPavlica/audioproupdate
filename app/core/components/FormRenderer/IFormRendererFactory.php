<?php

namespace App\Core\Components\FormRenderer;

interface IFormRendererFactory {

    /** @return FormRendererControl */
    function create();
}
