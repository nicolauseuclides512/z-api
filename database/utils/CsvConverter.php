<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace Database\Utils;


class CsvConverter
{
    public static function csvToArray($filename = '', $delimiter = ';')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return null;

        $header = null;
        $data = [];

        if (($handle = fopen($filename, 'r')) !== false) {

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public static function strToArray($csv_string, $delimiter = ",", $skip_empty_lines = true, $trim_fields = true)
    {

        $enc = preg_replace('/(?<!")""/', '!!Q!!', $csv_string);
        $enc = preg_replace_callback(
            '/"(.*?)"/s',
            function ($field) {
                return urlencode(utf8_encode($field[1]));
            },
            $enc
        );

        $lines = preg_split($skip_empty_lines ? ($trim_fields ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);

        $headers = str_getcsv(array_shift($lines));

        $data = [];

        foreach ($lines as $line) {
            $fields = $trim_fields ? array_map('trim', explode($delimiter, $line)) : explode($delimiter, $line);
            $row = [];

            foreach ($fields as $k => $v) {

//                $value = str_replace('!!Q!!', '"', utf8_decode(urldecode($v)));

//                $cleanValue = preg_replace_callback(
//                    '/[\p{So}\p{Cf}\p{Co}\p{Cs}\p{Cn}]/u',
//                    function ($string) {
//                        $result = [];
//
//                        foreach ((array)$string as $char) {
//                            $codePoint = unpack('N', iconv('UTF-8', 'UCS-4BE', $char));
//
//                            if (is_array($codePoint) && array_key_exists(1, $codePoint)) {
//                                $result[] = sprintf('U+%04X', $codePoint[1]);
//                            }
//                        }
//
//                        return implode('', $result);
//                    }, $value);


                $row[str_replace(
                    " ",
                    "_",
                    strtolower($headers[$k]))] = str_replace('!!Q!!', '"', utf8_decode(urldecode($v)));
            }

            array_push($data, $row);
        }

        return $data;
    }

    public static function detectDelimiter($csvSource)
    {
        $firstLine = null;

        $delimiters = [
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        ];

        if (is_string($csvSource)) {
            $enc = preg_replace('/(?<!")""/', '!!Q!!', $csvSource);
            $enc = preg_replace_callback(
                '/"(.*?)"/s',
                function ($field) {
                    return urlencode(utf8_encode($field[1]));
                },
                $enc
            );

            $lines = preg_split(true ? (true ? '/( *\R)+/s' : '/\R+/s') : '/\R/s', $enc);

            $firstLine = array_shift($lines);

        } else {
            $handle = fopen($csvSource, "r");
            $firstLine = fgets($handle);
            fclose($handle);
        }

        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        $delimiter = array_search(max($delimiters), $delimiters);

        return (string)$delimiter;
    }

}