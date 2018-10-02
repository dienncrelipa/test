<?php
/**
 * Created by IntelliJ IDEA.
 * User: Duc Bob
 * Date: 10/16/2017
 * Time: 1:44 PM
 */

namespace App\ActivityListeners;


use App\Models\Activity;
use App\Models\ActivityMeta;
use App\Models\Post;

class PostLockHandler implements BaseHandler
{
    public static function event()
    {
        return [
            'PostController@postLock',
        ];
    }
    
    public function handle(Activity $activity)
    {
        $mess = ' LOCKED!';
        if(isset($activity->meta['LockError'][0])) {
            $mess .= ' ERROR - ' . $activity->meta['LockError'][0];
            return $mess;
        }
        
        return false;
    }
}