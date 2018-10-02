<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 1/23/17
 * Time: 4:01 PM
 */

namespace App\Models;


class Backup extends BaseModel
{
    public $table = 'thirdparty_backup';

    public static function newBackup($postId, $sessionKey, $metadata) {
        $backup = new self();
        $backup->post_id = $postId;
        $backup->session_key = $sessionKey;
        $backup->metadata = $metadata;

        $backup->save();

        return $backup;
    }
}