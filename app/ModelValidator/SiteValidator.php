<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/20/16
 * Time: 9:10 AM
 */

namespace App\ModelValidator;


use App\Libs\CdsApiDriver;
use App\Libs\ColdValidator;

class SiteValidator extends BaseValidator
{
    public function _name($name) {
        if(strlen($name) == 0) {
            $this->bindError(__FUNCTION__, 'Name can not be empty');
            return false;
        }

//        if(!(ColdValidator::instance()->isJapanese($name) || preg_match('/^[0-9a-zA-Z._# ]+$/', $name))) {
//            $this->bindError(__FUNCTION__, 'Name can not contain special chars');
//            return false;
//        }

        return true;
    }

    public function _api_url($url) {
        if(filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->bindError(__FUNCTION__, 'Invalid API URL');
            return false;
        }

        try {
            (new CdsApiDriver($url))->send('ping')->getResult();
        } catch(\Exception $e) {
            $this->bindError(__FUNCTION__, 'API does not response correctly');
            return false;
        }

        return true;
    }

    public function _site_url($url) {
        if(filter_var($url, FILTER_VALIDATE_URL) === false) {
            $this->bindError(__FUNCTION__, 'Invalid Site URL');
            return false;
        }

        return true;
    }
}