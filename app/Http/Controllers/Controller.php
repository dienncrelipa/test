<?php

namespace App\Http\Controllers;

use App\Libs\MessagesContainer\Message;
use App\Libs\MessagesContainer\MessageSet;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    public function _flashMessage(Request $request, $message, $type) {
        $request->session()->flash('flashMessageSet', (new MessageSet())->setType($type)->add(new Message($message, $type)));
    }

    public function _clearFlashMessage(Request $request) {
        $request->session()->pull('flashMessageSet', null);
    }
}
