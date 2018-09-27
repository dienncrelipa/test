<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/21/16
 * Time: 10:37 AM
 */

namespace App\Http\Controllers\Webapp;


use App\Http\Controllers\Controller;
use App\Libs\CdsApiDriver;
use App\Libs\GooglePhoto;
use App\Models\Post;
use Illuminate\Http\Request;

class TestController extends Controller
{

    public function getIndex(Request $request)
    {
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Referer' => 'https://iframely.com/',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
                'Origin' => 'https://iframely.com/',
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest'
            ],
            'http_errors' => false
        ]);

        $content =  $client->post('https://iframely.com/rest/link', [
            'json' => [
                'uri' => $request->get('url')
            ]
        ])->getBody()->getContents();

        return $content;
    }
}