<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 2:02 PM
 */

namespace App\Http\Controllers;


use App\APIs\ApiControllerFactory;
use Illuminate\Http\Request;

class ApiController extends Controller
{

    public function index(Request $request, $api = null, $action = null, $parametersRaw = "") {
        $api = new ApiControllerFactory($api, $request);
        $api->setParentAction('ApiController@index');
        $parameters = explode('/', $parametersRaw);
        if($api->constructError != false) {
            return $api->constructError;
        }
        return $api->doAction($action, $parameters);
    }
}