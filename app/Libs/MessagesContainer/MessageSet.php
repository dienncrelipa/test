<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/21/16
 * Time: 9:40 AM
 */

namespace App\Libs\MessagesContainer;


class MessageSet
{
    private $messages;
    private $indexIterator = 0;
    private $type = 'default';

    public function __construct() {
        $this->messages = [];
    }

    public function add(Message $message) {
        $this->messages[] = $message;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function hasMesage() {
        return count($this->messages) > 0;
    }

    public function next() {
        return $this->messages[$this->indexIterator++];
    }

    public function hasNext() {
        return $this->indexIterator < count($this->messages);
    }
}