<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 * @param $val
 * @return int
 */

function intOrNull($val)
{
    return empty($val) ? null : (int)$val;
}

function floatOrNull($val)
{
    return empty($val) ? null : (float)$val;
}

function parseToGram($val, $unit = 'kg')
{
    return ($unit == 'kg') ? $val * 1000 : $val;
}

function parseBool($val)
{
    $boolStr = $val ?? false;
    $bool = $boolStr === true;
    return $bool;
}

function convertDotToArray($array) {
    $newArray = array();
    foreach($array as $key => $value) {
        $dots = explode(".", $key);
        if(count($dots) > 1) {
            $last = &$newArray[ $dots[0] ];
            foreach($dots as $k => $dot) {
                if($k == 0) continue;
                $last = &$last[$dot];
            }
            $last = $value;
        } else {
            $newArray[$key] = $value;
        }
    }
    return $newArray;
}
