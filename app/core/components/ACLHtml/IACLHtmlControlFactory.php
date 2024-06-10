<?php

namespace App\Core\Components\ACLHtml;

interface IACLHtmlControlFactory {

    /** @return ACLHtmlControl */
    function create();
}
