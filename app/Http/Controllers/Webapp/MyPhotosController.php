<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 9/19/16
 * Time: 11:37 AM
 */

namespace App\Http\Controllers\Webapp;


use App\Libs\ColdValidator;
use App\Libs\MyPhotos;
use Illuminate\Http\Request;

class MyPhotosController extends NeedAuthController
{
    public function getSearch(Request $request) {
        return $this->postSearch($request);
    }

    public function getSearchByType(Request $request)
    {
        $api_url = env('MYPHOTOS_API_URL', '').'/api/image/searchByTypes?'.$request->getQueryString();

        return response()->json(json_decode(file_get_contents($api_url)));
    }

    public function postSearch(Request $request) {
        $myPhoto = new MyPhotos(env('MYPHOTOS_API_URL', ''));

        $page = ColdValidator::instance()->page();

        return response(json_encode($myPhoto->search($request->get('keyword'), $page), JSON_PRETTY_PRINT), 200, [
            'Content-type' => 'application/json'
        ]);
    }
}