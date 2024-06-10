<?php

namespace Intra\Model\Utils\Text;

class Text {

    public static function getRandomString($length = 5, $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
        $str = "";

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        return $str;
    }

    public static function seoUrl($phrase, $maxLength = 100000000000000) {
        $table = array(
            "ě" => "e", "š" => "s", "č" => "c", "ř" => "r", "ž" => "z", "ý" => "y", "á" => "a", "í" => "i", "é" => "e", "ď" => "d",
            "ť" => "t", "ň" => "n", "ů" => "u", "ú" => "u", "Ě" => "E", "Š" => "S", "Č" => "C", "Ř" => "R", "Ž" => "Z", "Ý" => "Y",
            "Á" => "A", "Í" => "I", "É" => "E", "Ď" => "D", "Ť" => "T", "Ň" => "N", "Ů" => "U", "Ú" => "U"
        );

        $phrase = strtr($phrase, $table);
        $result = strtolower($phrase);

        $result = preg_replace("/[^A-Za-z0-9\s-._\/]/", "", $result);
        $result = trim(preg_replace("/[\s-]+/", " ", $result));
        $result = trim(substr($result, 0, (int) $maxLength));
        $result = preg_replace("/\s/", "-", $result);

        return $result;
    }

    public static function getStrictLength($string = '', $length = 2, $fill = '0', $direction = 0) {//0-doleva, 1- doprava
        if (Values::isEmpty($fill) || (Values::isEmpty($length) && $length < 1 && $length < strlen($string)))
            return $string;
        $direction = (intval($direction) && $direction > 0) ? 1 : 0;

        while (strlen($string) < $length) {
            if ($direction == 0)
                $string = $fill . $string;
            else
                $string .= $fill;
        }

        return $string;
    }

    public static function getPasswordEncode($passwd) {
        $passwd = $passwd . '_' . strlen($passwd);
        return str_rot13(sha1(md5(base64_encode($passwd))));
    }

    public static function limitWords($string, $limit = 20) {
        $temp = explode(' ', strip_tags($string), $limit);
        $temp[$limit] = '...';
        return implode(' ', $temp);
    }

    public static function limitChars($string = '', $length = 20, $dots = "...", $striptags = true) {
        if ($striptags || Values::isEmpty($string))
            $string = strip_tags($string);
        else
            $string = self::xhtml_cut_tidy(nl2br($string), $length) . $dots; //$string = strip_tags(nl2br($string),'<br><br/><br />');
        $length = intval($length);
        return (strlen($string) > $length) ? mb_substr($string, 0, $length - strlen($dots), 'UTF-8') . $dots : $string;
    }

    public static function limitFilename($string, $limit = 10) {
        //pripona - 4znaky, zacatek|konec - (limit-3)/4, prostredek - 2znaky
        $konce = ceil(($limit) / 4);
        $pocty = 2 * $konce + 6;
        if (strlen($string) > $pocty) {
            $output = substr($string, 0, $konce);
            $output .= '...' . substr($string, strrpos($string, '.') - $konce, $konce) . '.' . substr($string, strrpos($string, '.') + 1);
        } else
            $output = $string;
        return $output;
    }

    public static function datum($datum, $mezera = '', $format = 'd.m.Y') {
        if (!is_int($datum))
            $datum = strtotime($datum);
        if ($datum == 0)
            return $mezera;

        $datum = date($format, $datum);
        return preg_match('~^(([01]?[0-9]|2[0-3]):([0-5]?[0-9]):?([0-5]?[0-9])?[^0-9]?)?(0?[1-9]|19|[12][0-8]|29|31(?=\.([^2]|2\.(([02468][048]|[13579][26])00|[0-9]{2}(0[48]|[2468][048]|[13579][26]))))|30(?=\.[^2])|31(?=\.([13578][02]?\.)))\.(0?[1-9]|1[012])\.[0-9]{4}([^0-9]?([01]?[0-9]|2[0-3]):([0-5]?[0-9]):?([0-5]?[0-9])?)?$~D', $datum) ? $datum : $mezera;
    }

    public static function xhtml_cut_tidy($s, $limit) {
        $length = 0;
        for ($i = 0; $i < strlen($s) && $length < $limit; $i++) {
            switch ($s[$i]) {
                case '<':
                    $in_quote = '';
                    while ($i < strlen($s) && ($in_quote || $s[$i] != '>')) {
                        if (($s[$i] == '"' || $s[$i] == "'") && !$in_quote) {
                            $in_quote = $s[$i];
                        } elseif ($in_quote == $s[$i]) {
                            $in_quote = '';
                        }
                        $i++;
                    }
                    break;
                case '&':
                    $length++;
                    while ($i < strlen($s) && $s[$i] != ';') {
                        $i++;
                    }
                    break;
                default:
                    $length++;
            }
        }
        $config = array('output-xhtml' => true, 'show-body-only' => true);
        return tidy_repair_string(substr($s, 0, $i), $config, 'raw');
    }

    /**
      Uprava zakladni fce pro pouziti v URL, kde by mi vadilo "/", ktere nahradim "-"
     */
    public static function base64_encode($string = '', $strict = false) {
        $string = base64_encode($string);
        if (!$strict)
            $string = str_replace('/', '-', $string);
        return $string;
    }

    /**
      Uprava zakladni fce pro pouziti v URL, kde bz mi vadilo "/", ktere nahradim "-"
     */
    public static function base64_decode($string = '') {
        $string = str_replace('-', '/', $string);
        $string = base64_decode($string);
        return $string;
    }

    /**
      Lorem Ipsum generator
     */
    public static function loremIpsum($slov = 20) {
        $moznaSlova = 'Lorem ipsum dolor sit amet consectetuer adipiscing elit Donec id eros Quisque elit nisl lacinia cursus lacinia tincidunt porttitor non leo Nunc metus nibh semper ac interdum nec fringilla ut felis Donec tempor semper ligula Suspendisse ut ipsum quis est commodo dignissim Suspendisse mauris neque convallis ac tincidunt id interdum porttitor urna Sed orci turpis pretium id semper consequat venenatis et risus Cras facilisis augue in ipsum Praesent pellentesque diam sed est Vestibulum nec justo Curabitur lorem Sed sed dui Vivamus vitae dui at enim laoreet tincidunt Phasellus nunc metus cursus eget ornare et pulvinar ut enim Vivamus a turpis Aenean condimentum Nullam id nibh Mauris quis ante In fermentum Sed scelerisque velit ac justo Morbi quis urna Nam rhoncus orci quis laoreet dapibus libero orci suscipit dui ut hendrerit elit elit at tortor Integer congue sem quis orci Morbi pretium Integer eu felis Vestibulum mauris Pellentesque mi tellus condimentum et nonummy non vestibulum ac lacus Duis in wisi Aliquam justo Nam nulla Cum sociis natoque penatibus et magnis dis parturient montes nascetur ridiculus mus Sed nibh nibh sodales at tincidunt vitae congue id tortor Proin et leo Morbi odio nunc tempor vitae pretium auctor convallis quis nulla Sed sollicitudin consectetuer neque Vestibulum adipiscing Maecenas ac magna Aliquam quis velit Donec tristique urna vitae convallis malesuada velit libero fringilla nunc sed euismod libero sem eget elit Phasellus et neque Curabitur vel ante a elit cursus porta Curabitur interdum Lorem ipsum dolor sit amet consectetuer adipiscing elit Vestibulum consequat sapien id eros Phasellus metus elit cursus et tempus vitae mattis ut massa In vel purus at arcu congue eleifend Nullam sit amet dolor a risus mollis tempus Ut eget lacus Nulla eu lorem Ut aliquet lacinia tellus Nulla interdum Sed pharetra semper tellus Nam sapien Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas Vestibulum sagittis Nam id justo nec nibh gravida molestie Nulla facilisi In volutpat Quisque a augue vel mauris feugiat egestas Curabitur accumsan nibh vel justo Phasellus consequat wisi sed eros Vestibulum tellus pede pellentesque et auctor non dictum et dui Curabitur lobortis semper urna Aenean aliquam In hac habitasse platea dictumst Aenean rutrum pede vitae auctor consequat enim sem condimentum lacus vitae iaculis orci lorem et mauris Pellentesque tempus volutpat orci Curabitur at nunc Maecenas pellentesque nulla eget tellus Nam sagittis Curabitur wisi leo lobortis eu congue ut hendrerit vel enim Aenean pretium nisl pretium leo Vestibulum pellentesque augue et faucibus adipiscing tortor ligula faucibus urna id gravida pede justo eget pede Curabitur eu ligula nec magna convallis condimentum Fusce malesuada laoreet felis Nulla sed risus Vivamus laoreet accumsan odio Quisque ut nibh tincidunt ante tincidunt feugiat Nunc est massa ultrices id ullamcorper id laoreet at quam ';

        return $moznaSlova;
    }

    /**
      Formatovani adres
     */
    public static function formatAddress($town = '', $district = '', $street = '', $street_num_building = '', $street_num = '', $zip = '') {
        $adresa = array();
        $mesto = array();
        $c_ulice = array();
        $ulice_a_cp = array();

        //mesto
        if (!Values::isEmpty($town))
            $mesto[] = $town;
        if (!Values::isEmpty($district))
            $mesto[] = $district;
        if (!Values::isEmpty($mesto))
            $mesto = implode(' - ', array_unique($mesto));

        //ulice
        if (Values::isEmpty($street))
            $street = $town;

        //cislo ulice
        if (!Values::isEmpty($street_num_building))
            $c_ulice[] = $street_num_building;
        if (!Values::isEmpty($street_num))
            $c_ulice[] = $street_num;
        if (!Values::isEmpty($c_ulice))
            $c_ulice = implode(' / ', $c_ulice);

        //ulice a cp
        if (!Values::isEmpty($street))
            $ulice_a_cp[] = $street;
        if (!Values::isEmpty($c_ulice))
            $ulice_a_cp[] = $c_ulice;
        if (!Values::isEmpty($ulice_a_cp))
            $ulice_a_cp = implode(' ', array_unique($ulice_a_cp));

        if (!Values::isEmpty($ulice_a_cp))
            $adresa[] = $ulice_a_cp;
        if (!Values::isEmpty($zip))
            $adresa[] = $zip;
        if (!Values::isEmpty($town))
            $adresa[] = $mesto;

        return implode(', ', $adresa);
    }

    public static function orderBy($data, $field) {
        $code = "return strnatcmp(\$a['$field'], \$b['$field']);";
        usort($data, create_function('$a,$b', $code));
        return $data;
    }

}

?>
