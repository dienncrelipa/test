<?php
/**
 * Created by IntelliJ IDEA.
 * User: Duc Bob
 * Date: 10/16/2017
 * Time: 1:44 PM
 */

namespace App\ActivityListeners;


use App\Models\Activity;

class DevToolEventHandler implements BaseHandler
{
    public static function event()
    {
        return [
            'PostController@postLoggingDevTool',
        ];
    }
    
    public function handle(Activity $activity)
    {
        $devTool = 'off';
        $mess = ' changed debug status: ';
        if(isset($activity->meta['DevToolStatus'][0])) {
            $devTool = $activity->meta['DevToolStatus'][0];
            return $mess . $devTool;
        }
        
        return false;
    }
}