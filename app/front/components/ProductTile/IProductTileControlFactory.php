<?php

namespace Front\Components\ProductTile;

interface IProductTileControlFactory {

    /** @return ProductTileControl */
    function create();
}
