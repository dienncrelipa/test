<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 7/27/16
 * Time: 10:18 AM
 */

namespace App\Http\Controllers;


use Symfony\Component\HttpFoundation\Request;

class TwitterController extends Controller
{
    public function getIndex(Request $request) {
        $twitterUrl = $request->get('url', null);

        if(!$twitterUrl) {
            return '';
        }

        try {
            $data = file_get_contents('https://publish.twitter.com/oembed?url='.$twitterUrl);
            $json = \GuzzleHttp\json_decode($data);
            return view('webapp/components/twitter', [
                'html' => $json->html
            ]);
        } catch(\Exception $e) {
            return '';
        }
    }
}