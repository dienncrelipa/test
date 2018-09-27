<?php

namespace App\Http\Controllers\Webapp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\RenderFunc;
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
use Nayjest\Grids\SelectFilterConfig;
use App\Libs\CdsApiDriver;


class AdminController extends NeedAuthController
{
    public function __construct() {
        parent::__construct();
        if( $this->currentUser->username !== 'relipa_admin') {
            exit('401 Unauthorized!');
        }
    }

    public function getIndex(Request $request)
    {
        if (!empty(session('current_user')->is('admin'))) {
            return view('webapp/admin/admin');
        } else {
            return "User not permission";
        }
    }

    public function getDataCheckTitle()
    {
        if (!empty(session('current_user')->is('admin'))) {

            $query = DB::getDoctrineConnection()->createQueryBuilder();
            $query->select([
                'p.id',
                'title',
                'published_status',
                'target_post_id',
                'site_url',
                'name',
                'p.site_id',
                'COUNT(title) as count_title'
            ])
                ->from('posts', 'p')
                ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
                ->where('published_status = 1')
                ->groupBy('title')
                ->having('count_title > 1');

            $cfg = (new GridConfig())
                ->setDataProvider(
                    new DbalDataProvider($query)
                )
                ->setColumns([
                    (new FieldConfig)
                        ->setName('title')
                        ->setLabel('タイトル')
                        ->setSortable(true)
                        ->setSorting(Grid::SORT_ASC)
                        ->setCallback(function ($val, ObjectDataRow $row) {
                            $title = $row->getCellValue('title');
                            $site_id = $row->getCellValue('site_id');
                            return '<a href="' . action('Webapp\AdminController@getPostsError', ['title' => $title, 'site_id' => $site_id]) . '"</a>' . $val . '</td>';
                        }),
                    (new FieldConfig)
                        ->setName('name')
                        ->setLabel('サイト名'),
                    (new FieldConfig)
                        ->setName('count_title')
                        ->setLabel('重複記事の数')
                        ->setCallback(function ($count_title, ObjectDataRow $row) {
                            $title = $row->getCellValue('title');
                            $site_id = $row->getCellValue('site_id');
                            return '<a href="' . action('Webapp\AdminController@getPostsError', ['title' => $title, 'site_id' => $site_id]) . '"</a>' . $count_title . '</td>';
                        }),
                ])
                ->setComponents([
                    (new THead())
                        ->setComponents([
                            (new ColumnHeadersRow),
                            (new FiltersRow)
                                ->setComponents([
                                    (new RenderFunc(function () {
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
                                        ->setContent('チェック')
                                        ->setTagName('button')
                                        ->setRenderSection(RenderableRegistry::SECTION_END)
                                        ->setAttributes([
                                            'class' => 'btn btn-primary btn-sm filter-post'
                                        ])
                                ])
                        ]),
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
            $counter = $grid->getConfig()->getDataProvider()->getPaginator()->toArray()['total'];
            $grid = $grid->render();

            return view('webapp/admin/title', [
                'posts_grid' => $grid,
                'counter' => $counter
            ]);

        } else {
            return "User not permission";
        }
    }

    public function getPostsError(Request $request)
    {
        if (empty(session('current_user')->is('admin')))
            return "User not permission";

        $site_id = (is_numeric($request->site_id)) ? $request->site_id : 0;

        $query = DB::getDoctrineConnection()->createQueryBuilder();
        $query->select([
            'p.id',
            'title',
            'published_status',
            'target_post_id',
            'site_url',
            'fullname',
            'name',
            'p.user_id',
            'p.site_id'
        ])
            ->from('posts', 'p')
            ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
            ->leftJoin('p', 'users', 'u', 'p.user_id=u.id')
            ->where('published_status = 1 AND p.title = \''.$request->title.'\' AND p.site_id = '.$site_id.'');

        $cfg = (new GridConfig())
            ->setDataProvider(
                new DbalDataProvider($query)
            )
            ->setColumns([
                (new FieldConfig)
                    ->setName('id')
                    ->setLabel('ID')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC)
                    ->setCallback(function ($pid) {
                        return $pid;
                    }),
                (new FieldConfig)
                    ->setName('title')
                    ->setLabel('タイトル')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_ASC)
                    ->setCallback(function ($val, ObjectDataRow $row) {
                        $post_id = $row->getCellValue('id');
                        return '<a href="' . action('Webapp\PostController@getEdit', $post_id) . '"</a>' . $val . '</td>';
                    }),
                (new FieldConfig)
                    ->setName('fullname')
                    ->setLabel('ライター名')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_ASC)
                    ->setCallback(function ($val, ObjectDataRow $obj) {
                        $user_id = $obj->getCellValue('user_id');
                        return '<a href="' . action('Webapp\UserController@getEdit', $user_id) . '">' . $val . '</a>';
                    }),
                (new FieldConfig)
                    ->setName('name')
                    ->setLabel('記事リンク')
                    ->setCallback(function ($val, ObjectDataRow $row) {
                        $site_url = $row->getCellValue('site_url');
                        $target_post_id = $row->getCellValue('target_post_id');
                        $post_link = !empty($target_post_id) ? $site_url . "?p=" . $target_post_id : "";
                        return '<a target="_blank" href="' . $post_link . '">' . $post_link . '</a>';
                    })
            ]);

        $grid = new Grid($cfg);
        $grid = $grid->render();

        return view('webapp/admin/post_list_error', [
            'posts_grid' => $grid,
        ]);
    }

    public function getDataCheckLinkTarget()
    {
        if (!empty(session('current_user')->is('admin'))) {

            $query = DB::getDoctrineConnection()->createQueryBuilder();
            $query->select([
                'p.id',
                'title',
                'published_status',
                'target_post_id',
                'site_url',
                'fullname',
                'name',
                'p.word_count',
                'p.user_id',
                'p.site_id'
            ])
                ->from('posts', 'p')
                ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
                ->innerJoin('p', 'users', 'u', 'p.user_id=u.id')
                ->where('((published_status = 1) AND (p.target_post_id = 0 OR p.target_post_id IS NULL))');

            $cfg = (new GridConfig())
                ->setDataProvider(
                    new DbalDataProvider($query)
                )
                ->setColumns([
                    (new FieldConfig)
                        ->setName('id')
                        ->setLabel('ID')
                        ->setSortable(true)
                        ->setSorting(Grid::SORT_DESC)
                        ->setCallback(function ($pid) {
                            return $pid;
                        }),
                    (new FieldConfig)
                        ->setName('title')
                        ->setLabel('タイトル')
                        ->setSortable(true)
                        ->setSorting(Grid::SORT_ASC)
                        ->setCallback(function ($val, ObjectDataRow $row) {
                            $post_id = $row->getCellValue('id');
                            return '<a href="' . action('Webapp\PostController@getEdit', $post_id) . '"</a>' . $val . '</td>';
                        }),
                    (new FieldConfig)
                        ->setName('fullname')
                        ->setLabel('ライター名')
                        ->setSortable(true)
                        ->setSorting(Grid::SORT_ASC)
                        ->setCallback(function ($val, ObjectDataRow $obj) {
                            $user_id = $obj->getCellValue('user_id');
                            return '<a href="' . action('Webapp\UserController@getEdit', $user_id) . '">' . $val . '</a>';
                        }),
                    (new FieldConfig)
                        ->setName('name')
                        ->setLabel('記事リンク')
                        ->addFilter(
                            (new SelectFilterConfig)
                                ->setDefaultValue('null')
                                ->setTemplate('webapp.components.nayjest_site_id_filter_template')
                                ->setFilteringFunc(function ($val, DbalDataProvider $dap) {
                                    return $dap->getBuilder()->andWhere("p.site_id= $val")->addGroupBy('p.id');
                                })
                        )
                        ->setCallback(function ($val, ObjectDataRow $row) {
                            $site_url = $row->getCellValue('site_url');
                            $target_post_id = $row->getCellValue('target_post_id');
                            $post_link = !empty($target_post_id) ? $site_url . "?p=" . $target_post_id : "";
                            return '<a target="_blank" href="' . $post_link . '">' . $post_link . '</a>';
                        }),
                    (new FieldConfig)
                        ->setName('word_count')
                        ->setLabel('文字数')
                        ->setCallback(function ($val) {
                            return $val === -1 ? '未更新' : $val;
                        }),
                ])
                ->setComponents([
                    (new THead())
                        ->setComponents([
                            (new ColumnHeadersRow),
                            (new FiltersRow)
                                ->setComponents([
                                    (new RenderFunc(function () {
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
                                        ->setContent('チェック')
                                        ->setTagName('button')
                                        ->setRenderSection(RenderableRegistry::SECTION_END)
                                        ->setAttributes([
                                            'class' => 'btn btn-primary btn-sm filter-post'
                                        ])
                                ])
                        ]),
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
            $counter = $grid->getConfig()->getDataProvider()->getPaginator()->toArray()['total'];
            $grid = $grid->render();
            return view('webapp/admin/link', [
                'posts_grid' => $grid,
                'counter' => $counter
            ]);

        } else {
            return "User not permission";
        }
    }

    public function exportCsvLinkTarget()
    {
        $file = fopen('data.csv', 'w');

        $query = DB::getDoctrineConnection()->createQueryBuilder();
        $columns = array('id', 'タイトル', 'ライター名', 'キーワード', 'ステータス', '記事リンク', '文字数', '編集');
        fputcsv($file, $columns, ',');

        $query->select([
            'p.id',
            'p.title',
            'fullname',
            'keyword',
            'name',
            'p.word_count',
        ])
            ->from('posts', 'p')
            ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
            ->leftJoin('p', 'post_meta', 'pm', 'p.id = pm.post_id')
            ->leftJoin('pm', 'keywords', 'k', 'pm.keyword_id = k.id')
            ->innerJoin('p', 'users', 'u', 'p.user_id=u.id')
            ->where('((status = 1) AND (published_status = 1) AND (p.target_post_id = 0 OR p.target_post_id IS NULL)) GROUP BY p.id');


        $dataQuery = $query->execute()->fetchAll();
        $dataQueryLenght = count($dataQuery);
        for ($i = 0; $i < $dataQueryLenght; $i++) {
            fputcsv($file, $dataQuery[$i], ',');
        }

        fclose($file);

        $file = 'data.csv';
        if (file_exists($file)) {
            return response()->download($file)->deleteFileAfterSend(true);
        }
    }

    public function exportCsvTitleDuplicated()
    {
        $file = fopen('dataDuplicated.csv', 'w');

        $columns = array('id', 'タイトル', 'ライター名', 'キーワード', 'ステータス', '記事リンク', '文字数', '編集');
        fputcsv($file, $columns, ',');

        $query = DB::getDoctrineConnection()->createQueryBuilder();
        $query->select([
            'p.id',
            'p.title',
            'fullname',
            'keyword',
            'name',
            'p.word_count',
        ])
            ->from('posts', 'p')
            ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
            ->leftJoin('p', 'post_meta', 'pm', 'p.id = pm.post_id')
            ->leftJoin('pm', 'keywords', 'k', 'pm.keyword_id = k.id')
            ->innerJoin('p', 'users', 'u', 'p.user_id=u.id')
            ->where('(s.status = 1) AND ((published_status = 1) AND (p.title IN (SELECT title FROM posts INNER JOIN sites ON posts.site_id = sites.id  WHERE (sites.status = 1 AND published_status = 1) GROUP BY title HAVING COUNT(posts.id)>1))) GROUP BY p.id');

        $dataQuery = $query->execute()->fetchAll();
        $dataQueryLenght = count($dataQuery);
        for ($i = 0; $i < $dataQueryLenght; $i++) {
            fputcsv($file, $dataQuery[$i], ',');
        }

        fclose($file);

        $file = 'dataDuplicated.csv';
        if (file_exists($file)) {
            return response()->download($file)->deleteFileAfterSend(true);
        }
    }

    public function getDataPostStatus(Request $request)
    {
        ini_set('max_execution_time', 60);
        if ($request->input('checkOK') == null) {
            return view('webapp/admin/status', []);
        }
        $data = array();
        $check = array();

        $query = DB::table('sites')
            ->select('site_url', 'api_url')
            ->where('status', '=', 1)
            ->get();

        $check_error = [];
        for ($i = 0; $i < count($query); $i++) {
            try {
                $responseData = (new CdsApiDriver($query[$i]->api_url, []))->send('similarPostsList')->getResult();
            } catch (\Exception $e) {
                Log::info("Error send get data import 1 CSV : " . $e->getMessage());
                continue;
            }

            try {
                $file = fopen($responseData->data, 'r');
            } catch (\Exception $e) {
                Log::info("Open file import 1 CSV fails: " . $e->getMessage());
                $check_error[] = parse_url($responseData->data)['host'];
                continue;
            }

            if ($error_total = count($check_error)) {
                $check_status_message= '';
                $check_status_message .= implode(', ', $check_error);
                $request->session()->flash('check_error', $check_status_message);
            }

            fgetcsv($file);
            for ($j = 0; $row = fgetcsv($file); ++$j) {
                $key = trim($row[2]);
                if (!empty($check[$key])) {
                    $check[$key] = $check[$key] + 1;
                } else {
                    $check = array_add($check, $key, 1);
                }
                $row[3] = $query[$i]->site_url;
                array_push($data, $row);
            }
            fclose($file);
        }


        $dataResult = array();
        $i = 0;
        foreach ($check as $key => $value) {
            $checkValue = intval(trim($value));
            if ($key != 0 && $checkValue > 1) {
                $query = DB::table('posts')
                    ->SELECT('posts.id', 'title', 'fullname', 'name', 'site_url', 'target_post_id')
                    ->JOIN('sites', 'posts.site_id', '=', 'sites.id')
                    ->Join('users', 'posts.user_id', '=', 'users.id')
                    ->WHERE([['posts.id', '=', intval(trim($key))], ['status', '=', 1]])
                    ->groupby('posts.id')
                    ->first();
                if (!Empty($query)) {
                    $str = '';
                    foreach ($data as $val) {
                        if ($val[2] == $query->id) {
                            $str = $str . '<a href ="' . $val[3] . '?p=' . trim($val[1]) . '">' . trim($val[1]) . '</a></br>';
                        }
                    }
                    if ($str != '') {
                        $tmp = array(
                            'id' => $query->id,
                            'title' => $query->title,
                            'fullname' => $query->fullname,
                            'name' => $query->name,
                            'site_url' => $query->site_url,
                            'target_post_id' => $query->target_post_id,
                            'id_post_target' => $str,
                        );
                        $dataResult = array_add($dataResult, $i, $tmp);
                        $i++;
                    }
                }
            }
        }

        return view('webapp/admin/status', ['data' => $dataResult]);
    }

    public function getPostStatusChecker()
    {

        if (isset($_GET['order'])) {
            $i = intval($_GET['order']);
            $jsonCheck = json_decode(file_get_contents(public_path('res/post-checker/result.json')));

            if ($i > (count($jsonCheck) - 1)) {
                echo json_encode(
                    [
                        'status' => -1,
                        'checking' => null,
                        'url' => url('res/post-checker/result.csv')
                    ]
                );
            } else {
                if (filter_var($jsonCheck[$i], FILTER_VALIDATE_URL)) {
                    $headers = get_headers($jsonCheck[$i]);
                    if ($headers) {
                        if (strpos($headers[0], '200')) {
                            $this->rwFile('result.csv', $i . ',' . $jsonCheck[$i] . ',' . 'PUBLISH' . PHP_EOL);
                        } else {
                            $this->rwFile('result.csv', $i . ',' . $jsonCheck[$i] . ',' . 'DRAFT' . PHP_EOL);
                        }
                    }
                }

                echo json_encode(
                    [
                        'status' => $i,
                        'checking' => $jsonCheck[$i],
                        'url' => url('res/post-checker/result.csv')
                    ]
                );
            }

        }
    }

    public function rwFile($file, $data)
    {
        $fileToWritePath = public_path('res/post-checker/' . $file);
        file_put_contents($fileToWritePath, $data, FILE_APPEND);
    }

    public function postUpCsv(\Illuminate\Http\Request $request)
    {
        $file = $request->file('csv_file');
        if ($file === null) {
            return redirect()->to(action('Webapp\\AdminController@getIndex'))->send();
        }

        if ($this->is_dir_empty(public_path('res/post-checker')) === false) {
            $globs = glob(public_path('res/post-checker/*.*'));
            foreach ($globs as $item) {
                unlink($item);
            }
        }

        $csvFile = $file->move(public_path('res/post-checker'), $file->getClientOriginalName());
        $csvRead = fopen($csvFile, 'r');
        $line = 1;
        $csvJson = array();
        set_time_limit(100);
        while (($data = fgetcsv($csvRead)) !== FALSE) {
            if ($line > 1) {
                $csvJson[] = $data[0];
            }
            $line++;
        }

        $json = json_encode($csvJson); //chuyen thanh json
        $resultJson = public_path("res/post-checker/result.json");
        if (file_exists($resultJson)) {
            unlink($resultJson);
        }
        $jsonFileToSave = fopen($resultJson, "w");
        fwrite($jsonFileToSave, $json);
        fclose($jsonFileToSave);

        return redirect()->to(action('Webapp\\AdminController@getIndex'))->send();
    }

    public function is_dir_empty($dir)
    {
        if (!is_readable($dir)) return NULL;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return FALSE;
            }
        }
        return TRUE;
    }

}