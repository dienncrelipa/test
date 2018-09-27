<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/7/16
 * Time: 11:18 AM
 */

namespace App\Models;


use Illuminate\Session\Store;

class PostHistory extends BaseModel
{
    public $table = 'post_history';

    const STATUS_SAVED = 'saved';
    const STATUS_DRAFT = 'draft';
    const AUTOSAVE_INTERVAL = 4;

    public static function newHistory(Post $post, $status) {
        $history = new self();
        if((new \ReflectionClass(__CLASS__))->getConstant('STATUS_'.strtoupper($status)) === false) {
            return null;
        }

        $history->post_id = $post->id;
        $history->content = $post->content;
        $history->checksum = $post->getChecksum();
        $history->status = $status;
        $history->save();

        return $history;
    }

    public static function getChecksum($post, $status) {
        $history = self::where('post_id', $post->id)->where('status', $status)->first();
        return (!empty($history)) ? $history->checksum : null;
    }

    public static function getFirstFrom($post, $status, $time) {
        return self::where('post_id', $post->id)
            ->where('status', $status)
            ->where('checksum', '<>', $post->getChecksum())
            ->where('created_at', '>', $time)->orderBy('id', 'DESC')->first();
    }

    public static function deleteAllHistory(Post $post, $status) {
        return self::where('post_id', $post->id)->where('status', $status)->delete();
    }

    public static function saveFromSession(Store $session, Post $post) {
        if(($autoSaver = $session->get('autoSave'.$post->id, null)) === null) {
            return false;
        }

        if($autoSaver['count'] % self::AUTOSAVE_INTERVAL == 0) {
            return true;
        }

        $postTemp = clone $post;
        $postTemp->content = $autoSaver['currentContent'];

        // mark it saved (make count var divisible by AUTOSAVE_INTERVAL)
        $autoSaver['count'] += $autoSaver['count'] % self::AUTOSAVE_INTERVAL;

        $history = self::newHistory($postTemp, self::STATUS_DRAFT);

        return $history;
    }
}