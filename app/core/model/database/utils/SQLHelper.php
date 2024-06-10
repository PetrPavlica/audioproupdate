<?php

namespace App\Core\Model\Database\Utils;

class SQLHelper {

    /**
     * Helper method for convert searching term to SQL query for Like form
     * @param string $term
     * @param string $prefix
     * @param array $columns
     * @return string
     */
    public static function termToLike($term, $alias, $columns) {
        $term = explode(" ", $term);
        $term2 = "";
        foreach ($term as $item) {
            $item = trim($item);
            if ($item != "") {
                $term2 .= " (";
                foreach ($columns as $col) {
                    $term2 .= " " . $alias . "." . $col . " LIKE '%" . $item . "%' OR";
                }
                $term2 = substr($term2, 0, -2);
                $term2 .= ") AND";
            }
        }
        $term2 = substr($term2, 0, -3);
        return $term2;
    }

    public static function termToLikeAnd($term, $alias, $columns)
    {
        $term = explode(" ", $term);
        foreach($term as $k => $i) {
            if (empty(trim($i))) {
                unset($term[$k]);
            }
        }
        $term = array_values($term);
        $term2 = '';
        $termCount = count($term);
        if ($termCount) {
            $term2 = "((";
        }
        foreach ($term as $k => $item) {
            $item = trim($item);
            if ($item != "") {
                foreach ($columns as $ka => $col) {
                    $term2 .= $alias.'.'.$col." like '%" . $item . "%'";
                    if (($ka < count($columns) - 1)) {
                        $term2 .= ' OR ';
                    }
                }
                if ($k < $termCount - 1) {
                    $term2 .= ') AND (';
                } else {
                    $term2 .= ')';
                }
            } else {
                $termCount--;
            }
        }
        if ($termCount) {
            $term2 .= ')';
        }
        return $term2;
    }

}
