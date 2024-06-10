<?php

namespace App\Core\Components\UblabooTable\Model;

use Nette\Application\UI;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

class SimpleUblabooTranslator extends \Nette\Object {

    /**
     * Get translation for table
     * @return SimpleTranslator
     */
    public function get() {
        /**
         * Localization
         */
        return new SimpleTranslator([
            'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
            'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
            'ublaboo_datagrid.here' => 'zde',
            'ublaboo_datagrid.items' => 'Položky',
            'ublaboo_datagrid.all' => 'všechny',
            'ublaboo_datagrid.from' => 'z',
            'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
            'ublaboo_datagrid.group_actions' => 'Hromadné akce',
            'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
            'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
            'ublaboo_datagrid.action' => 'Akce',
            'ublaboo_datagrid.previous' => 'Předchozí',
            'ublaboo_datagrid.next' => 'Další',
            'ublaboo_datagrid.choose' => 'Vyberte',
            'ublaboo_datagrid.execute' => 'Provést',
            'ublaboo_datagrid.save' => 'Uložit',
            'ublaboo_datagrid.cancel' => 'Zrušit',
            'ublaboo_datagrid.show_default_columns' => 'Zobrazit původní nastavení',
            'ublaboo_datagrid.filter_submit_button' => 'Vyhledat'
        ]);
    }

}
