<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/22/15
 * Time: 6:30 PM
 */

namespace App\APIs\DataInputter;


class Inputter
{
    private $data;

    public function __construct($data) {
        $this->setData($data);
    }

    public function setData($data = array()) {
        $this->data = is_array($data) ? $data : array();
    }

    public function input($key) {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function inputOrDefault($key, $default) {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
}