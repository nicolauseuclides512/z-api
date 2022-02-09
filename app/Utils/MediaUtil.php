<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Utils;


use App\Exceptions\AppException;

class MediaUtil
{
    /**
     * @param $src
     * @return string
     * @throws \Exception
     */
    public static function getType($src)
    {
        try {
            $youtubeRx = '~
                ^(?:https?://)?              # Optional protocol
                 (?:www\.)?                  # Optional subdomain
                 (?:youtube\.com|youtu\.be)  # Mandatory domain name
                 /watch\?v=([^&]+)           # URI with video id as capture group 1
                 ~x';

            if (preg_match($youtubeRx, $src, $matches)) {
                return 'youtube';
            } else if (preg_match('#^data:image/\w+;base64,#i', $src, $matches) && base64_encode(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $src), true)) === preg_replace('#^data:image/\w+;base64,#i', '', $src)) {
                return 'file';
            } else if (filter_var($src, FILTER_VALIDATE_URL)) {
                return 'url';
            } else {
                throw AppException::bad('invalid data type');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}