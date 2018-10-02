<?php

namespace App\Http\Controllers\Webapp;

use App\Http\Classes\RouteMap;
use App\Http\Controllers\Controller;
use App\Libs\BrowserDetection;
use App\Libs\MessagesContainer\Error;
use App\Libs\MessagesContainer\ErrorSet;
use App\Libs\MessagesContainer\Message;
use App\Libs\MessagesContainer\MessageSet;
use App\ModelFactory\UserFactory;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function getIndex(Request $request) {
        if(!RouteMap::checkBrowserDetection()){
            redirect()->to(RouteMap::get(RouteMap::BROWSERDETECTION))->send();
        }
        $sessionKey = $request->session()->get('session_key');
        $session = UserSession::getSession($sessionKey);
        if($session->isValid()) {
            $user_is_del = User::onlyTrashed()->where('id', $session->user_id)->first();
            if(!$user_is_del){
                redirect()->to(RouteMap::get(RouteMap::USER_DASHBOARD))->send();
            }else{
                $this->_flashMessage($request, "アカウント：[$user_is_del->username] がブロックられる。管理者に連絡して下さい。", 'danger');
                $request->session()->set('session_key', '');
            }
        }

        return view('webapp/auth/login');
    }

    public function postIndex(Request $request) {
        $user = User::validateLogin($request->get('username'), $request->get('password'));

        $errors = new ErrorSet();
        if(!$user->isValid()) {
            $errors->add(new Error('Username or Password is invalid'));
        }
        if($user->isValid() && $user->isDeactive()) {
            $errors->add(new Error('こちらのアカウントは凍結されています。管理者にご連絡ください'));
        }

        if($errors->hasError()) {
            return view('webapp/auth/login', [
                'errorsSet' => $errors
            ]);
        }


        $ipAddress = $request->ip();
        $userAgent = $request->header('User-Agent');
        $session = UserSession::newSession($user->id, $ipAddress, $userAgent);

        $request->session()->set('session_key', $session->session_key);

        $redirectUrl = RouteMap::get(RouteMap::USER_DASHBOARD);

        if(($nextUrl = $request->get('next', null)) !== null) {
            $redirectUrl = $nextUrl;
        }

        return redirect()->to($redirectUrl)->send();
    }

    public function getLogout(Request $request) {
        $request->session()->set('session_key', '');

        return redirect()->to(RouteMap::get(RouteMap::LOGIN_FORM))->send();
    }

    public function getSignup(Request $request) {
        return view('webapp/auth/signup', [
            'currentRequest' => $request
        ]);
    }

    public function postSignup(Request $request) {
        $username = $request->get('username');
        $password = $request->get('password');
        $fullname = $request->get('fullname');
        $crowdworks_id = $request->get('crowdworks_id');

        $userFactory = UserFactory::create()->bind([
            'username' => $username,
            'password' => $password,
            'fullname' => $fullname,
            'crowdworks_id' => $crowdworks_id,
            'role'     => 'editor',
        ])->save();

        if(!$userFactory->saved()) {
            return view('webapp/auth/signup', [
                'errorsSet' => $userFactory->error(),
                'currentRequest' => $request
            ]);
        }
        $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('Signup successfully. Please login', 'success')));
        return redirect()->to(RouteMap::get(RouteMap::POST_LIST))->send();
    }

    public function getBrowserDetection(){
        if(RouteMap::checkBrowserDetection()){
            redirect()->to(RouteMap::get(RouteMap::LOGIN_FORM))->send();
        }
        $browser = new BrowserDetection();
        return view('webapp/auth/browserDetection', [
            'browser_name' => $browser->getName()
        ]);
    }
}