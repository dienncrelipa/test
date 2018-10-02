<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 2:09 PM
 */

namespace App\Libs\MessagesContainer;


class Error extends Message
{
    public function __construct($message = '', $code = 0) {
        parent::__construct($message, 'error', $code);
    }
}