<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 8:52 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;

interface BaseHandler
{
    public static function event();

    public function handle(Activity $activity);
}