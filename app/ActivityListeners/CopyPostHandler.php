<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 8:53 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;
use App\Models\ActivityNotification;

class CopyPostHandler implements BaseHandler
{
    public static function event()
    {
        return ['PostController@getCopyPost'];
    }

    public function handle(Activity $activity) {
        $newPostId = $activity->meta['NewPostId'][0];

        return "copied to new post #{$newPostId}";
    }
}