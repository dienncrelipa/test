<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/25/17
 * Time: 3:08 PM
 */

namespace App\ActivityListeners;


use App\Models\Activity;
use App\Models\Post;

class DuplicateTitleHandler implements BaseHandler
{
    public static function event()
    {
        return [
            'PostController@postEditAjax',
            'PostController@postCreateAjax',
            'PostController@getPublish',
        ];
    }

    public function handle(Activity $activity)
    {
        $postId = $activity->post_id;
        $post = Post::find($postId);

        if(empty($post)) {
            return false;
        }

        if(!Post::isUniquePostByTitle($post)) {
            return "has title that existed: {$post->title}. System failed to check, please contact DevOps";
        }
    }
}