<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 11:27 AM
 */

namespace App\Http\Classes;


use App\Libs\BrowserDetection;

class RouteMap
{
    public $map;
    private static $instance;

    const LOGIN_FORM = 'loginForm';
    const LOG_OUT = 'logOut';
    const SITE_LIST = 'siteList';
    const SITE_CREATE = 'siteCreate';
    const SITE_EDIT = 'siteEdit';
    const USER_DASHBOARD = 'userDashboard';
    const USER_LIST = 'userList';
    const USER_CREATE = 'userCreate';
    const USER_CREATE_MULTI = 'userCreateMulti';
    const POST_LIST = 'postList';
    const POST_CREATE = 'postCreate';
    const PRODUCT_LIST = 'productList';
    const CATEGORY_LIST = 'categoryList';
    const CATEGORY_CREATE = 'categoryCreate';
    const GOOGLE_PHOTO = 'googlePhoto';
    const BROWSERDETECTION = 'browserDetection';
    const SITE_ADMIN = 'Admin';
    const ADMIN_TITLE = 'getDataCheckTitle';
    const ADMIN_TARGET = 'getDataCheckLinkTarget';
    const ADMIN_POSTS = 'getDataPostStatus';

    public function __construct() {
        $this->map = [
            self::LOGIN_FORM => 'Webapp\\AuthController@getIndex',
            self::LOG_OUT => 'Webapp\\AuthController@getLogout',
            self::USER_DASHBOARD => 'Webapp\\PostController@getIndex',
            self::USER_LIST => 'Webapp\\UserController@getIndex',
            self::USER_CREATE => 'Webapp\\UserController@getCreate',
            self::USER_CREATE_MULTI => 'Webapp\\UserController@getMassCreate',
            self::SITE_LIST => 'Webapp\\SiteController@getIndex',
            self::SITE_CREATE => 'Webapp\\SiteController@getCreate',
            self::SITE_EDIT => 'Webapp\\SiteController@getEdit',
            self::POST_LIST => 'Webapp\\PostController@getIndex',
            self::POST_CREATE => 'Webapp\\PostController@getCreate',
            self::PRODUCT_LIST => 'Webapp\\ProductController@getIndex',
            self::CATEGORY_CREATE => 'Webapp\\CategoryController@getCreate',
            self::GOOGLE_PHOTO => 'Webapp\\GooglePhotoController@getConfig',
            self::BROWSERDETECTION => 'Webapp\\AuthController@getBrowserDetection',
            self::SITE_ADMIN => 'Webapp\\AdminController@getIndex',
            self::ADMIN_TITLE => 'Webapp\\AdminController@getDataCheckTitle',
            self::ADMIN_TARGET =>'Webapp\\AdminController@getDataCheckLinkTarget',
            self::ADMIN_POSTS => 'Webapp\\AdminController@getDataPostStatus',

        ];
    }

    public static function get($actionName) {
        $object = self::getInstance();
        return isset($object->map[$actionName]) ? action($object->map[$actionName]) : '';
    }

    public static function getInstance() {
        if(empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function checkBrowserDetection(){
        $browser = new BrowserDetection();
        if($browser->getName() !== BrowserDetection::BROWSER_CHROME
            || $browser->isMobile()){
            return false;
        }
        return true;
    }
}