<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 9/19/16
 * Time: 11:06 AM
 */

namespace App\Libs;


class MyPhotos
{
    public $apiUrl;

    public function __construct($apiUrl = '') {
        if(empty($apiUrl)) {
            throw new \Exception('No API Url found');
        }

        $this->apiUrl = $apiUrl;
    }

    public function api($path) {
        return $this->apiUrl.'/api/'.$path;
    }

    public function search($keyword = '', $page = 1) {
        if(empty($keyword)) {
            return null;
        }

        if(!is_numeric($page) || $page < 1) {
            $page = 1;
        }

        $page = intval($page);

        $result = json_decode(file_get_contents($this->api('image/search')."?keyword=$keyword&page=$page"));

        return $result;
    }
}