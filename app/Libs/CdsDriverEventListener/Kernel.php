<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 10/19/17
 * Time: 10:30 AM
 */

namespace App\Libs\CdsDriverEventListener;


class Kernel
{
    public $eventHandlers = [
        SendPostHandler::class
    ];
}