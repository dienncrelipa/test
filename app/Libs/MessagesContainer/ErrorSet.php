<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 2:07 PM
 */

namespace App\Libs\MessagesContainer;


class ErrorSet
{
    private $errors;
    private $indexIterator = 0;

    public function __construct() {
        $this->errors = [];
    }

    public function add(Error $error) {
        $this->errors[] = $error;
    }

    public function hasError() {
        return count($this->errors) > 0;
    }

    public function next() {
        return $this->errors[$this->indexIterator++];
    }

    public function hasNext() {
        return $this->indexIterator < count($this->errors);
    }
}