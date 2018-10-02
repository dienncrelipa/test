<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/22/15
 * Time: 10:36 PM
 */

namespace App\APIs\SourceLeecher;


class SourceMap
{
    public static function resolve($host) {
        $hostsMap = array(
            'chiasenhac.com'    => 'Chiasenhac',
            'mp3.zing.vn'       => 'ZingMp3'
        );

        return $hostsMap[$host];
    }
}