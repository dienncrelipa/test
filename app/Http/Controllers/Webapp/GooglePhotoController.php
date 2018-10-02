<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/22/16
 * Time: 11:43 AM
 */

namespace App\Http\Controllers\Webapp;


use App\Libs\ColdValidator;
use App\Libs\GooglePhoto;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

class GooglePhotoController extends NeedAuthController
{
    public $authenticatedClient = null;

    public function __construct(Route $route) {
        parent::__construct();

        $this->fileAccessToken = storage_path('gphoto_access_token');

        $this->client = new \Google_Client();
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://picasaweb.google.com/data/'
        ]);
        $this->client->setClientId('184494817289-6aj60a1nrgfh0390bue30u9nss015fdv.apps.googleusercontent.com');
        $this->client->setClientSecret('gz1VksFLQ5Pq4XXaO3c72GjA');
        $this->client->setRedirectUri(action('Webapp\\GooglePhotoController@getAuth'));

        $accessToken = $this->getAccessToken();

        if ($accessToken === null) {
            return;
        }

        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired() && isset($accessToken['refresh_token'])) {
            $newAccessToken = $this->client->refreshToken($accessToken['refresh_token']);
            foreach ($newAccessToken as $key => $value) {
                $accessToken[$key] = $value;
            }
            file_put_contents($this->fileAccessToken, json_encode($accessToken));
            $this->client->setAccessToken($accessToken);
        }

        $this->authenticatedClient = $this->client;
    }

    private function getAccessToken() {
        $accessToken = null;
        if (file_exists(storage_path('gphoto_access_token'))) {
            $accessToken = json_decode(file_get_contents($this->fileAccessToken), TRUE);
        }

        return ($accessToken) ? $accessToken : null;
    }


    public function getAuth(Request $request) {
        if($this->currentUser->is('admin') === false) {
            exit('404 Not Found');
        }

        $client = $this->client;
        $accessToken = null;
        try {
            $client->authenticate($request->get('code'));
            $accessToken = $client->getAccessToken();
            $refreshToken = $client->getRefreshToken();
            $accessToken['refresh_token'] = $refreshToken;
            file_put_contents($this->fileAccessToken, json_encode($accessToken));
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return redirect()->to(action('Webapp\\GooglePhotoController@getConfig'))->send();
    }

    public function getIndex() {
        if (!$this->authenticatedClient) {
            return '<h2>Google Photoを設定してください</h2>';
        }
        return view('webapp/gphoto/index');
    }

    public function getSinglePick() {
        if (!$this->authenticatedClient) {
            return '<h2>Google Photoを設定してください</h2>';
        }
        return view('webapp/gphoto/single');
    }

    public function getSearch(Request $request) {
        return $this->postSearch($request);
    }

    public function postSearch(Request $request) {
        $googlePhoto = new GooglePhoto($this->client);

        $page = ColdValidator::instance()->page();

        return response(json_encode($googlePhoto->searchByTag($request->get('keyword'), $page), JSON_PRETTY_PRINT), 200, [
            'Content-type' => 'application/json'
        ]);
    }

    public function getConfig() {
        if($this->currentUser->is('admin') === false) {
            exit('404 Not Found');
        }

        $id = '(none)';
        $email = '(none)';
        if($this->authenticatedClient) {
            try {
                $googlePhoto = new GooglePhoto($this->authenticatedClient);
                $id = $googlePhoto->userInfo->id;
                $email = $googlePhoto->userInfo->email;
            } catch(\Exception $e) {}
        }

        return view('webapp/gphoto/config', [
            'id' => $id,
            'email' => $email,
        ]);
    }

    public function getRenew() {
        return redirect()->to($this->client->createAuthUrl())->send();
    }
}