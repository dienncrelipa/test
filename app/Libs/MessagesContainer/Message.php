<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 2:41 PM
 */

namespace App\Libs\MessagesContainer;


class Message
{
    protected $message, $type, $code;

    public function __construct($message, $type, $code = 0) {
        $this->message = $message;
        $this->type = $type;
        $this->code = $code;
    }

    public function getMessage() {
        return $this->message;
    }

    public function getType() {
        return $this->type;
    }

    public function getCode() {
        return $this->code;
    }
}