<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 11:08 AM
 */

namespace App\Http\Controllers\Webapp;

use App\Http\Classes\RouteMap;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class NeedAuthController extends Controller
{
    public function __construct() {
        if(!RouteMap::checkBrowserDetection()){
            redirect()->to(RouteMap::get(RouteMap::BROWSERDETECTION))->send();
        }
        $sessionKey = session('session_key');

        $session = UserSession::getSession($sessionKey);

        $loginUrl = RouteMap::get(RouteMap::LOGIN_FORM);
        $loginUrl .= '?next='.Request::capture()->url();

        if(!$session->isValid()) {
            redirect()->to($loginUrl)->send();
            exit;
        }

        $user = User::getUser($session->user_id);

        if(!$user->isValid()) {
            redirect()->to($loginUrl)->send();
            exit;
        }

        $this->currentSession = $session;
        $this->currentSession->ip_address = request()->ip();
        $this->currentSession->user_agent = request()->header('User-Agent');
        $this->currentSession->save();
        $this->currentUser = $user;

        $this->currentSession->activate();

        session()->set('current_user', $user);
    }
}