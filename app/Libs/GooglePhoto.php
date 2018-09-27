<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/22/16
 * Time: 10:47 AM
 */

namespace App\Libs;


class GooglePhoto
{
    public $googleClient;
    public $httpClient;
    public $userInfo;
    public $maxResults = 100;

    public function __construct(\Google_Client $google_Client) {
        $this->googleClient = $google_Client;
        $this->httpClient = new \GuzzleHttp\Client([
            'headers' => ['Authorization' => 'Bearer '.$this->googleClient->getAccessToken()['access_token']],
            'http_errors' => false,
        ]);

        $this->userInfo = json_decode(
            $this->httpClient->get('https://www.googleapis.com/oauth2/v1/userinfo?alt=json')->getBody()->getContents()
        );

        if(isset($this->userInfo->error)) {
            throw new \Exception(json_encode($this->userInfo->error));
        }
    }

    public function searchByTag($tag, $page = 1) {
        $searchQuery = "&tag={$tag}";
        $maxResults = $this->maxResults;
        $startIndex = ($page-1)*$this->maxResults + 1;

        if(empty($tag)) {
            if($page > 1) {
                return [];
            }
            $maxResults = 999999999;
            $searchQuery = "";
        }

        $photoData = json_decode($this->httpClient->get(
            "https://picasaweb.google.com/data/feed/api/user/{$this->userInfo->id}?kind=photo&{$searchQuery}alt=json&max-results={$maxResults}&start-index={$startIndex}"
        )->getBody()->getContents());

        return $photoData;
    }
}