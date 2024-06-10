<?php

namespace Intra\Model\Utils\EET;

use Tracy\Debugger;
use FilipSedivy\EET\Certificate;
use FilipSedivy\EET\Dispatcher;
use FilipSedivy\EET\Receipt;
use FilipSedivy\EET\Utils\UUID;
use FilipSedivy\EET\Exceptions\EetException;

// Pro fungování je třeba nasadit: composer require filipsedivy/php-eet
// a povolit v php.ini: extension=php_soap.dll

class EETAdapter
{
    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Certificate */
    protected $certificate;

    /** @var Boolean mode - production TRUE | playground FALSE */
    protected $mode;

    /**
     * Set mode production/playgound mode
     * @param bool $typeMode - define false => playgroud mode, true => production mode
     */
    public function setProductionMode($mode = false)
    {
        $this->mode = $mode;
    }

    /**
     * Send base EET paragon
     * @param array $array
     * parameters of array:
     * string 'id_provoz'  => Id provozovny
     * string 'id_pokl'    => Označení pokladního dokladu
     * string 'dic_popl'   => DIČ poplatníka
     * string 'porad_cis'  => Pořadové číslo
     * string 'dat_trzby'  => Datum tržby (pokud není, nastaví se na dnešní)
     * double 'zakl_nepodl_dph' => Celková částka vč. DPH v Kč
     * double 'zakl_dan1' => Celková částka vč. DPH v Kč
     * double 'dan1' => Celková částka vč. DPH v Kč
     * double 'zakl_dan2' => Celková částka vč. DPH v Kč
     * double 'dan2' => Celková částka vč. DPH v Kč
     * double 'zakl_dan3' => Celková částka vč. DPH v Kč
     * double 'dan3' => Celková částka vč. DPH v Kč
     */
    public function sendBase($array)
    {
        try {
            if ($this->mode) {
                $this->certificate = new Certificate(__DIR__ . '/3971983167.p12', 'X9Pwkr{9t9');
                $this->dispatcher = new Dispatcher($this->certificate);
                $this->dispatcher->setProductionService();
            } else {
                $this->certificate = new Certificate(__DIR__ . '/EET_CA1_Playground-CZ00000019.p12', 'eet');
                $this->dispatcher = new Dispatcher($this->certificate);
                $this->dispatcher->setPlaygroundService();
            }
        } catch (\Exception $ex) {
            Debugger::log($ex);
            return false;
        }
        $uuid = UUID::v4(); // Generování UUID
        $r = new Receipt;
        $r->uuid_zpravy = $uuid;
        $r->id_provoz = $array['id_provoz'];
        $r->id_pokl = $array['id_pokl'];
        $r->dic_popl = $array['dic_popl'];
        $r->porad_cis = $array['porad_cis'];

        $r->zakl_nepodl_dph = $array['zakl_nepodl_dph'];  // 0 %

        $r->zakl_dan1 = $array['zakl_dan1'];  // částka s 21 % DPH - částka bez DPH
        $r->dan1 = $array['dan1']; // výše 21 % DPH - částka čistě DPH

        $r->zakl_dan2 = $array['zakl_dan2']; // částka s 15 % DPH - částka bez DPH
        $r->dan2 = $array['dan2']; // výše 15 % DPH - částka čistě DPH

        $r->zakl_dan3 = $array['zakl_dan3']; // částka s 10 % DPH - částka bez DPH
        $r->dan3 = $array['dan3']; // výše 10 % DPH - částka čistě DPH

        $r->rezim = 0; // běžný režim

        if (isset($array['dat_trzby']))
            $r->dat_trzby = $array['dat_trzby'];
        else
            $r->dat_trzby = new \DateTime();
        $r->celk_trzba = $array['celk_trzba'];

        try {
            $this->dispatcher->send($r);
            // Tržba byla úspěšně odeslána
            if (isset($array['dat_trzby'])) {
                $array['dat_trzby'] = date_format($array['dat_trzby'], 'd. m. Y H:i:s');
            }
            Debugger::log('SendBase EET, FIK: ' . $this->dispatcher->getFik() . ', BKP: ' . $this->dispatcher->getBkp() . ' with data: ' . implode('|', $array), 'EET');
            return ['fik' => $this->dispatcher->getFik(), 'bkp' => $this->dispatcher->getBkp()];
        } catch (EetException $ex) {
            // Tržba nebyla odeslána
            Debugger::log('SendBase EET, TRŽBA NEBYLA ODESLÁNA!!!! with data: ' . implode('|', $array), 'EET-errors');
            Debugger::log($ex);
            return false;
        } catch (\Exception $ex) {
            Debugger::log($ex);
            return false;
        }
    }
}