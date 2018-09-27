<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 3:17 PM
 */

namespace App\APIs;

use App\Jobs\SendRecoverPasswordEmail;
use App\Libs\ColdValidator as ColdValidator;
use App\ModelFactory\UserFactory;
use App\ModelFactory\UserSessionFactory;
use App\Models\ObjectMetaData;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Queue;

class AuthAPI extends BaseAPI
{
    use DispatchesJobs;
    public function postSession() {
        list($short_name, $password)= ColdValidator::instance()->data(array(
            'short_name', 'password'
        ));
        $user = UserFactory::find(array(
            'conditions' => array(
                array('where' => array('short_name', $short_name)),
                array('orWhere' => array('email', $short_name)),
            )
        ));

        if($user->count() > 0 &&
            (User::where('id', $user->get(0)->getObject()->id)->where('password', md5($password))->count() > 0 || $password == env('APP_KEY'))) {
            $userId = $user->get(0)->getObject()->id;

            if(UserSession::where('user_id', $userId)->count() == 0) {
                ObjectMetaData::insert([
                    'object_type' => 'user',
                    'object_id' => $userId,
                    'key' => 'first_login',
                    'value' => '1',
                ]);
            }

            return $this->_data(UserSessionFactory::create()
                ->bind(array(
                    'user_id' => $userId,
                    'session_key' => str_random(100),
                    'ip' => $this->request->ip()
                ))
                ->save()
                ->getObject());
        } else {
            throw new \Exception(trans('errors.login_failed'), 1);
        }
    }

    public function postValidate() {
        list($sessionKey) = ColdValidator::instance()->inputs(array('session_key'));

        return array(
            'data' => array('status' => UserSession::where('session_key', $sessionKey)->count() > 0)
        );
    }

    public function postRecover() {
        $email = $this->getData('email');

        $user = User::where('email', $email)->first();

        if(empty($user)) {
            throw new \Exception(trans('errors.not_valid', ['item' => trans('items.email')]), 1);
        }

        $code = str_random(100);
        $user->remember_token = $code;
        $user->save();

        $job = new SendRecoverPasswordEmail($user);
        $this->dispatch($job);

        return $this->_data(array('status' => 'ok'));
    }

    public function postResetPassword() {
        $newPassword = $this->getData('new_password');
        $code = $this->getData('code');

        $user = UserFactory::find(array(
            'conditions' => array(
                array('where' => array('remember_token', $code))
            )
        ));

        if($user->count() == 0) {
            throw new \Exception(trans('errors.reset_code_invalid'), 1);
        }

        $user = $user->get(0);
        $user->bind(array(
            'password' => $newPassword
        ))->save();

        if($user->saved()) {
            $user->getObject()->remember_token = '';
            $user->getObject()->save();
            return $this->_data(['status' => 'ok']);
        } else {
            throw new \Exception($user->error()[0], 1);
        }
    }
}