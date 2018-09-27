<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 9:45 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;

class EditPostHandler implements BaseHandler
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
        $previousPublishedStatus = isset($activity->meta['PreviousPublishedStatus'][0]) ? $activity->meta['PreviousPublishedStatus'][0] : null;
        $newPublishedStatus = isset($activity->meta['NewPublishedStatus'][0]) ? $activity->meta['NewPublishedStatus'][0] : null;

        if($previousPublishedStatus == null || $newPublishedStatus == null) {
            return false;
        }

        if($previousPublishedStatus == $newPublishedStatus) {
            return false;
        }

        return "changed status from {$this->stringStatus($previousPublishedStatus)} to {$this->stringStatus($newPublishedStatus)} ";
    }

    public function stringStatus($statusInt) {
        return $statusInt == 1 ? 'published' : 'draft';
    }
}