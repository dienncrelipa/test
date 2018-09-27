<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/19/15
 * Time: 5:21 PM
 */

namespace App\ModelValidator;

use App\Libs\ColdValidator;
use App\Models\User;

class UserValidator extends BaseValidator
{

    public $replaceFieldName = array(
        'short_name' => 'Login ID'
    );

    public $canNotUpdate = array(
        'username'
    );

    public function _username($username) {
        if(!preg_match("/^[a-zA-Z0-9-_]+$/", $username)) {
            $this->bindError(__FUNCTION__, 'ユーザ名は無効です');
            return false;
        }

        $obj = User::where('username', $username)->first();
        if(!empty($obj) && $obj->id != $this->modelObject->id) {
            $this->bindError(__FUNCTION__, 'このユーザ名は存在しています');
            return false;
        }
        return true;
    }

    public function _password($password) {
        if(strlen($password) < 6) {
            $this->bindError(__FUNCTION__, 'パスワードは短すぎます。６文字以上を入力してください');
            return false;
        }

        return true;
    }

    public function _role($role) {
        if(!User::isValidRole($role)) {
            $this->bindError(__FUNCTION__, '権限は無効です');
            return false;
        }

        return true;
    }

    public function _fullname($writername) {
      $obj = User::where('fullname', $writername)->first();
      if(!empty($obj) && $obj->id != $this->modelObject->id ) {
        $this->bindError(__FUNCTION__, 'このライター名は存在しています');
        return false;
      }
      return true;
    }
}