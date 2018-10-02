<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/10/16
 * Time: 8:57 AM
 */

namespace App\Http\Controllers\Webapp;

use App\Http\Classes\RouteMap;
use App\Libs\MessagesContainer\Error;
use App\Libs\MessagesContainer\ErrorSet;
use App\Libs\MessagesContainer\Message;
use App\Libs\MessagesContainer\MessageSet;
use App\ModelFactory\UserFactory;
use App\Models\User;
use Illuminate\Http\Request;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\RenderFunc;
use Nayjest\Grids\FilterConfig;
use Nayjest\Grids\GridConfig;
use Nayjest\Grids\DbalDataProvider;
use Nayjest\Grids\FieldConfig;
use Nayjest\Grids\Grid;
use Nayjest\Grids\ObjectDataRow;
use Nayjest\Grids\Components\THead;
use Nayjest\Grids\Components\TFoot;
use Nayjest\Grids\Components\RecordsPerPage;
use Nayjest\Grids\Components\ShowingRecords;
use Nayjest\Grids\Components\OneCellRow;
use Nayjest\Grids\Components\ColumnHeadersRow;
use Nayjest\Grids\Components\Base\RenderableRegistry;
use Nayjest\Grids\Components\HtmlTag;
use Nayjest\Grids\Components\Laravel5\Pager;
use Nayjest\Grids\EloquentDataProvider;

use HTML;
use Nayjest\Grids\SelectFilterConfig;

class UserController extends NeedAuthController
{
    public function __construct() {
        parent::__construct();
        if($this->currentUser->is('admin') === false) {
            exit('404 Not Found');
        }
    }

    public function getIndex(Request $request) {

        $cfg = (new GridConfig())
          ->setName('filter-user')
          ->setDataProvider(
            new EloquentDataProvider(
              (new User)->newQuery()
            )
          )
          ->setColumns([
            (new FieldConfig())
              ->setName('id')
              ->setLabel('ID')
              ->setSortable(true)
              ->addFilter(
                (new SelectFilterConfig())
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setDefaultValue(null)
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setFilteringFunc(function($val, EloquentDataProvider $provider) {
                      $provider->getBuilder()->where('id', '=', $val);
                  })
                )
              ->setSorting(Grid::SORT_ASC),
            (new FieldConfig())
              ->setName('username')
              ->setLabel('ユーザID')
              ->setSortable(true)
              ->addFilter(
                (new FilterConfig)
                    ->setOperator(FilterConfig::OPERATOR_EQ)
                    ->setDefaultValue(null)
                    ->setTemplate('webapp.components.nayjest_columns_filter')
                    ->setFilteringFunc(function($val, EloquentDataProvider $provider) {
                        $provider->getBuilder()->where('id', '=' , $val);
                    })
              )
              ->setSorting(Grid::SORT_ASC),
            (new FieldConfig())
              ->setName('fullname')
              ->setLabel('ライター名')
              ->setSortable(true)
                ->addFilter(
                    (new SelectFilterConfig())
                        ->setDefaultValue(null)
                        ->setOperator(FilterConfig::OPERATOR_EQ)
                        ->setTemplate('webapp.components.nayjest_columns_filter')
                        ->setFilteringFunc(function($val, EloquentDataProvider $dap) {
                            $dap->getBuilder()->where('id', '=', $val);
                        })
                )
              ->setSorting(Grid::SORT_ASC),
            (new FieldConfig())
              ->setName('crowdworks_id')
              ->setLabel('クラウドワークスID')
              ->setSortable(true)
              ->addFilter(
                (new FilterConfig)
                    ->setOperator(FilterConfig::OPERATOR_EQ)
                    ->setDefaultValue(null)
                    ->setTemplate('webapp.components.nayjest_columns_filter')
                    ->setFilteringFunc(function($val, EloquentDataProvider $provider) {
                        $sampleUser = User::find($val);
                        $crowd = $sampleUser->crowdworks_id;
                        $provider->getBuilder()->where('crowdworks_id', $crowd);
                    })
              )
              ->setSorting(Grid::SORT_ASC)
              ->setCallback(function($val, ObjectDataRow $row) {
                  $crowdworks_id = $row->getCellValue('crowdworks_id');
                  return '<a target="_blank" href="https://crowdworks.jp/public/employees/' . $crowdworks_id . '">' . $crowdworks_id . '</a>';
              }),
            (new FieldConfig())
              ->setName('role')
              ->setLabel('権限')
              ->setSortable(true)
              ->addFilter(
                (new FilterConfig)
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setDefaultValue(null)
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setFilteringFunc(function($val, EloquentDataProvider $provider) {
                      $sampleUser = User::find($val);
                      $role = $sampleUser->role;
                      return $provider->getBuilder()->where("role", $role);
                  })
              )
              ->setSorting(Grid::SORT_ASC),
            (new FieldConfig())
              ->setName('active_status')
              ->setLabel('アカウントステータス')
              ->setCallback(function($val, ObjectDataRow $row) {
                  $user = $row->getSrc();
                  $user->isDeactive = $user->isDeactive();
                  return ($user->isDeactive) ? '<span class="red">凍結</span>' : '<b class="text-success">アクティブ</b>';
              }),
            (new FieldConfig())
              ->setName('action')
              ->setLabel('編集')
              ->setSortable(false)
              ->setCallback(function($val, ObjectDataRow $row) {
                  $user_id = $row->getCellValue('id');
                  $deleteBtn = ($user_id != $this->currentUser->id ? '<a href="#" data-href="' . action('Webapp\UserController@getDelete', $user_id) . '" class="btn-delete-user" data-title="' . $row->getCellValue('username') . ' のユーザーを削除"><span class="label label-danger fa fa-trash"><span class="action-top tooltip">削除</span></span></a>' : '');
                  $activateBtn = $row->getSrc()->isDeactive ? '&nbsp;<a href="' . action('Webapp\UserController@getActivate', $user_id) . '"><span class="label label-success fa fa-power-off"><span class="action-top tooltip">アクティブ</span></span></a>' : '';
                  
                  return '<div class="btn-toolbar" role="toolbar" aria-label="Action button group toolbar"><div class="btn-group" role="group" aria-label="Action group button"><a href="' . action('Webapp\UserController@getEdit', $user_id) . '"><span class="label label-warning fa fa-edit"><span class="action-top tooltip">編集</span></span></a> ' . $deleteBtn . $activateBtn . '</div></div>';
              })
          ])
          ->setComponents([
            (new THead())
              ->setComponents([
                (new ColumnHeadersRow),
                  (new FiltersRow)
                    ->setComponents([
                      (new RenderFunc(function() {
                          return "";
                      }))
                          ->setRenderSection('filters_row_column_name')
                    ]),
                (new OneCellRow)
                  ->setRenderSection(RenderableRegistry::SECTION_END)
                  ->setComponents([
                    (new RecordsPerPage)
                      ->setVariants([25, 50, 100, 1000])
                      ->setTemplate('webapp/components/nayjest_records_per_page_template'),
                    (new HtmlTag)
                      ->setContent('フィルタ')
                      ->setTagName('button')
                      ->setRenderSection(RenderableRegistry::SECTION_END)
                      ->setAttributes([
                        'class' => 'btn btn-success btn-sm filter-user'
                      ])
                  ])
              ])
              ,
            (new TFoot)
              ->setComponents([
                (new OneCellRow)
                  ->setComponents([
                    (new Pager),
                    (new HtmlTag)
                      ->setAttributes(['class' => 'pull-right'])
                      ->addComponent((new ShowingRecords)->setTemplate('webapp/components/nayjest_showing_records_template')),
                  ])
              ])
              ,
          ]);
        $grid = new Grid($cfg);
        $grid = $grid->render();
        return view('webapp/user/index', [
          'users' => User::all(), // hình như không dùng tới
          'grid' => $grid,
          'is_filter' => count(array_column($request->all(), 'filters')) ? true : false
        ]);
    }

    public function getCreate() {
        return view('webapp/user/create');
    }

    public function getMassCreate() {
        return view('webapp/user/createmulti');
    }

    public function getEdit($user_id){
        $user = User::find($user_id);
        $current_user = $this->currentUser;
        if(!$user){
            exit('404 Not Found');
        }

        return view('webapp/user/edit', [
            'user' => $user,
            'current_user' => $current_user
        ]);
    }

    public function getDelete($user_id, Request $request){
        $user = User::find($user_id);
        if(!$user){
            exit('404 Not Found');
        }

        if($this->currentUser->id == $user_id || !$user->delete()) {
            $request->session()->set('flashMessageSet', (new MessageSet())->setType('warning')->add(new Message('NOT OK', 'warning')));
            return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
        }

        $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('OK', 'success')));
        return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
    }

    public function postEdit($user_id = 0, Request $request) {
        $password = $request->get('password', '');
        $fullname = $request->get('fullname');
        $crowdworks_id = $request->get('crowdworks_id');
        $role = $request->get('role');

        $dataUpdate = [
            'fullname' => $fullname,
            'crowdworks_id' => $crowdworks_id,
            'role' => $role
        ];
        if(!empty($password)) {
            $dataUpdate['password'] = $password;
        }

        $userFactory = UserFactory::find(['conditions' => [
          [ 'where' => ['id', $user_id]]
        ]])->get(0);
        if(empty($userFactory)){
            $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('ユーザーが保存していません。', 'warning')));
            return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
        }

        $userFactory->bind($dataUpdate)->save();

        if(!$userFactory->saved()) {
            return view('webapp/user/edit', [
              'errorsSet' => $userFactory->error(),
              'user' => $userFactory->getObject(),
              'current_user' => $this->currentUser
            ]);
        }

        $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('OK', 'success')));
        return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
    }

    public function postCreate(Request $request) {
        $username = $request->get('username');
        $password = $request->get('password');
        $fullname = $request->get('fullname');
        $crowdworks_id = $request->get('crowdworks_id');
        $role = $request->get('role');

        $userFactory = UserFactory::create()->bind([
          'username' => $username,
          'password' => $password,
          'fullname' => $fullname,
          'crowdworks_id' => $crowdworks_id,
          'role'     => $role,
        ])->save();

        if(!$userFactory->saved()) {
            return view('webapp/user/create', [
              'errorsSet' => $userFactory->error()
            ]);
        }
        $request->session()->set('flashMessageSet', (new MessageSet())->setType('success')->add(new Message('OK', 'success')));
        return redirect()->to(RouteMap::get(RouteMap::USER_LIST))->send();
    }


    public function postMassCreate(Request $request) {
        $usernames = $request->get('username', null);

        if(empty($usernames)) {
            return redirect()->to(action('Webapp\\UserController@getMassCreate'))->send();
        }

        $passwords = $request->get('password');
        $fullnames = $request->get('fullname');
        $crowdworksIds = $request->get('crowdworks_id');
        $roles = $request->get('role');

        $errors = [];
        $successfulUsers = [];

        foreach($usernames as $key => $username) {
            if(empty($username)) {
                continue;
            }

            $userFactory = UserFactory::create()->bind([
                'username' => $username,
                'password' => $passwords[$key],
                'fullname' => $fullnames[$key],
                'crowdworks_id' => $crowdworksIds[$key],
                'role'     => $roles[$key],
            ])->save();

            if(!$userFactory->saved()) {
                $errors[$username] = $userFactory->error()->next()->getMessage();
                continue;
            }

            $successfulUsers[] = $username;
        }

        return view('webapp/user/createmulti', [
            'errorsSet' => $errors,
            'successfulUsers' => $successfulUsers,
        ]);
    }

    public function postFromCsv(Request $request) {
        set_time_limit(0);
        $file = $request->file('csv_file');
        if(!$file->isValid()) {
            $this->_flashMessage($request, 'File is not valid', 'danger');
            return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
        }

        if(($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1024, ",")) !== FALSE) {
                UserFactory::create()->bind([
                    'username' => $data[0],
                    'password' => $data[1],
                    'fullname' => $data[2],
                    'crowdworks_id' => $data[3],
                    'role'     => isset($data[4]) ? $data[4] : 'editor',
                ])->save();
            }
            fclose($handle);
        }

        $this->_flashMessage($request, 'OK', 'success');
        return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
    }

    public function getActivate($userId = 0) {
        $user = User::find($userId);
        if(!$user){
            exit('404 Not Found');
        }

        if($user->isDeactive()) {
            $user->reActivate();
        }

        return redirect()->to(action('Webapp\\UserController@getIndex'))->send();
    }
}
