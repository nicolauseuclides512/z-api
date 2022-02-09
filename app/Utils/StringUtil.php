<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Utils;


//setlocale(LC_ALL, 'en_US.UTF8');

class StringUtil
{
    public static function slugify($str, $replace = array(), $delimiter = '-', $maxLength = 200)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $str);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $delimiter);

        // remove duplicate -
        $text = preg_replace('~-+~', $delimiter, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;

    }

    public static function cleanPhone($string, $minLength = 9, $maxLength = 15)
    {
        if(is_null($string))
            return null;

        if (strlen($string) < $minLength || strlen($string) > $maxLength)
            return null;

        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        return preg_replace('/\D+/', '', $string);
    }


}