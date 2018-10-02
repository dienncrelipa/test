<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 10/19/17
 * Time: 10:33 AM
 */

namespace App\Libs\CdsDriverEventListener;


interface BaseHandler
{
    public function handle($data);
}