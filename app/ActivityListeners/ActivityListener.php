<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 8:40 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;
use App\Models\ActivityNotification;

class ActivityListener
{
    public static $handlerClasses = [
        CopyPostHandler::class,
        EditPostHandler::class,
        CreatePostHandler::class,
        ChangeTargetSiteHandler::class,
        DuplicateTitleHandler::class,
        FailedAutosaveHandler::class,
        FailedPostToTargetSite::class,
        PostLockHandler::class,
        DevToolEventHandler::class
    ];

    public static function handle(Activity $activity) {
        $messages = [];

        foreach(self::$handlerClasses as $class) {
            /** @var BaseHandler $class */
            $events = $class::event();
            if(!is_array($events)) {
                $events = [$events];
            }
            foreach($events as $event) {
                if($event != $activity->action) {
                    continue;
                }

                $message = (new $class())->handle($activity);
                if($message != false) {
                    $messages[] = $message;
                }
                break;
            }
        }

        if(!count($messages)) {
            return;
        }

        $notification = new ActivityNotification();
        $notification->activity_id = $activity->id;
        $notification->message = "Post #{$activity->post_id} has been ".implode(', ', $messages);
        $notification->save();
    }
}