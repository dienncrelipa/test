<?php
/**
 * Created by PhpStorm.
 * User: nclin
 * Date: 9/1/2017
 * Time: 09:53 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;

class FailedPostToTargetSite implements BaseHandler
{

    public static function event()
    {
        return [
            'PostController@postEditAjax',
            'PostController@getPublish',
            'PostController@getDraft',
        ];
    }

    public function handle(Activity $activity)
    {
        if(isset($activity->meta['FailedPostToTargetSite'])) {
            return 'to target site failed '.$activity->meta['FailedPostToTargetSite'][0];
        }

        return false;
    }
}