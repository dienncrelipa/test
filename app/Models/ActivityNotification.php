<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 8:41 AM
 */

namespace App\Models;


use Illuminate\Support\Facades\DB;

class ActivityNotification extends BaseModel
{
    public $table = 'activity_notification';

    public static function getLogFilterUser($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array();
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['username', 'like', "$id_data%"]);
        }
        return DB::table('activity_notification as ac_no')
            ->select(['u.id', 'u.username as value'])
            ->leftJoin('activity_log as ac_log', 'ac_no.activity_id','=', 'ac_log.id')
            ->leftJoin('sessions as ss', 'ac_log.session_id', '=','ss.id')
            ->leftJoin('users as u', 'ss.user_id', '=','u.id')
            ->where($_where)
            ->groupBy('u.id')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }

    public static function getLogFilterPostId($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array();
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['post_id', 'like', "$id_data%"]);
        }
        return DB::table('activity_notification as ac_no')
            ->select(['ac_log.id', 'ac_log.post_id as value'])
            ->leftJoin('activity_log as ac_log', 'ac_no.activity_id','=', 'ac_log.id')
            ->where($_where)
            ->groupBy('ac_log.post_id')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }

    public static function getMetaData() {
        return ActivityNotification::get()->toArray();
    }
}