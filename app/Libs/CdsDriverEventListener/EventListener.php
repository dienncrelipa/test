<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 10/19/17
 * Time: 10:35 AM
 */

namespace App\Libs\CdsDriverEventListener;


use Illuminate\Support\Facades\Log;

class EventListener
{
    public static function handle($data) {
        $handlers = (new Kernel())->eventHandlers;
        foreach($handlers as $handlerClass) {
            $handlerObject = new $handlerClass();
            $data = $handlerObject->handle($data);
        }

        return $data;
    }
}