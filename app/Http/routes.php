<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    redirect()->to(action('Webapp\\AuthController@getIndex'))->send();
});

Route::get('webapp/admin','Webapp\\AdminController@getIndex');

Route::get('webapp/admin/checktitle','Webapp\\AdminController@getDataCheckTitle');
Route::get('webapp/admin/checktitle/posts-error','Webapp\\AdminController@getPostsError');
Route::get('webapp/admin/checklink','Webapp\\AdminController@getDataCheckLinkTarget');
Route::get('webapp/admin/statusPosts','Webapp\\AdminController@getDataPostStatus');
Route::post('webapp/admin/postUpCsv','Webapp\\AdminController@postUpCsv');
Route::get('webapp/admin/PostStatusChecker','Webapp\\AdminController@getPostStatusChecker');

Route::get('exportCsvLinkTarget/', 'Webapp\\AdminController@exportCsvLinkTarget');
Route::get('exportCsvTitleDuplicated/', 'Webapp\\AdminController@exportCsvTitleDuplicated');


Route::controllers([
    'ajax'        => 'Webapp\\AjaxController',
    'webapp/auth' => 'Webapp\\AuthController',
    'webapp/site' => 'Webapp\\SiteController',
    'webapp/post' => 'Webapp\\PostController',
    'webapp/category' => 'Webapp\\CategoryController',
    'webapp/user' => 'Webapp\\UserController',
    'test'        => 'Webapp\\TestController',
    'twitter'     => 'TwitterController',
    'webapp/product' => 'Webapp\\ProductController',
    'webapp/gphoto' => 'Webapp\\GooglePhotoController',
    'webapp/myphotos' => 'Webapp\\MyPhotosController',
    'webapp/log'  => 'Webapp\\LogViewerController'
]);
