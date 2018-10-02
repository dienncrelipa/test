<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 4:59 PM
 */

namespace App\Libs;

class ColdUtils {

    public static function randomString($length = 0) {
        $chars = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        $return = '';
        for ($i = 0; $i < $length; $i++) {
            $return .= $chars[rand(0, strlen($chars) - 1)];
        }

        return $return;
    }

    public static function explodeHashtag($text) {
        preg_match_all('/#([a-zA-z0-9]+)/', $text, $data);
        return $data[1];
    }

    public static function objColToArray($collection, $key) {
        $return = array();
        foreach ($collection as $obj) {
            $return[] = $obj->$key;
        }

        return $return;
    }

    public static function unpackXY($data) {
        return (Object) unpack('x/x/x/x/corder/Ltype/dlatitude/dlongitude', $data);
    }

    public static function copyProperty(&$obj1, $obj2, $properties = array('*')) {
        if (in_array('*', $properties)) {
            foreach (get_object_vars($obj2) as $key => $value) {
                $obj1->$key = $value;
            }
            return $obj1;
        }
        foreach ($properties as $property) {
            $obj1->$property = $obj2->$property;
        }
        return $obj1;
    }

}
