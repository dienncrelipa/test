<?php
/**
 * Created by PhpStorm.
 * User: nclin
 * Date: 8/30/2017
 * Time: 05:49 PM
 */

namespace App\ActivityListeners;


use App\Models\Activity;

class FailedAutosaveHandler implements BaseHandler
{

    public static function event()
    {
        return [
            'PostController@postAutosave',
            'PostController@postErrorLog',
        ];
    }

    public function handle(Activity $activity)
    {
        if(isset($activity->meta['AutoSaveFalse'])) {
            return 'autosaved failed '.$activity->meta['AutoSaveFalse'][0];
        }

        return false;
    }
}