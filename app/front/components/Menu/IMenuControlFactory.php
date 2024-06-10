<?php

namespace Front\Components\Menu;

interface IMenuControlFactory {

    /** @return MenuControl */
    function create();
}
