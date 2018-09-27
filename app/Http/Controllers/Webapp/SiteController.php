<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 11:40 AM
 */

namespace App\Http\Controllers\Webapp;


use App\Http\Classes\RouteMap;
use App\Libs\CdsApiDriver;
use App\Libs\MessagesContainer\Error;
use App\Libs\MessagesContainer\ErrorSet;
use App\Libs\MessagesContainer\Message;
use App\Libs\MessagesContainer\MessageSet;
use App\ModelFactory\SiteFactory;
use App\Models\Category;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class SiteController extends NeedAuthController
{
    public function __construct() {
        parent::__construct();
        if($this->currentUser->is('admin') === false) {
            exit('404 Not Found');
        }
    }

    public function getIndex() {
        $sites = Site::all();

        return view('webapp/site/index', [
            'sites' => $sites
        ]);
    }

    public function getCreate() {
        return view('webapp/site/create');
    }

    public function postCreate(Request $request) {
        $siteName = $request->get('site_name');
        $siteApi = $request->get('site_api');
        $siteUrl = $request->get('site_url');

        $categorySlugs = $request->get('category_slug');

        $siteFactory = SiteFactory::create()->bind([
            'name' => $siteName,
            'api_url' => $siteApi,
            'site_url' => $siteUrl
        ])->save();

        if(!$siteFactory->saved()) {
            return view('webapp/site/create', [
                'errorsSet' => $siteFactory->error(),
                'previousData' => [
                    'siteName' => $siteName,
                    'siteApi'  => $siteApi,
                    'siteUrl'  => $siteUrl
                ]
            ]);
        }

        return redirect()->to(RouteMap::get(RouteMap::SITE_LIST))->send();
    }

    public function getEdit($id = 0) {
        if(empty($site = Site::find($id))) {
            return '404 Not found';
        }

        return view('webapp/site/edit', [
            'site' => $site,
            'categories' => Category::where('site_id', $site->id)->get()
        ]);
    }

    public function postEdit(Request $request, $id = 0) {
        if(empty($site = Site::find($id))) {
            return '404 Not found';
        }

        $siteFactory = SiteFactory::find(['conditions' => [
            ['where' => ['id', $id]]
        ]])->get(0);

        $siteFactory->bind([
            'name' => $request->get('site_name'),
            'site_url'  => $request->get('site_url')
        ])->save();

        if(!$siteFactory->saved()) {
            return view('webapp/site/edit', [
                'errorsSet' => $siteFactory->error(),
                'site' => $siteFactory->getObject(),
                'categories' => Category::where('site_id', $id)->get()
            ]);
        }

        $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('OK', 'success')));
        return redirect()->to(action('Webapp\\SiteController@getIndex'))->send();
    }

    public function getStatus(Request $request, $action, $id = 0) {
        if(!$this->currentUser->is(User::ADMIN)) {
            return 'You don\'t have permission';
        }

        if(empty($site = Site::find($id))) {
            return 'Not found';
        }

        $nextStatus = 1;

        if($action == 'enable') {
            $nextStatus = 1;
        }

        if($action == 'disable') {
            $nextStatus = 0;
        }

        $site->status = $nextStatus;
        $site->save();

        $this->_flashMessage($request, 'OK', 'success');

        return redirect()->to(action('Webapp\\SiteController@getIndex'))->send();
    }
}