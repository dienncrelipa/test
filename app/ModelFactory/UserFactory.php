<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/19/15
 * Time: 2:00 AM
 */

namespace App\ModelFactory;


use App\Models\User;

class UserFactory extends BaseFactory
{
    public function _password($password) {
        return md5($password);
    }
}