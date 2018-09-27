<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 5:16 PM
 */

namespace App\APIs;


use App\Libs\ColdValidator;
use App\ModelFactory\UserSessionFactory;
use App\Models\LoginSession;
use App\Models\Device;
use App\Models\User;

class NeedAuthAPI extends BaseAPI
{
    const INVALID_SESS_KEY = 101;
    private static $errorMsg = array(
        self::INVALID_SESS_KEY => 'Invalid session key'
    );

    public $isPublic = true;
    public $dontNeedAuth = false;

    protected $currentSession;

    public function __construct() {
        if($this->dontNeedAuth == true) {
            return;
        }
        list($sessionKey) = ColdValidator::instance()->inputs(array(
            'session_key'
        ));

        $session = UserSessionFactory::find(array(
            'conditions' => array(
                array('where' => array('session_key', $sessionKey))
            )
        ));

        if($session->count() == 0) {
            throw new \Exception(self::$errorMsg[self::INVALID_SESS_KEY], self::INVALID_SESS_KEY);
        }

        $this->currentSession = $session->get(0)->getObject();
        $this->currentUser = User::find($this->currentSession->user_id);
    }

    public function permission($rolePermitted = array()) {
        if(array_search($this->currentUser->role, $rolePermitted) === false) {
            throw new \Exception('You do not have permission', 1);
        }
    }

}