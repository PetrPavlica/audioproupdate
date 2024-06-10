<?php

namespace Intra\Model\Utils\Registers;

use Intra\Model\Utils\Text\Text;

class Register extends \Nette\Object {
    /*     * *************************************************************************
     *  RZP - Registr živnostenského podnikání
     *  OR - Obchodní rejstřík
     * ************************************************************************ */

    protected static $priority = array(
        'rzp',
        'or'
    );

    /*     * *************************************************************************
     *  ZAKLAD
     * ************************************************************************ */

    public static function getInfo($findme = '', $prefer = false) {
        return self::getBySubject($findme, $prefer);
    }

    /*     * *************************************************************************
     *  NACTENI
     * ************************************************************************ */
    /*     * *****************************************************************************
     *  Načtení dle seznamu dle jmena
     * **************************************************************************** */

    public static function getBySubject($hledany_subjekt = '', $rejstrik = 'rzp') {
        if (self::isEmpty($hledany_subjekt))
            return 'Nebyl zadán hledaný subjekt';
        $hledany_subjekt = rawurlencode(str_replace('-', ' ', Text::seoURL($hledany_subjekt)));

        return self::getSourceList($hledany_subjekt, $rejstrik);
    }

    public static function getSourceList($hledany_subjekt = '', $rejstrik = 'rzp') {
        require_once('htmlParser2.php');

        if (preg_match('~^[0-9]+$~', $hledany_subjekt) && intval($hledany_subjekt) > 0)
            $html = self::openCurl('http://www.rzp.cz/cgi-bin/aps_cacheWEB.sh?VSS_SERV=ZVWSBJFND&Action=Search&PRESVYBER=0&PODLE=subjekt&ICO=' . ($hledany_subjekt) . '&OBCHJM=&ROLES=&OKRES=&OBEC=&CASTOBCE=&ULICE=&COR=&COZ=&CDOM=&JMENO=&PRIJMENI=&NAROZENI=&ROLE=&VYPIS=1'); // podle ica
        else
            $html = self::openCurl('http://www.rzp.cz/cgi-bin/aps_cacheWEB.sh?VSS_SERV=ZVWSBJFND&Action=Search&PRESVYBER=0&PODLE=subjekt&ICO=&OBCHJM=' . ($hledany_subjekt) . '&ROLES=&OKRES=&OBEC=&CASTOBCE=&ULICE=&COR=&COZ=&CDOM=&JMENO=&PRIJMENI=&NAROZENI=&ROLE=&VYPIS=1'); // podle ica

            /* Create a DOM object */
        $html_base = new \simple_html_dom();
        // Load HTML from a string
        $html_base->load($html);

        return self::parseFromHTML($html_base, $rejstrik);
    }

    private static function openCurl($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $str = curl_exec($curl);
        curl_close($curl);

        return $str;
    }

    public static function parseFromHTML($html, $rejstrik) {
        require_once('htmlParser2.php');
        $output = [];
        foreach ($html->find('div.subjekt') as $key_elem => $elem) {
            $ico = trim($elem->find('dl dd.aktual', -1)->plaintext);
            $nazev = trim(preg_replace('~[0-9]+\. ~', '', $elem->find('h3', -1)->plaintext));
            $forma = !self::isEmpty(trim($elem->find('dl dd.aktual', 0)->plaintext)) ? trim($elem->find('dl dd.aktual', 0)->plaintext) : '';
            $adresa = trim($elem->find('dl dd.aktual', 1)->plaintext);
            $role = !self::isEmpty(trim($elem->find('dl dd.aktual', 2)->plaintext)) ? trim($elem->find('dl dd.aktual', 2)->plaintext) : '';

            $output[] = array('nazev' => $nazev, 'ico' => $ico, 'forma' => $forma, 'adresa' => $adresa, 'role' => $role);
            //$output[] = self::getByIco($ico,$rejstrik);
        }

        if (!self::isEmpty()) {
            $output = Text::orderBy($output, 'nazev');
        } else
            return $output;

        return !self::isEmpty($output) ? $output : array(array('nazev' => strtoupper_mb($rejstrik, 'utf-8'), 'ico' => 'Zadaný výraz nebyl nalezen'));
    }

    /*     * *****************************************************************************
     *  KONEC - dle seznamu dle jmena
     * **************************************************************************** */
    /*     * *****************************************************************************
     *  FORMAT VYSTUPU
     * **************************************************************************** */

    public static function formatOutput($input, $rejstrik = 'rzp') {
        switch (mb_strtolower(trim($rejstrik), 'UTF-8')) {
            case 'rzp':
                $ico = $input['Vypis_RZP']['Zakladni_udaje']['ICO'];
                $town = $input['Vypis_RZP']['Adresy']['Adresa']['Nazev_obce'];
                $district = $input['Vypis_RZP']['Adresy']['Adresa']['Nazev_casti_obce'];
                $street = $input['Vypis_RZP']['Adresy']['Adresa']['Nazev_ulice'];
                $street_num_building = $input['Vypis_RZP']['Adresy']['Adresa']['Cislo_domovni'];
                $street_num = $input['Vypis_RZP']['Adresy']['Adresa']['Cislo_orientacni'];
                $zip = $input['Vypis_RZP']['Adresy']['Adresa']['PSC'];
                $firma = $input['Vypis_RZP']['Zakladni_udaje']['Obchodni_firma'];
                $zaznam = 'ŽÚ - ' . $input['Vypis_RZP']['Zakladni_udaje']['Zivnostensky_urad']['Nazev_ZU'];
                $forma = $input['Vypis_RZP']['Zakladni_udaje']['Pravni_forma']['Nazev_PF'];
                $role = $input['Vypis_RZP']['Zakladni_udaje']['Pravni_forma']['Text'];

                $street_num_komplet = array();
                if (!self::isEmpty($street_num))
                    $street_num_komplet[] = $street_num;
                if (!self::isEmpty($street_num_building))
                    $street_num_komplet[] = $street_num_building;
                $street_num_komplet = implode('/', $street_num_komplet);

                $statutarni_organ = array();
                if (!self::isEmpty($input['Vypis_RZP']['Osoby']['Osoba'])) {
                    if (!self::isEmpty($input['Vypis_RZP']['Osoby']['Osoba']['0'])) {
                        foreach ($input['Vypis_RZP']['Osoby']['Osoba'] as $so) {
                            $statutarni_organ[] = $so['Prijmeni'] . ' ' . $so['Jmeno'];
                        }
                    } else {
                        $statutarni_organ[] = $input['Vypis_RZP']['Osoby']['Osoba']['Prijmeni'] . ' ' . $input['Vypis_RZP']['Osoby']['Osoba']['Jmeno'];
                    }
                }
                $statutarni_organy = $statutarni_organ;
                $statutarni_organ = implode(', ', $statutarni_organ);
                break;

            case 'or':
                $ico = $input['Vypis_OR']['Zakladni_udaje']['ICO'];
                $town = !self::isEmpty($input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Nazev_obce']) ? $input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Nazev_obce'] : $input['Vypis_OR']['Zakladni_udaje']['Sidlo']['Nazev_obce'];
                $district = '';
                $street = !self::isEmpty($input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Nazev_ulice']) ? $input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Nazev_ulice'] : $input['Vypis_OR']['Zakladni_udaje']['Sidlo']['Nazev_ulice'];
                $street_num_building = !self::isEmpty($input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Cislo_domovni']) ? $input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Cislo_domovni'] : $input['Vypis_OR']['Zakladni_udaje']['Sidlo']['Cislo_domovni'];
                $street_num = !self::isEmpty($input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Cislo_do_adresy']) ? $input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['Cislo_do_adresy'] : $input['Vypis_OR']['Zakladni_udaje']['Sidlo']['Cislo_do_adresy'];
                $zip = !self::isEmpty($input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['PSC']) ? $input['Vypis_OR']['Zakladni_udaje']['Sidlo'][0]['PSC'] : $input['Vypis_OR']['Zakladni_udaje']['Sidlo']['PSC'];
                $firma = $input['Vypis_OR']['Zakladni_udaje']['Obchodni_firma'];
                $zaznam = $input['Vypis_OR']['Registrace']['Spisova_znacka']['Soud']['Text'] . ' - ' . preg_replace('~\s+~', ' ', $input['Vypis_OR']['Registrace']['Spisova_znacka']['Oddil_vlozka']);
                $forma = $input['Vypis_OR']['Zakladni_udaje']['Pravni_forma_OR']['Nazev_PF'];
                $role = $input['Vypis_OR']['Zakladni_udaje']['Pravni_forma_OR']['Nazev_PF'];

                $street_num_komplet = array();
                if (!self::isEmpty($street_num))
                    $street_num_komplet[] = $street_num;
                if (!self::isEmpty($street_num_building))
                    $street_num_komplet[] = $street_num_building;
                $street_num_komplet = implode('/', $street_num_komplet);

                $statutarni_organ = array();
                if (!self::isEmpty($input['Vypis_OR']['Statutarni_organ']['Clen_SO'])) {
                    if (!self::isEmpty($input['Vypis_OR']['Statutarni_organ']['Clen_SO']['0'])) {
                        foreach ($input['Vypis_OR']['Statutarni_organ']['Clen_SO'] as $so) {
                            $statutarni_organ[] = $so['Clen']['Fyzicka_osoba']['Prijmeni'] . ' ' . $so['Clen']['Fyzicka_osoba']['Jmeno'];
                        }
                    } else {
                        $statutarni_organ[] = $input['Vypis_OR']['Statutarni_organ']['Clen_SO']['Clen']['Fyzicka_osoba']['Prijmeni'] . ' ' . $input['Vypis_OR']['Statutarni_organ']['Clen_SO']['Clen']['Fyzicka_osoba']['Jmeno'];
                    }
                }
                $statutarni_organy = $statutarni_organ;
                $statutarni_organ = implode(', ', $statutarni_organ);
                break;
        }
        //echo Maintenance::dump($input);
        //echo $rejstrik;
        //formatAddress($town,$district,$street,$street_num_building,$street_num,$zip);
        $output = array(
            'ico' => $ico,
            'dic' => self::getDic($ico),
            'nazev' => $firma,
            'adresa' => Text::formatAddress($town, $district, $street, $street_num_building, $street_num, $zip),
            'statutarni_organy' => $statutarni_organy,
            'statutarni_organ' => $statutarni_organ,
            'town' => $town,
            'district' => $district,
            'street' => (self::isEmpty($street) ? $town : $street),
            'street_num' => $street_num_komplet,
            'zip' => $zip,
            'forma' => $forma,
            'zaznam' => $zaznam,
            'role' => $role);
        return $output;
    }

    /*     * *****************************************************************************
     *  Načtení dle iča 8číslic
     * **************************************************************************** */

    public static function getByIco($ico = '', $forma = '', $rejstrik = false) {
        if (self::isEmpty($ico))
            return 'Nebylo zadáno IČO';

        $ico = Text::getStrictLength($ico, 8);

        if (!$rejstrik) {
            if (preg_match('~[F|f]+yzická~', $forma))
                $rejstrik = 'rzp';
            else
                $rejstrik = 'or';
        }

        $url = self::getSourceURL($ico, $rejstrik);
        $vypis_z_rejstriku = self::loadXMLFromSourceURL($url);
        $output = self::formatOutput($vypis_z_rejstriku, $rejstrik);

        return !self::isEmpty($output['nazev']) ? $output : array(array('nazev' => strtoupper_mb($rejstrik, 'utf-8'), 'ico' => 'Zadané IČO nebylo nalezeno'));
    }

    public static function getSourceURL($ico, $rejstrik = 'rzp') {
        switch (mb_strtolower(trim($rejstrik), 'UTF-8')) {
            case 'rzp':
                $url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_rzp.cgi?ico=' . $ico . '&xml=0&rozsah=2&ver=1.0.4';
                break;

            case 'or':
                $url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_or.cgi?ico=' . $ico . '&xml=0&rozsah=1&ver=1.0.2';
                break;

            default:
                $url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_or.cgi?ico=' . $ico . '&xml=0&rozsah=1&ver=1.0.2';
                break;
        }
        return $url;
    }

    /*     * *****************************************************************************
     *  KONEC - Načtení dle iča
     * **************************************************************************** */
    /*     * *****************************************************************************
     *  Načtení XML z URL
     * **************************************************************************** */

    public static function loadXMLFromSourceURL($url = '') {
        if (self::isEmpty($url))
            return '';

        $xml = simplexml_load_file($url, 'SimpleXMLElement');
        $namespaces = $xml->getNameSpaces(true);
        $feed = $xml->children($namespaces['are']);
        $feed = $feed->children($namespaces['dtt']);

        $json = json_encode($feed);
        $xml_array = json_decode($json, TRUE);

        return $xml_array;
    }

    /*     * *****************************************************************************
     *  KONEC - Načtení XML z URL
     * **************************************************************************** */
    /*     * *****************************************************************************
     *  Ostatní - Obsluha
     * ***************************************************************************** */

    public static function getDic($ico) {
        require_once('application/classes/htmlParser2.php');

        $xml = simplexml_load_file('http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=' . $ico, 'SimpleXMLElement');
        $namespaces = $xml->getNameSpaces(true);
        $feed = $xml->children($namespaces['are']);
        $feed = $feed->children($namespaces['D']);

        $json = json_encode($feed);
        $output = json_decode($json, TRUE);

        return $output['VBAS']['DIC'];
    }

    public static function getErrorDetail($html) {
        return !self::isEmpty($html['Error']['Error_text']) ? $html['Error']['Error_text'] : 'Jiná chyba';
    }

    public static function isEmpty($value = '', $moreThan = 0, $trimmovat = '') {
        //neni vubec nastaveno?
        if (!isset($value))
            return true;

        //prazdne pole ?
        if (is_array($value)) {
            if ($moreThan === 0)
                return empty($value) ? true : false;
            //nebo mena prvku?
            else
                return count($value) > $moreThan ? false : true;
        }

        //prazdny + nechtene znaky? zbytecne pouzivat empty kdyz toto je 2in1
        if (strlen(trim($trimmovat)) > 0)
            $value = trim($value, $trimmovat);
        else
            $value = trim($value);

        return (strlen($value) > $moreThan) ? false : true;
    }

}

?>