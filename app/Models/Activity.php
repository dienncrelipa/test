<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/12/17
 * Time: 2:58 PM
 */

namespace App\Models;


use App\ActivityListeners\ActivityListener;

class Activity extends BaseModel
{
    public $table = 'activity_log';
    public $meta = [];

    public function setSession($session) {
        $this->session_id = $session->id;
    }

    public function setAction($action) {
        $this->action = $action;
    }

    public function setPostId($postId) {
        $this->post_id = $postId;
    }

    public function setInfo($info) {

    }

    public function addMeta($key, $value) {
        if(isset($this->meta[$key])) {
            $this->meta[$key] = [];
        }
        $this->meta[$key][] = $value;
    }

    public function __destruct() {
        if(empty($this->id)) {
            return;
        }

        foreach($this->meta as $key => $values) {
            foreach($values as $value) {
                $activityMeta = new ActivityMeta();
                $activityMeta->activity_id = $this->id;
                $activityMeta->key = $key;
                $activityMeta->value = $value;
                $activityMeta->save();
            }
        }

        ActivityListener::handle($this);
    }
}