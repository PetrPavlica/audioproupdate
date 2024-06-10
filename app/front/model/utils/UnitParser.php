<?php

namespace Front\Model\Utils\Text;

class UnitParser {

    public static function parse($text, $count) {
        if (strpos($text, '#') === FALSE) {
            if (in_array($text, ['pár', 'set'])) {
                if ($count >= 2 && $count <= 4) {
                    $text .= 'y';
                } elseif ($count > 4) {
                    $text .= 'ů';
                }
            }

            return $text;
        }
        $text = str_replace('#', '', $text);
        $arr = explode('|', $text);
        $other = $text;
        foreach ($arr as $a) {
            if (strpos($a, ')') === FALSE) {
                $other = self::clean($a);
            } else {
                $aa = explode(')', $a);
                $text = self::clean($aa[1]);
                $tmp = explode(',', self::cleanBracket($aa[0]));
                if (intval($tmp[0]) <= $count && $count <= intval($tmp[1])) {
                    return $text;
                }
            }
        }
        return $other;
    }

    public static function clean($text) {
        $text = str_replace('"', '', $text);
        $text = trim($text);
        return $text;
    }

    public static function cleanBracket($text) {
        $text = self::clean($text);
        $text = str_replace(')', '', $text);
        $text = str_replace('(', '', $text);
        return $text;
    }

}

?>
