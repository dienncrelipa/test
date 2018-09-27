<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 1:53 PM
 */

namespace App\ActivityListeners;

use App\Models\Activity;
use App\Models\ActivityNotification;

class CreatePostHandler implements BaseHandler
{

    public static function event()
    {
        return ['PostController@postCreateAjax'];
    }

    public function handle(Activity $activity)
    {
        $newPublishedStatus = $activity->meta['NewPublishedStatus'][0];

        if($newPublishedStatus != 1) {
            return false;
        }

        return "created and published";
    }
}