<?php

namespace Front\Components\StockExpedition;

interface IStockExpeditionFactory {

    /** @return StockExpeditionControl */
    function create();
}
