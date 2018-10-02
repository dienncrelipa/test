<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/23/15
 * Time: 1:28 AM
 */

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserSession extends BaseModel
{
    public $table  = 'sessions';
    public $fillable = ['user_id', 'session_key', 'ip_address', 'user_agent'];

    public function isValid() {
        return !empty($this->session_key);
    }

    public function activate() {
        $this->setUpdatedAt(Carbon::now()->toDateTimeString());
        $this->save();
    }

    public static function isValidSession($session_key = null) {
        $session = self::where('session_key', $session_key)->first();
        if(empty($session)) {
            return false;
        }

        $user = User::find($session->user_id);
        if(empty($user)) {
            return false;
        }

        return true;
    }

    public static function newSession($userId, $ipAddress = null, $userAgent = null) {
        $session = new self([
            'user_id' => $userId,
            'session_key' => str_random(64)
        ]);
        $session->save();

        return $session;
    }

    public static function getSession($sessionKey) {
        $session = self::where('session_key', $sessionKey)->first();
        if(empty($session)) {
            return new self();
        }

        return $session;
    }
}