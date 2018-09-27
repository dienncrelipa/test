<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 4:41 PM
 */

namespace App\Http\Controllers\Webapp;


use App\Exceptions\TitleDuplicatedException;
use App\Models\Activity;
use App\Models\Backup;
use App\Models\Keywords;
use App\Libs\CdsApiDriver;
use App\Libs\MessagesContainer\Message;
use App\Libs\MessagesContainer\MessageSet;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostHistory;
use App\Models\PostMeta;
use App\Models\Site;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Nayjest\Grids\Components\FiltersRow;
use Nayjest\Grids\Components\RenderFunc;
use Nayjest\Grids\Components\SelectFilter;
use Nayjest\Grids\EloquentDataProvider;
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

use HTML;
use Nayjest\Grids\SelectFilterConfig;
use Psy\Exception\BreakException;

class PostController extends NeedAuthController
{
    public function __construct(Route $route, Request $request) {
        parent::__construct();

        $this->activityLog = new Activity();
        $this->activityLog->setSession($this->currentSession);

        $actionName = explode("\\", $route->getActionName());
        $actionName = $actionName[count($actionName)-1];

        $this->activityLog->setAction($actionName);
        if(($postId = $request->get('post_id', false)) !== false) {
            $this->activityLog->setPostId($postId);
        }
    }

    public function __destruct() {
        $exceptActions = [
            'PostController@getIndex',
            'PostController@postStashChange',
//            'PostController@postLock',
            'PostController@postAutoSave'
        ];

        if(array_search($this->activityLog->action, $exceptActions) !== false) {
            return;
        }

        $this->activityLog->save();
    }

    public function getIndex(Request $request) {
        $query = DB::getDoctrineConnection()->createQueryBuilder();
        $query->select([
                'p.id',
                'title',
                'published_status',
                'target_post_id',
                'p.id_post_old',
                'site_url',
                'fullname',
                'name',
                'keyword',
                'p.word_count',
                'type',
                'p.user_id',
                'p.is_delete'
            ])
            ->from('posts', 'p')
            ->leftJoin('p', 'sites', 's', 'p.site_id=s.id AND s.status = 1')
            ->leftJoin('p', 'post_meta', 'pm', 'p.id = pm.post_id')
            ->leftJoin('pm', 'keywords', 'k', 'pm.keyword_id = k.id')
            ->innerJoin('p', 'users', 'u', 'p.user_id=u.id');

        $userQueryWhere = '';
        if(!$this->currentUser->is(User::ADMIN)) {
            $userQueryWhere = ' AND is_delete = 0 ';
        }
        if($this->currentUser->is(User::EDITOR) || $this->currentUser->is(User::CONTRIBUTOR)) {
            $userQueryWhere .= ' AND p.user_id = '.$this->currentUser->id;
        }
        if($request->get('keyword')) {
            $_kw = urldecode($request->get('keyword'));
            $queryWhere = '((p.site_id > 0 AND s.id > 0) OR (p.site_id = 0 OR p.site_id IS NULL)) AND keyword=\'' . mysqli_real_escape_string(new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT')), $_kw) . '\' AND k.type = 1'.$userQueryWhere;
        } else if(empty($request->all())) {
            $queryWhere = '((p.site_id > 0 AND s.id > 0) OR (p.site_id = 0 OR p.site_id IS NULL)) ' . $userQueryWhere . ' GROUP BY p.id';
        } else {
            $filters = array_column($request->all(), 'filters');

            if($filters && count($filters[0]) == 1 && isset($filters[0]['records_per_page'])) {
                $queryWhere = '((p.site_id > 0 AND s.id > 0) OR (p.site_id = 0 OR p.site_id IS NULL)) '.$userQueryWhere . ' GROUP BY p.id';
            } else if(count($filters) == 0) {
                $queryWhere = '((p.site_id > 0 AND s.id > 0) OR (p.site_id = 0 OR p.site_id IS NULL)) '.$userQueryWhere . ' GROUP BY p.id';
            } else {
                $queryWhere = '((p.site_id > 0 AND s.id > 0) OR (p.site_id = 0 OR p.site_id IS NULL)) '.$userQueryWhere;
            }

        }

        $query->where($queryWhere);

        $cfg = (new GridConfig())
          ->setDataProvider(
            new DbalDataProvider($query)
          )
          ->setColumns([
            (new FieldConfig)
              ->setName('id')
              ->setLabel('ID')
              ->addFilter(
                (new SelectFilterConfig())
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setDefaultValue(null)
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                    return $dap->getBuilder()->andWhere("p.id = $val")->addGroupBy('p.id');
                })
              )
              ->setSortable(true)
              ->setSorting(Grid::SORT_DESC)
              ->setCallback(function($pid) {
                  return "<input type='checkbox' name='selected_ids[]' value='$pid' style='display: none'/>".$pid;
              }),
            (new FieldConfig)
              ->setName('title')
              ->setLabel('タイトル')
              ->setSortable(true)
              ->setSorting(Grid::SORT_ASC)
              ->setCallback(function ($val, ObjectDataRow $row) {
                  $post_id = $row->getCellValue('id');
                  return '<a href="' . action('Webapp\PostController@getEdit', $post_id) . '"</a>'.$val.'</td>';
              })
              ->addFilter(
                (new FilterConfig)
                  ->setOperator(FilterConfig::OPERATOR_LIKE)
                  ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                      $val = mysqli_real_escape_string(new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT')), $val);
                      return $dap->getBuilder()->andWhere("title like '%$val%'")->addGroupBy('p.id');
                  })
              ),
            (new FieldConfig)
              ->setName('fullname')
              ->setLabel('ライター名')
              ->setSortable(true)
              ->setSorting(Grid::SORT_ASC)
              ->addFilter(
                (new SelectFilterConfig())
                  ->setDefaultValue(null)
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                      return $dap->getBuilder()->andWhere("u.id = $val")->addGroupBy('p.id');
                  })
              )
                ->setCallback(function($val, ObjectDataRow $obj) {
                    $user_id = $obj->getCellValue('user_id');
                    if($this->currentUser->is('admin')) {
                        return '<a href="' . action('Webapp\UserController@getEdit', $user_id) . '">' . $val . '</a>' ;
                    }
                    return $val;
                }),
            (new FieldConfig)
              ->setName('keyword')
              ->setLabel('キーワード')
              ->addFilter(
                (new SelectFilterConfig())
                  ->setDefaultValue(null)
                  ->setName('keyword_fil')
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                      return $dap->getBuilder()->andWhere("k.id = $val")->addGroupBy('p.id');
                  })
              )
              ->setCallback(function($val, ObjectDataRow $obj) {
                  return $obj->getCellValue('type') == 1 ? '<a href="' . url('/webapp/post') . '?keyword=' . urlencode($val) . '">' . $val . '</a>' : '';
              }),
            (new FieldConfig)
              ->setName('published_status')
              ->setLabel('ステータス')
              ->setSortable(true)
              ->setSorting(Grid::SORT_ASC)
              ->setCallback(function($val) {
                  return $val == 1 ? $val = '<span class="label label-success fa fa-check"><span class="tooltip action-top">公開済み</span></span>' : $val = '<span class="label label-default fa fa-minus-circle"><span class="tooltip action-top">下書き</span></span>';
              })
              ->addFilter(
                  (new SelectFilterConfig())
                      ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                          return $dap->getBuilder()->andWhere("p.published_status = $val")->addGroupBy('p.id');
                      })
                    ->setOptions(['1' => '公開済み', '0' => '下書き'])
              ),
            (new FieldConfig)
              ->setName('name')
              ->setLabel('記事リンク')
              ->addFilter(
                (new SelectFilterConfig)
                  ->setDefaultValue(null)
                  ->setName('site_id')
                  ->setOperator(FilterConfig::OPERATOR_EQ)
                  ->setTemplate('webapp.components.nayjest_columns_filter')
                  ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                      $post= false;
                      $site = false;
                      if(strpos($val,'SITE-') !== false){
                          $site = str_replace('SITE-', '', $val);
                          return $dap->getBuilder()->andWhere("p.site_id = $site")->addGroupBy('p.id');
                      } else{
                          return $dap->getBuilder()->andWhere("p.id = $val")->addGroupBy('p.id');
                      }
                  })
              )
              ->setCallback(function($val, ObjectDataRow $row) {
                  $site_url = $row->getCellValue('site_url');
                  $target_post_id = $row->getCellValue('target_post_id');
                  $post_link = !empty($target_post_id) ? $site_url . "?p=".$target_post_id : "";
                  return '<a target="_blank" href="' . $post_link . '">' . $post_link . '</a>';
              }),
            (new FieldConfig)
               ->setName('word_count')
               ->setLabel('文字数')
               ->setCallback(function($val){
                   return $val === -1 ? '未更新' : $val;
               }),
              (new FieldConfig)
                  ->setName('id_post_old')
                  ->setLabel('元ID')
                  ->setCallback(function($val){
                      return $val === 0 ? '' : $val;
                  }),
            (new FieldConfig)
              ->setName('is_delete')
              ->setLabel('編集')
              ->setCallback(function($val, ObjectDataRow $row) {
                  $clonePost = new Post();
                  $clonePost->id = $row->getCellValue('id');
                  $clonePost->user_id = $row->getCellValue('user_id');
                  $clonePost->is_delete = $row->getCellValue('is_delete');
                  $clonePost->published_status = $row->getCellValue('published_status');

                  $status = $row->getCellValue('published_status');
                  $post_id = $row->getCellValue('id');
                  $action_delete = $this->currentUser->is(User::ADMIN) ? 'Webapp\PostController@getDelete' : 'Webapp\PostController@getWriterDelete';
                  $is_delete = '';
                  if($val){
                      $is_delete = '<span class="is-delete"></span>';
                  }
                  $url_pub_draft = '';

                  $nextStatusButton = ($status == 1) ? 'Draft' : 'Publish';

                  if($clonePost->{'can'.$nextStatusButton.'By'}($this->currentUser)) {
                      $url_pub_draft = '<a href="'. action(($status==0 ? 'Webapp\PostController@getPublish' : 'Webapp\PostController@getDraft' ), $post_id) . '?hasFilter">'
                          . '<span class="label'. ($status==0 ? ' label-success fa fa-check' : ' label-default fa fa-minus-circle' ) .'"><span class="tooltip action-top">'. ($status==0 ? '公開' : '下書きに戻す' ) .'</span></span>'
                          . '</a> ';
                  }

                  $editHtml = '<a href="' . action('Webapp\PostController@getEdit', $post_id) . '">'
                      . '<span class="label label-warning fa fa-edit"><span class="action-top tooltip">編集</span></span>'
                      . '</a> ';

                  $copyHtml = ($clonePost->canCopyBy($this->currentUser)) ? '<a href="' . action('Webapp\PostController@getCopyPost', $post_id) . '?hasFilter">'
                      . '<span class="label label-primary fa fa-copy"><span class="action-top tooltip">コピー</span></span>'
                      . '</a> ' : '';

                  $deleteHtml = $clonePost->canWriteDeleteBy($this->currentUser) ? '<a href="' . action($action_delete, $post_id) . '?hasFilter" class="btn-delete-post" data-title="ID' . $post_id . 'の記事を削除">'
                      . '<span class="label label-danger fa fa-trash"><span class="action-top tooltip">削除</span></span>'
                      . '</a>' : '';

                  return '<div class="btn-toolbar" role="toolbar" aria-label="Action button group toolbar">'
                                            . '<div class="btn-group" role="group" aria-label="Action group button">'
                                                . $url_pub_draft
                                                . $editHtml
                                                . $copyHtml
                                                . $deleteHtml
                                            . '</div>'
                                        . '</div>' . $is_delete;
              }),
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
                        'class' => 'btn btn-success btn-sm filter-post'
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
        return view('webapp/posts/index', [
          'posts_grid' => $grid,
          'action_delete' => $this->currentUser->is(User::ADMIN) ? 'delete' : 'writerDelete',
          'is_filter' => count(array_column($request->all(), 'filters')) ? true : false
        ]);
    }

    public function getCreate(Request $request) {
        $categoriesSet = [];
        $sites = Site::where('status', 1)->get();

        if(count($sites) == 0) {
            $request->session()->flash('flashMessageSet',
              (new MessageSet())->setType('warning')->add(new Message('ターゲットサイトがありません。記事を新規作成する前にターゲットサイトを追加してください', 'warning'))
            );

            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        foreach(Category::all() as $category) {
            if(!isset($categoriesSet[$category->site_id])) {
                $categoriesSet[$category->site_id] = [];
            }
            $categoriesSet[$category->site_id][] = $category;
        }

        return view('webapp/posts/create', [
          'sites' => $sites,
          'categoriesSet' => $categoriesSet
        ]);
    }

    public function postCreate(Request $request) {
        $postTitle = $request->get('post_title');
        $postContent = $request->get('post_content');
        $postDescription = trim($request->get('post_description'));
        if($request->get('site_id') == ''){
            $this->_flashMessage($request, 'ターゲットサイトを選んでください', 'danger');
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        $post = new Post();

        if(($postId = $request->get('post_id', 0)) != 0) {
            $post = (!empty($postTmp = Post::find($postId)) && $postTmp->user_id == $this->currentUser->id) ? $postTmp : $post;
        }
        $post->title = !empty($postTitle) ? $postTitle : 'タイトルなし';
        $post->content = !empty($postContent) ? $postContent : '&nbsp;';
        $post->user_id = $this->currentUser->id;
        $post->site_id = $request->get('site_id');
        $post->category = $request->get('category');
        $post->description = $postDescription;
        $post->feature_img = $request->get('feature_img_url');
        $post->priority = (int)$request->get('post_priority', 0);
        $post->word_count = (int)$request->get('word_count', 0);

        $submitType = strpos($request->get('submit_type'), '公開') !== false ? 'publish' : 'draft';

        $post->save();

        $post->saveHistory();

        try {
            if(!$this->currentUser->is(User::CONTRIBUTOR) && $post->site_id > 0) {
                $apiUrl = Site::find($post->site_id)->api_url;
                $returnData = (new CdsApiDriver($apiUrl, [
                    'content' => $post->content,
                    'title' => $postTitle,
                    'categories' => [$post->category],
                    'post_excerpt' => $postDescription,
                    'post_status' => $submitType,
                    'feature_img' => $post->feature_img,
                    'author_name' => !empty($author = User::find($post->user_id)) ? $author->username : '',
                    'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                    'priority' => $post->priority,
                ]))->send('publish')->getResult();

                if($returnData->data->post_id == 0) {
                    throw new \Exception();
                }
                $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $post->content);
                $post->target_post_id = $returnData->data->post_id;
                $post->published_status = ($submitType == 'publish') ? 1 : 0;
            }

            $this->_flashMessage($request, $submitType == 'publish' ? '公開しました' : '下書きに保存しました', 'success');
        } catch(\Exception $e) {
            $post->published_status = 0;
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'warning');
        }

        $post->save();

        // Save kw and match post_id and kw_id
        // Main keyword
        $main = explode(',', $request->get('mainkw'));
        $uniqueMainKeyword = trim($main[0]);
        $mainKeyword = Keywords::getKeyword($uniqueMainKeyword, 1);

        if(empty($mainKeyword)) {//there is no keyword
            if(strlen($uniqueMainKeyword) > 0) {
                // Insert new main keyword
                $mainKeyword = new Keywords();
                $mainKeyword->keyword = $uniqueMainKeyword;
                $mainKeyword->type = 1;
                $mainKeyword->save();
            }
        }

        $postMetaMkw = PostMeta::getPostMeta($post->id, isset($mainKeyword->id) ? $mainKeyword->id : 0);
        if(empty($postMetaMkw) && isset($mainKeyword->id)) {
            // Insert new post-keyword relationship if not exist
            $postMetaMkw = new PostMeta();
            $postMetaMkw->post_id = $post->id;
            $postMetaMkw->keyword_id = $mainKeyword->id;
            $postMetaMkw->save();
        }

        // Sub keywords
        $subs = explode(',', $request->get('subkws'));
        foreach ($subs as $item) {
            $trimmedItem = trim($item);
            if(strlen($trimmedItem) == 0) {
                continue;
            }

            $subKeyword = Keywords::getKeyword($trimmedItem, 2);

            if(empty($subKeyword)) {
                // Insert new keyword
                $subKeyword = new Keywords();
                $subKeyword->keyword = $trimmedItem;
                $subKeyword->type = 2;
                $subKeyword->save();

                // Save post-keyword relationship
            }

            $postMetaSkw = PostMeta::getPostMeta($post->id, $subKeyword->id);
            if(empty($postMetaSkw)) {
                // Insert new post-keyword relationship if not exist
                $postMetaSkw = new PostMeta();
                $postMetaSkw->post_id = $post->id;
                $postMetaSkw->keyword_id = $subKeyword->id;
                $postMetaSkw->save();
            }


        }

        if(strpos($request->get('submit_type'),'公開') !== false || strpos($request->get('submit_type'),'下書き') !== false){
            return redirect()->to(action('Webapp\\PostController@getEdit', $post->id))->send();
        }else if(strpos($request->get('submit_type'),'プレビュー') !== false){
            return redirect()->to(action('Webapp\\PostController@getPreviewIframe', $post->id))->send();
        }

        return redirect()->to(action('Webapp\\PostController@getEdit', $post->id))->send();
    }

    public function postCreateAjax(Request $request) {
        $post = new Post();

        if(($postId = $request->get('post_id', 0)) != 0) {
            $post = (!empty($postTmp = Post::find($postId)) && $postTmp->user_id == $this->currentUser->id) ? $postTmp : $post;
        }

        $postTitle = $request->get('post_title');
        $postContent = $request->get('post_content');
        $postChecksum = $request->get('checksum', 0);
        $postDescription = trim($request->get('post_description'));

        if($post->isLock($this->currentSession)) {
            $this->activityLog->setInfo('Locked');
            return ['error' => [
                'message' => 'このコンテンツは現在ロックされています。編集できません'
            ]];
        }

        if(empty($postChecksum) && empty($postContent)) {
            $this->activityLog->setInfo('Empty content');
            return ['error' => [
                'message' => '投稿内容は空です'
            ]];
        }

        $calculatedChecksum = Post::calculateChecksum($postContent);

        if($postChecksum != $calculatedChecksum) {
            $this->activityLog->setInfo('Checksum failed');
            return ['error' => [
                'message' => 'サーバへ送信する内容にエラーがあります'
            ]];
        }

        $oldTitle = $post->title;

        $post->title = !empty($postTitle) ? $postTitle : 'タイトルなし';
        $post->content = !empty($postContent) ? $postContent : '&nbsp;';
        $post->user_id = $this->currentUser->id;
        $post->site_id = $request->get('site_id');
        $post->category = $request->get('category');
        $post->description = $postDescription;
        $post->feature_img = $request->get('feature_img_url');
        $post->priority = (int)$request->get('post_priority', 0);
        $post->published_status = 0;
        $post->word_count = (int)$request->get('word_count', 0);

        $submitType = strpos($request->get('submit_type'), '公開') !== false ? 'publish' : 'draft';

        if($submitType == 'publish' && !$post->canPublishBy($this->currentUser)) {
            $submitType = 'draft';
        }

        if($submitType == 'publish' && $this->currentUser->is(User::CONTRIBUTOR)){
            $this->activityLog->setInfo('Contributor can not publish');
            return '404 Not Found';
        }

        $post->save();

        $post->saveHistory();

        $this->activityLog->setPostId($post->id);
        $this->activityLog->addMeta('SubmitType', $submitType);

        if($submitType == 'publish'){
            try {
                if (!Post::isUniquePostByTitle($post)) {
                    throw new TitleDuplicatedException('このタイトルは存在しています。公開できないのでタイトルを変更してください。');
                }

                if (!$this->currentUser->is(User::CONTRIBUTOR) && $post->site_id > 0) {
                    $apiUrl = Site::find($post->site_id)->api_url;
                    $returnData = (new CdsApiDriver($apiUrl, [
                        'content' => $post->content,
                        'title' => $postTitle,
                        'categories' => [$post->category],
                        'post_excerpt' => $postDescription,
                        'post_status' => $submitType,
                        'feature_img' => $post->feature_img,
                        'author_name' => !empty($author = User::find($post->user_id)) ? $author->username : '',
                        'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                        'priority' => $post->priority,
                    ]))->send('publish')->getResult();

                    if ($returnData->data->post_id == 0) {
                        $this->activityLog->addMeta('PublishedStatus', 'failed');
                        throw new \Exception();
                    }
                    $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $post->content);
                    $post->target_post_id = $returnData->data->post_id;
                    $post->published_status = 1;
                    $this->activityLog->addMeta('PublishedStatus', 'success');
                    $this->activityLog->addMeta('TargetPostId', $post->target_post_id);
                }

                $this->_flashMessage($request, '公開しました', 'success');
            } catch(TitleDuplicatedException $e) {
                $this->_flashMessage($request, $e->getMessage(), 'danger');
                $this->activityLog->addMeta('Error', 'Title duplicated: '.$post->title);
                $post->title = $oldTitle;
            } catch(\Exception $e) {
                $post->published_status = 0;
                $this->activityLog->addMeta('PublishedStatus', 'failed');
                $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'warning');
            }

            $post->save();
        }else{
            $this->_flashMessage($request, '下書きに保存しました', 'success');
        }

        $this->activityLog->addMeta('NewPublishedStatus', $post->published_status);

        // Save kw and match post_id and kw_id
        // Main keyword
        $main = explode(',', $request->get('mainkw'));
        $uniqueMainKeyword = trim($main[0]);
        $mainKeyword = Keywords::getKeyword($uniqueMainKeyword, 1);

        if(empty($mainKeyword)) {//there is no keyword
            if(strlen($uniqueMainKeyword) > 0) {
                // Insert new main keyword
                $mainKeyword = new Keywords();
                $mainKeyword->keyword = $uniqueMainKeyword;
                $mainKeyword->type = 1;
                $mainKeyword->save();
            }
        }

        $postMetaMkw = PostMeta::getPostMeta($post->id, isset($mainKeyword->id) ? $mainKeyword->id : 0);
        if(empty($postMetaMkw) && isset($mainKeyword->id)) {
            // Insert new post-keyword relationship if not exist
            $postMetaMkw = new PostMeta();
            $postMetaMkw->post_id = $post->id;
            $postMetaMkw->keyword_id = $mainKeyword->id;
            $postMetaMkw->save();
        }

        // Sub keywords
        $subs = explode(',', $request->get('subkws'));
        foreach ($subs as $item) {
            $trimmedItem = trim($item);
            if(strlen($trimmedItem) == 0) {
                continue;
            }

            $subKeyword = Keywords::getKeyword($trimmedItem, 2);

            if(empty($subKeyword)) {
                // Insert new keyword
                $subKeyword = new Keywords();
                $subKeyword->keyword = $trimmedItem;
                $subKeyword->type = 2;
                $subKeyword->save();

                // Save post-keyword relationship
            }

            $postMetaSkw = PostMeta::getPostMeta($post->id, $subKeyword->id);
            if(empty($postMetaSkw)) {
                // Insert new post-keyword relationship if not exist
                $postMetaSkw = new PostMeta();
                $postMetaSkw->post_id = $post->id;
                $postMetaSkw->keyword_id = $subKeyword->id;
                $postMetaSkw->save();
            }


        }

        return [
            'data' => [
                'id' => $post->id,
                'checksum' => $calculatedChecksum
            ],
            'next' => action('Webapp\\PostController@getEdit', $post->id)
        ];
    }

    public function getEdit(Request $request, $postId = 0) {
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId))
            || !$post->canViewBy($this->currentUser)
            || ($post->site_id > 0 && Site::find($post->site_id)->status != 1) ) {
            $this->activityLog->setInfo('Not found');
            return '404 Not Found';
        }

        $readOnly = !$post->canEditBy($this->currentUser);

        // Lock detect
        if(!($lockStatus = $post->isLock($this->currentSession)) && !$readOnly) {
            $post->lock($this->currentSession);
        }

        $categoriesSet = [];

        foreach(Category::all() as $category) {
            if(!isset($categoriesSet[$category->site_id])) {
                $categoriesSet[$category->site_id] = [];
            }
            $categoriesSet[$category->site_id][] = $category;
        }

        $post->site = Site::find($post->site_id);

        $pmt = new PostMeta();
        $pmt_data = $pmt->getPostMetaByPostId($post->id);
        $subkws = '';
        $mainkw = '';
        foreach ($pmt_data as $value) {
            if($value->type == 2) {
                $subkws .= $value->keyword . ',';
            } elseif ($value->type == 1) {
                $mainkw .= $value->keyword . ',';
            }
        }
        $action_delete = $this->currentUser->is(User::ADMIN) ? 'Webapp\PostController@getDelete' : 'Webapp\PostController@getWriterDelete';

        $clientChecksum = $post->getLastClientChecksum();
        $recoverChecksum = null;

        if(!empty($checksum) && $post->getChecksum() != $clientChecksum) {
            $this->_flashMessage($request,
                'Content failed integrity check, maybe error when saving or database was manipulated. '.
                'Editor will try to recover from backup. If error continue, please contact admin with message: Checksum failed('.$checksum.')'
                , 'danger');
            $recoverChecksum = $clientChecksum;
        }

        $site = Site::find($post->site_id);
        $additionCss = (!empty($site)) ? $site->getAdditionCss() :null;
        $draftUnsaved = PostHistory::getFirstFrom($post, PostHistory::STATUS_DRAFT, $post->updated_at->toDateTimeString());

        return view('webapp/posts/edit'.(($readOnly) ? '_readonly' : ''), [
            'post' => $post,
            'lockStatus' => $lockStatus,
            'sites' => Site::where('status', 1)->get(),
            'categoriesSet' => $categoriesSet,
            'subkws' => $subkws,
            'mainkw' => $mainkw,
            'action_delete' => $action_delete,
            'additionCss' => $additionCss,
            'postChecksum' => $clientChecksum,
            'recoverChecksum' => $recoverChecksum,
            'draftUnsaved' => $draftUnsaved,
            'readOnly' => $readOnly
        ]);
    }

    public function postEdit(Request $request, $postId = 0) {
        if(empty($post = Post::find($postId)) || !$post->canEditBy($this->currentUser) ) {
            return '404 Not Found';
        }


        if($post->isLock($this->currentSession)) {
            $this->_flashMessage($request, 'このコンテンツは現在ロックされています。編集できません。', 'danger');
            return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
        }
        if($request->get('site_id') == ''){
            $this->_flashMessage($request, 'ターゲットサイトを選んでください', 'danger');
            return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
        }

        $postTitle = $request->get('post_title');
        $postContent = $request->get('post_content');
        $postDescription = trim($request->get('post_description'));

        $previousContent = $post->content;
        $post->title = !empty($postTitle) ? $postTitle : 'タイトルなし';
        $post->content = $postContent;
        $post->category = $request->get('category');
        $post->description = $postDescription;
        $post->feature_img = !empty($request->get('new_feature_img_url')) ? $request->get('new_feature_img_url') : $request->get('feature_img_url');

        $post->priority = (int)$request->get('post_priority', 0);
        $post->word_count = (int)$request->get('word_count', 0);

        $submitType = strpos($request->get('submit_type'),'下書き') !== false ? 'draft' : 'publish';

        if($submitType == 'publish' && $this->currentUser->is(User::CONTRIBUTOR)){
            return '404 Not Found';
        }

        $oldSiteId = $post->site_id;
        $oldTargetPostId = $post->target_post_id;
        $oldPostId = 0;
        if($post->site_id == 0 || $oldSiteId != $request->get('site_id')) {
            $post->site_id = $request->get('site_id');
        }

        try {
//            $action = ($submitType == 'publish' && $post->published_status == 0 && $post->target_post_id == 0) ? 'publish' : 'edit';
            if( ($oldSiteId != $request->get('site_id')) || $oldSiteId == 0
                    || ($post->published_status == 0 && $post->target_post_id == 0) ) {
                $action = 'publish';
            } else {
                $action = 'edit';
            }

            $post->published_status = ($submitType == 'publish' && $post->site_id > 0) ? 1 : 0;

            if(!$this->currentUser->is(User::CONTRIBUTOR)
                && $post->site_id > 0) {
                $apiUrl = Site::find($post->site_id)->api_url;

                  $returnData = (new CdsApiDriver($apiUrl, [
                    'content' => $postContent,
                    'title' => $postTitle,
                    'categories' => [$post->category],
                    'post_id' => $post->target_post_id,
                    'post_excerpt' => $postDescription,
                    'post_status' => $submitType,
                    'feature_img' => $post->feature_img,
                    'author_name' => !empty($author = User::find($post->user_id)) ? $author->username : '',
                    'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                    'old_post_id' => $oldPostId,
                    'priority' => $post->priority,
                  ]))->send($action)->getResult();
                  if($returnData->data->post_id == 0) {
                    throw new \Exception();
                  }
                  $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $postContent, $oldPostId);
                  $post->target_post_id = $returnData->data->post_id;
                  $apiUrlOldTarget = ($oldSiteId != 0) ? Site::find($oldSiteId)->api_url : null;

                  if($post->target_post_id != 0 && $oldSiteId != $post->site_id && $oldSiteId != 0 && $oldTargetPostId != 0) {
                    $resData = (new CdsApiDriver($apiUrlOldTarget, ['post_id' => $oldTargetPostId]))->send('delete')->getResult();
                    if($resData->data->post == 'ERROR') {
                        throw new \Exception();
                    }
                  }
            }

            $this->_flashMessage($request, $submitType == 'publish' ? '公開しました' : '下書きに保存しました', 'success');
        } catch(\Exception $e) {
            $post->content = Post::replaceIDButtonLink('__POSTID__', $postContent, $oldPostId);
            $post->target_post_id = 0;
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。' . $e->getMessage(), 'warning');
        }

        $post->save();
        $post->saveHistory();

        // Update Post meta and keywords
        // Main keyword
        PostMeta::deletePostMetaByPostId($post->id);
        $main = explode(',', $request->get('mainkw'));

        $uniqueMainKeyword = trim($main[0]);
        $mainKeyword = Keywords::getKeyword($uniqueMainKeyword, 1);

        if(empty($mainKeyword)) {//there is no keyword
            if(strlen($uniqueMainKeyword) > 0) {
                // Insert new main keyword
                $mainKeyword = new Keywords();
                $mainKeyword->keyword = $uniqueMainKeyword;
                $mainKeyword->type = 1;
                $mainKeyword->save();
            }
        }

        $postMetaMkw = PostMeta::getPostMeta($post->id, isset($mainKeyword->id) ? $mainKeyword->id : 0);
        if(empty($postMetaMkw) && isset($mainKeyword->id)) {
            // Insert new post-keyword relationship if not exist
            $postMetaMkw = new PostMeta();
            $postMetaMkw->post_id = $post->id;
            $postMetaMkw->keyword_id = $mainKeyword->id;
            $postMetaMkw->save();
        }

        // Sub keywords
        $subs = explode(',', $request->get('subkws'));
        foreach ($subs as $item) {
            $trimmedItem = trim($item);
            if(strlen($trimmedItem) == 0) {
                continue;
            }

            $subKeyword = Keywords::getKeyword($trimmedItem, 2);

            if(empty($subKeyword)) {
                // Insert new keyword
                $subKeyword = new Keywords();
                $subKeyword->keyword = $trimmedItem;
                $subKeyword->type = 2;
                $subKeyword->save();

                // Save post-keyword relationship
            }

            $postMetaSkw = PostMeta::getPostMeta($post->id, $subKeyword->id);
            if(empty($postMetaSkw)) {
                // Insert new post-keyword relationship if not exist
                $postMetaSkw = new PostMeta();
                $postMetaSkw->post_id = $post->id;
                $postMetaSkw->keyword_id = $subKeyword->id;
                $postMetaSkw->save();
            }


        }

        if(strpos($request->get('submit_type'),'公開') !== false || strpos($request->get('submit_type'),'下書き') !== false){
            return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
        }else if(strpos($request->get('submit_type'),'プレビュー') !== false){
            return redirect()->to(action('Webapp\\PostController@getPreviewIframe', $postId))->send();
        }

        return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
    }

    public function postEditAjax(Request $request, $postId) {
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId)) || !$post->canEditBy($this->currentUser) ) {
            $this->activityLog->setInfo('Not found');
            return ['error' => [
                'message' => '記事が見つかりません'
            ]];
        }

        if($post->isLock($this->currentSession)) {
            $this->activityLog->setInfo('Locked');
            return ['error' => [
                'message' => 'このコンテンツは現在ロックされています。編集できません。'
            ]];
        }

        $postChecksum = $request->get('checksum', 0);
        $postContent = $request->get('post_content');

        if(empty($postChecksum) && empty($postContent)) {
            PostHistory::saveFromSession($request->session(), $post);
            $this->activityLog->setInfo('Empty content');
            return ['error' => [
                'message' => '投稿内容は空です'
            ]];
        }

        $calculatedChecksum = Post::calculateChecksum($postContent);

        if($postChecksum != $calculatedChecksum) {
            PostHistory::saveFromSession($request->session(), $post);
            $this->activityLog->setInfo('Checksum failed');
            return ['error' => [
                'message' => 'サーバへ送信する内容にエラーがあります'
            ]];
        }

        $postTitle = $request->get('post_title');
        $postDescription = trim($request->get('post_description'));

        $oldTitle = $post->title;
        $post->title = !empty($postTitle) ? $postTitle : 'タイトルなし';
        $post->content = $postContent;
        $post->description = $postDescription;
        $post->feature_img = !empty($request->get('new_feature_img_url')) ? $request->get('new_feature_img_url') : $request->get('feature_img_url');

        $post->priority = (int)$request->get('post_priority', 0);
        $post->word_count = (int)$request->get('word_count', 0);

        $submitType = strpos($request->get('submit_type'),'下書き') !== false ? 'draft' : 'publish';

        $postAction = 'can'.ucfirst($submitType).'By';

        if($submitType == 'publish' && !$post->{$postAction}($this->currentUser)) {
            $this->_flashMessage($request, 'Action is not permitted', 'danger');
            $this->activityLog->setInfo("$submitType is not permitted");
            return redirect()->to($request->url());
        }

        $this->activityLog->addMeta('SubmitType', $submitType);
        $this->activityLog->addMeta('PreviousPublishedStatus', $post->published_status);

        $oldStatus = $post->published_status;

        $oldSiteId = $post->site_id;
        $this->activityLog->addMeta('PreviousSiteId', $oldSiteId);

        $oldTargetPostId = $post->target_post_id;
        $oldPostId = 0;

        $newCategory = $request->get('category');
        $newSiteId = $post->site_id;

        if($post->site_id == 0 || $oldSiteId != $request->get('site_id')) {
            $newSiteId = $request->get('site_id');
        }

        try {
            if($this->currentUser->is(User::CONTRIBUTOR)) {
                throw new BreakException();
            }

            if ($submitType == 'publish' && !Post::isUniquePostByTitle($post, ['site_id' => $newSiteId])) {
                throw new TitleDuplicatedException('このタイトルは存在しています。公開できないのでタイトルを変更してください。');
            }

            if ($oldSiteId != $newSiteId && $oldSiteId != 0 && $oldTargetPostId != 0) {
                $apiUrlOldTarget = ($oldSiteId != 0) ? Site::find($oldSiteId)->api_url : null;
                try {
                    (new CdsApiDriver($apiUrlOldTarget, ['post_id' => $oldTargetPostId]))->send('delete')->getResult();
                } catch (\Exception $e) {
                    if ($e->getMessage() != 'DELETE_POST_NOT_FOUND') {
                        $this->activityLog->setInfo('Delete old post on target site failed');
                        $this->activityLog->addMeta('ChangeTargetSiteFailed', "Delete old post on target site failed");
                        throw $e;
                    }
                    $this->activityLog->addMeta('Warning', "Target post id #{$oldTargetPostId} not found");
                }

                $post->target_post_id = 0;
            }

            if (($submitType == 'publish' || ($oldStatus == 1 && $submitType == 'draft'))
                && (!$this->currentUser->is(User::CONTRIBUTOR) && $post->site_id > 0)) {

                $action = ($oldSiteId != $newSiteId) || $oldSiteId == 0
                || ($post->published_status == 0 && $post->target_post_id == 0) ? 'publish' : 'edit';

                $apiUrl = Site::find($newSiteId)->api_url;

                $returnData = (new CdsApiDriver($apiUrl, [
                    'content' => $postContent,
                    'title' => $postTitle,
                    'categories' => [$newCategory],
                    'post_id' => $post->target_post_id,
                    'post_excerpt' => $postDescription,
                    'post_status' => $submitType,
                    'feature_img' => $post->feature_img,
                    'author_name' => !empty($author = User::find($post->user_id)) ? $author->username : '',
                    'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                    'old_post_id' => $oldPostId,
                    'priority' => $post->priority,
                ]))->send($action)->getResult();
                if ($returnData->data->post_id == 0) {
                    $this->activityLog->setInfo('Post to target site failed for unknown reason');
                    $this->activityLog->addMeta('FailedPostToTargetSite','unknown reason');
                    throw new \Exception();
                }
                $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $postContent, $oldPostId);
                $post->target_post_id = $returnData->data->post_id;
                $post->published_status = ($submitType == 'publish' && $post->site_id > 0) ? 1 : 0;
            }

            $post->site_id = $newSiteId;
            $post->category = $newCategory;
            $this->_flashMessage($request, $submitType == 'publish' ? '公開しました' : '下書きに保存しました', 'success');
            $this->activityLog->addMeta('SiteId', $post->site_id);
            $this->activityLog->addMeta('TargetPostId', $post->target_post_id);
            $this->activityLog->addMeta('Category', $post->category);

        } catch(BreakException $e) {
            $this->_flashMessage($request, $submitType == 'publish' ? '公開しました' : '下書きに保存しました', 'success');
        } catch(TitleDuplicatedException $e) {
            $this->_flashMessage($request, $e->getMessage(), 'danger');
            $this->activityLog->addMeta('Error', 'Title duplicated: '.$post->title);
            $post->title = $oldTitle;
            $post->site_id = $oldSiteId;
        } catch(\Exception $e) {
            $post->content = Post::replaceIDButtonLink('__POSTID__', $postContent, $oldPostId);
            if($e->getMessage() == 'POST_NOT_FOUND') {
                $post->target_post_id = 0;
                $post->published_status = 0;
            }
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。' . $e->getMessage(), 'warning');
            $this->activityLog->setInfo('Post to target site failed. Error: '.$e->getMessage());
            $this->activityLog->addMeta('FailedPostToTargetSite', $e->getMessage());
        }

        $post->save();
        $post->saveHistory();

        $this->activityLog->addMeta('NewPublishedStatus', $post->published_status);
        $this->activityLog->addMeta('NewSiteId', $post->site_id);

        // Update Post meta and keywords
        // Main keyword
        PostMeta::deletePostMetaByPostId($post->id);
        $main = explode(',', $request->get('mainkw'));

        $uniqueMainKeyword = trim($main[0]);
        $mainKeyword = Keywords::getKeyword($uniqueMainKeyword, 1);

        if(empty($mainKeyword)) {//there is no keyword
            if(strlen($uniqueMainKeyword) > 0) {
                // Insert new main keyword
                $mainKeyword = new Keywords();
                $mainKeyword->keyword = $uniqueMainKeyword;
                $mainKeyword->type = 1;
                $mainKeyword->save();
            }
        }

        $postMetaMkw = PostMeta::getPostMeta($post->id, isset($mainKeyword->id) ? $mainKeyword->id : 0);
        if(empty($postMetaMkw) && isset($mainKeyword->id)) {
            // Insert new post-keyword relationship if not exist
            $postMetaMkw = new PostMeta();
            $postMetaMkw->post_id = $post->id;
            $postMetaMkw->keyword_id = $mainKeyword->id;
            $postMetaMkw->save();
        }

        // Sub keywords
        $subs = explode(',', $request->get('subkws'));
        foreach ($subs as $item) {
            $trimmedItem = trim($item);
            if(strlen($trimmedItem) == 0) {
                continue;
            }

            $subKeyword = Keywords::getKeyword($trimmedItem, 2);

            if(empty($subKeyword)) {
                // Insert new keyword
                $subKeyword = new Keywords();
                $subKeyword->keyword = $trimmedItem;
                $subKeyword->type = 2;
                $subKeyword->save();

                // Save post-keyword relationship
            }

            $postMetaSkw = PostMeta::getPostMeta($post->id, $subKeyword->id);
            if(empty($postMetaSkw)) {
                // Insert new post-keyword relationship if not exist
                $postMetaSkw = new PostMeta();
                $postMetaSkw->post_id = $post->id;
                $postMetaSkw->keyword_id = $subKeyword->id;
                $postMetaSkw->save();
            }
        }

        return [
            'data' => [
                'id' => $post->id,
                'checksum' => $calculatedChecksum
            ],
            'next' => action('Webapp\\PostController@getEdit', $post->id)
        ];
    }

    public function getPublish(Request $request, $postId = 0) {
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId))
            || $this->currentUser->is(User::CONTRIBUTOR)) {
            $this->activityLog->setInfo('Not found');
            return '404 Not Found';
        }

        $this->activityLog->addMeta('PreviousPublishedStatus', $post->published_status);

        if(!$post->canPublishBy($this->currentUser)) {
            $this->_flashMessage($request, 'Publish is not permitted', 'danger');
            $this->activityLog->setInfo('Not permitted');
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        if($post->isLock($this->currentSession)) {
            $this->_flashMessage($request, 'このコンテンツは現在ロックされています。変更できません。', 'danger');
            $this->activityLog->setInfo('Locked');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }

        if($post->published_status == 1) {
            $this->_flashMessage($request, '更新しました。', 'warning');
            $this->activityLog->setInfo('Already published');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }

        $data = array(
            'site_id' => $post->site_id,
            'title' => $post->title,
            'desc' => $post->description,
            'thumb' => $post->feature_img,
            'mainKey' => empty(PostMeta::getMainKeywordByPostId($postId)) ? '' : $postId
        );

        $mess_error = Post::checkPostDataRequireField($data, true);

        if($mess_error != ''){
            $this->_flashMessage($request, $mess_error, 'danger');
            $this->activityLog->setInfo('Missing required field');
            return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
        }

        if($post->site_id == 0) {
            $this->_flashMessage($request, 'ターゲットサイトを選んでください', 'warning');
            $this->activityLog->setInfo('Missing target site');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getEdit', $postId))->send();
            else
                return;
        }

        if (!Post::isUniquePostByTitle($post)) {
            $this->_flashMessage($request, 'このタイトルは存在しています。公開できないのでタイトルを変更してください。', 'danger');
            $this->activityLog->addMeta('Error', 'Title duplicated: '.$post->title);
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }

        try {
            $apiUrl = Site::find($post->site_id)->api_url;
            $action = ($post->target_post_id == 0) ? 'publish' : 'edit';
            $returnData = (new CdsApiDriver($apiUrl, [
                'content' => $post->content,
                'title' => $post->title,
                'categories' => [$post->category],
                'post_id' => $post->target_post_id,
                'post_excerpt' => $post->description,
                'post_status' => 'publish',
                'feature_img' => $post->feature_img,
                'author_name' => $post->author_name,
                'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                'priority' => $post->priority,
            ]))->send($action)->getResult();
            $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $post->content, $post->target_post_id);
            $post->target_post_id = $returnData->data->post_id;
            $post->published_status = 1;
            $this->_flashMessage($request, 'OK', 'success');

            $this->activityLog->addMeta('TargetPostId', $post->target_post_id);
        } catch(\Exception $e) {
            $post->content = Post::replaceIDButtonLink('__POSTID__', $post->content, $post->target_post_id);
            $this->activityLog->setInfo('Error when reaching to target site');
            $this->activityLog->addMeta('FailedPostToTargetSite', 'error when reaching to target site');
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'warning');
        }

        $post->save();

        $this->activityLog->addMeta('NewPublishedStatus', $post->published_status);

        $next = action('Webapp\\PostController@getIndex');

        if(($from = $request->get('from', null) != null) && $from == 'preview') {
            $next = $post->getTargetSiteUrl();
            $this->_clearFlashMessage($request);
        }

        if($request->get('hasFilter', false) !== false) {
            return redirect()->back()->send();
        }

        if(!$request->get('selected_ids'))
        return redirect()->to($next)->send();
    }

    public function getDraft(Request $request, $postId = 0) {
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId))) {
            $this->activityLog->setInfo('Not found');
            return '404 Not Found';
        }

        if(!$post->canDraftBy($this->currentUser)) {
            $this->_flashMessage($request, 'Draft is not permitted', 'danger');
            $this->activityLog->setInfo('Not permitted');
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        if($post->isLock($this->currentSession)) {
            $this->_flashMessage($request, 'このコンテンツは現在ロックされています。下書きに保存できません。', 'danger');
            $this->activityLog->setInfo('Locked');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }
        if($post->site_id == ''){
            $this->_flashMessage($request, 'ターゲットサイトを選んでください', 'danger');
            $this->activityLog->setInfo('Missing target stie');
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        if($post->published_status == 0) {
            $this->_flashMessage($request, '下書きに保存しました。', 'warning');
            $this->activityLog->setInfo('Already draft');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }

        $this->activityLog->addMeta('PreviousPublishedStatus', $post->published_status);

        try {
            $apiUrl = Site::find($post->site_id)->api_url;
            if($post->target_post_id > 0) {
                $returnData = (new CdsApiDriver($apiUrl, [
                    'content' => $post->content,
                    'title' => $post->title,
                    'categories' => [$post->category],
                    'post_excerpt' => $post->description,
                    'post_id' => $post->target_post_id,
                    'post_status' => 'draft',
                    'feature_img' => $post->feature_img,
                    'author_name' => $post->author_name,
                    'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                    'priority' => 0
                ]))->send('edit')->getResult();
                $post->content = Post::replaceIDButtonLink($returnData->data->post_id, $post->content, $post->target_post_id);
                $post->target_post_id = $returnData->data->post_id;
            }

            $post->published_status = 0;
            $this->_flashMessage($request, 'OK', 'success');
        } catch(\Exception $e) {
            $post->content = Post::replaceIDButtonLink('__POSTID__', $post->content, $post->target_post_id);
            $this->activityLog->setInfo('Error when reaching to target site');
            $this->activityLog->addMeta('FailedPostToTargetSite', 'error when reaching to target site');
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'warning');
        }

        $post->save();

        if($request->get('hasFilter', false) !== false) {
            return redirect()->back()->send();
        }


        $this->activityLog->addMeta('NewPublishedStatus', $post->published_status);

        if(!$request->get('selected_ids'))
        return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
    }

    public function postRequestNewId() {
        $post = new Post();
        $post->user_id = $this->currentUser->id;
        $post->save();

        $this->activityLog->setPostId($post->id);

        return ['id' => $post->id];
    }

    public function postAutosave(Request $request) {
        $postId = $request->get('post_id', 0);
        $postContent = $request->get('post_content', '');
        $postTitle = $request->get('post_title', '');
        $postDescription = $request->get('post_description', '');
        $checksum = $request->get('checksum', 0);
        $word_count = (int)$request->get('word_count', 0);

        $autoSaveInterval = 4;

        if($postId == 0 && empty($postContent) && empty($postTitle) && empty($postDescription)) {
            return ['id' => 0];
        }

        if($request->session()->get('autoSave'.$postId) === null) {
            $request->session()->set('autoSave'.$postId, [
                'count' => 0,
                'currentContent' => null
            ]);
        }

        $autoSaver = $request->session()->get('autoSave'.$postId);

        $calculatedChecksum = Post::calculateChecksum($postContent);

        /** @var Post $post */
        $post = Post::find($postId);

        if(empty($post)) {
            return ['error' => [
                'message' => 'Post not found',
                'code' => 404
            ]];
        }

        $this->activityLog->setPostId($post->id);

        if(!$post->canEditBy($this->currentUser)) {
            $this->activityLog->addMeta('AutoSaveFalse', 'permission denied');
            return ['error' => [
                'message' => 'Post not found',
                'code' => 404
            ]];
        }

        // Lock detect
        if($post->isLock($this->currentSession)) {
            $this->activityLog->addMeta('AutoSaveFalse', 'expired session');
            return [
                'id' => $post->id,
                'status' => 'locked'
            ];
        }

        $post->content = $postContent;
        $post->title = $postTitle;
        $post->description = $postDescription;
        $post->word_count = $word_count;

        if(empty($checksum) && empty($postContent)) {
            PostHistory::saveFromSession($request->session(), $post);
            $this->activityLog->addMeta('AutoSaveFalse', 'post content is empty');
            return [
                'error' => [
                    'message' => 'Post content is empty',
                    'code' => 900
                ]
            ];
        }

        if(Post::calculateChecksum($postContent) != $checksum) {
            PostHistory::saveFromSession($request->session(), $post);
            $this->activityLog->addMeta('AutoSaveFalse', 'checksum failed');
            return [
                'error' => [
                    'message' => 'Save failed',
                    'trace' => 'Calculated checksum : '.$calculatedChecksum,
                    'code' => 900
                ]
            ];
        }

        $autoSaver['currentContent'] = $post->content;
        $autoSaver['count'] = (int)$autoSaver['count'] + 1;

        $request->session()->set('autoSave'.$postId, $autoSaver);

        $updatedTime = Carbon::now()->toDateTimeString();

        //$history = null;

        //if($autoSaver['count'] % PostHistory::AUTOSAVE_INTERVAL == 0) {
            $history = $post->saveHistory(PostHistory::STATUS_DRAFT);
            $updatedTime = $history->updated_at;
        //}

        if(!$post->isPublished()) {
            $post->save();
            $updatedTime = $post->updated_at;
        }

        $returnData = [
            "id" => $post->id,
            "time" => $updatedTime,
        ];

        if(env('APP_DEBUG', false)) {
            $returnData["debug"] = [
                "count" => $request->session()->get('autoSave'.$postId)['count'],
                "saved" => ($history != null)
            ];
        }

        return $returnData;
    }

    public function postStashChange(Request $request) {
        $postId = $request->get('post_id', 0);

        $post = Post::find($postId);

        if(empty($post) || ($post->user_id != $this->currentUser->id)) {
            return ['error' => 'Post not found'];
        }

        // Lock detect
        if($post->isLock($this->currentSession)) {
            return [
                'id' => $post->id,
                'status' => 'locked'
            ];
        }

        $post->updated_at = Carbon::now()->toDateTimeString();
        $post->save();

        PostHistory::saveFromSession($request->session(), $post);

        return ['id' => $post->id];
    }

    public function postBackupUri(Request $request) {
        $postId = $request->get('post_id');
        $metaData = $request->get('data');

        $post = Post::find($postId);

        if(empty($post) || ($post->user_id != $this->currentUser->id)) {
            return ['error' => [
                'code' => 404,
                'message' => 'Post not found'
            ]];
        }

        Backup::newBackup($post->id, $this->currentSession->session_key, $metaData);

        return ['id' => $post->id];
    }

    public function postLock(Request $request) {
        $postId = $request->get('post_id', 0);
        $postIdLog = $request->get('post_id_log', 0);

        $post = Post::find($postId);
        if(empty($post) || !$post->canEditBy($this->currentUser)) {
            $this->activityLog->setPostId($postIdLog);
            if(empty($post)) {
                $this->activityLog->addMeta('LockError', 'Post does not exist.');
            } else if(!$post->canEditBy($this->currentUser)) {
                $this->activityLog->addMeta('LockError', 'User ' . $this->currentUser->username . ' does not have permission to edit post!');
            }
            return [
                'error' => [
                    'message' => '記事が見つかりません',
                    'error' => 404
                ]
            ];
        }

        if($post->isLock($this->currentSession)) {
            return [
                'id' => $post->id,
                'status' => 'locked'
            ];
        }

        $post->lock($this->currentSession);

        return ["id" => $post->id];
    }
    
    public function postLoggingDevTool(Request $request) {
        $postId = $request->get('post_id', 0);
        $devToolStatus = trim($request->get('devtool'));
    
        $this->activityLog->setPostId($postId);
        $this->activityLog->addMeta('DevToolStatus', $devToolStatus);
    
        return [
            'post_id' => $postId,
            'devtool' => $devToolStatus
        ];
    }

    public function getPreview(Request $request, $postId = 0) {
        if (empty($post = Post::find($postId)) || ($post->user_id != $this->currentUser->id && !$this->currentUser->is('admin'))) {
            return 'Not found';
        }

        $view = 'webapp/posts/'.(($request->get('raw', null) === null) ? 'preview' : 'raw_preview');

        return view($view, [
            'post' => $post,
            'additionCss' => Site::find($post->site_id)->getAdditionCss(),
        ]);
    }

    public function getPreviewIframe(Request $request, $previewSession = 0) {
        if(($post = $request->session()->get('preview_'.$previewSession, null)) === null) {
            return 'Preview session not found';
        }
        try {
            $returnData = (new CdsApiDriver((Site::find($post->site_id)->api_url), [
                'content' => $post->content,
                'title' => $post->title,
                'categories' => [$post->category],
                'post_excerpt' => $post->description,
                'post_id' => $post->target_post_id,
                'post_status' => 'draft',
                'feature_img' => $post->feature_img,
                'author_name' => $post->author_name,
                'cback_url' => action('Webapp\\PostController@getEdit', $post->id),
                'priority' => 0
            ]))->send('preview')->getResult();

            $previewId = $returnData->data->post_id;
        } catch(\Exception $e) {
            return 'Can not preview now: '.$e->getMessage();
        }

        $previewPost = new Post();
        $previewPost->target_post_id = $previewId;
        $previewPost->site_id = $post->site_id;

        $previewUrl = $previewPost->getTargetSiteUrl();
        $previewUrl .=
            '&prvk='.Site::find($post->site_id)->getApiKey()
            .'&preview=true'
            .'&back='.action('Webapp\\PostController@getEdit', $post->id);

        return view('webapp/posts/preview_iframe', [
            'post' => $post,
            'previewUrl' => $previewUrl,
            'previewSession' => $previewSession,
            'isMobile' => $request->get('mobile', false)
        ]);
    }

    public function postPreviewIframe(Request $request) {
        $postTitle = $request->get('post_title');
        $postContent = $request->get('post_content_clone');
        $postDescription = $request->get('post_description');

        $post = new Post();
        $post->title = !empty($postTitle) ? $postTitle : 'タイトルなし';
        $post->content = !empty($postContent) ? $postContent : '&nbsp;';
        $post->site_id = $request->get('site_id');
        $post->category = $request->get('category');
        $post->description = !empty($postDescription) ? $postDescription : $postDescription = '&nbsp;';
        $post->feature_img = $request->get('feature_img_url', $request->get('new_feature_img_url', ''));

        if($request->get('read_only', false) !== false) {
            $post = Post::find($request->get('post_id', 0));
        }

        $previewSession = str_random(8);
        $request->session()->set('preview_'.$previewSession, $post);

        return redirect()->to(action('Webapp\\PostController@getPreviewIframe', $previewSession))->send();
    }

    public function postOverride(Request $request) {
        $postId = $request->get('post_id', 0);

        if(empty($post = Post::find($postId))) {
            return ['status' => false];
        }

        $post->lock($this->currentSession);

        return ['status' => true];
    }

    public function postUnlock(Request $request) {
        $postId = $request->get('post_id', 0);

        if(empty($post = Post::find($postId)) || $post->isLock($this->currentSession)) {
            return ['status' => false];
        }

        $post->freeLock($this->currentSession);

        return ['status' => true];
    }

    public function getDelete(Request $request, $postId = 0) {
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId))
          || !$this->currentUser->is(User::ADMIN)
          || !$post->canEditBy($this->currentUser)
          || ($post->site_id > 0 && Site::find($post->site_id)->status != 1) ) {

            $this->activityLog->setInfo('Not found');

            $this->_flashMessage($request, 'この記事を削除する権限がありません!.', 'danger');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }

        if($post->isLock($this->currentSession)) {
            $this->activityLog->setInfo('Locked');
            $this->_flashMessage($request, '記事が編集されていますので、削除出来ません。', 'warning');
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        if (($post->target_post_id > 0 || $post->target_post_id == 0) && $post->id != 0) {
            $site = Site::find($post->site_id);
            if($post->target_post_id != 0 && !empty($site)) {
                $apiUrl = $site->api_url;
                $resData = (new CdsApiDriver($apiUrl, ['post_id' => $post->target_post_id]))->send('delete')->getResult();
                if($resData->data->post == 'ERROR') {
                    $this->activityLog->addMeta('DeleteTargetSite', 'failed');
                    throw new \Exception();
                }
                $this->activityLog->addMeta('DeleteTargetSite', 'success');
            }

            $delpost = Post::where('id', $post->id)->delete();
            if($delpost === 1) {
                $this->_flashMessage($request, '削除に成功しました', 'success');
            } else {
                $this->_flashMessage($request, '削除に失敗しました。もう一度確認ください', 'danger');
            }
        }
        if($request->get('hasFilter', false) !== false) {
            return redirect()->back()->send();
        }

        if(!$request->get('selected_ids'))
        return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
    }

    public function postMassAction(Request $request) {
        if(array_search($action = $request->get('action'), ['publish', 'draft', 'delete', 'edit', 'writerDelete']) === false) {
            return '404 Not Found';
        }

        if($action == 'none') {
            return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
        }

        $this->activityLog->setAction('PostController@postMassAction>'.$action);

        $list_id = $request->get('selected_ids', []);

        $this->activityLog->setInfo(implode(',', $list_id));

        $controllerAction = 'get'.ucfirst($action);
        $key_insert = array();
        if($action == 'edit' && count($list_id)){
            $_main_key = explode(',', $request->get('mainkw'));
            $uniqueMainKeyword = trim($_main_key[0]);
            if($k = Keywords::getKeyword($uniqueMainKeyword, 1)){
                $key_insert[] = $k->id;
            }else if($uniqueMainKeyword != ''){
                $newKeyword = new Keywords();
                $newKeyword->keyword = $uniqueMainKeyword;
                $newKeyword->type = 1;
                $newKeyword->save();
                $key_insert[] = $newKeyword->id;
            }
            $_sub_key = explode(',', $request->get('subkws'));
            foreach ($_sub_key as $item){
                $item = trim($item);
                if($sub_k = Keywords::getKeyword($item, 2)){
                    $key_insert[] = $sub_k->id;
                }else if($item != ''){
                    $newSubKeyword = new Keywords();
                    $newSubKeyword->keyword = $item;
                    $newSubKeyword->type = 2;
                    $newSubKeyword->save();
                    $key_insert[] = $newSubKeyword->id;
                }
            }
        }
        set_time_limit(60);
        foreach( $list_id as $postId) {
            if($action == 'edit' && count($key_insert)){
                PostMeta::deletePostMetaByPostId($postId);
                $pre_data = array();
                foreach ($key_insert as $new_key){
                    $pre_data[] = array('post_id' => $postId, 'keyword_id' => $new_key);
                }
                PostMeta::insert($pre_data);
            }else{
                $this->activityLog = new Activity();
                $this->activityLog->setAction('PostController@'.$controllerAction);
                $this->activityLog->setSession($this->currentSession);
                $this->$controllerAction($request, $postId);
            }
        }
        $this->_flashMessage($request, '更新に成功しました', 'success');

        return redirect()->back();
    }

    public function getCopyPost(Request $request, $postId = 0){
        $this->activityLog->setPostId($postId);

        if(!empty($post = Post::find($postId))){
            if(!$post->canCopyBy($this->currentUser)) {
                $this->_flashMessage($request, 'Copy is not permitted', 'danger');
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            }

            if($post->site_id == ''){
                $this->_flashMessage($request, 'ターゲットサイトを選んでください', 'danger');
                $this->activityLog->setInfo('Missing target site');
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            }

            $new_post = $post->replicate();
            $new_post->published_status = 0;
            $new_post->lock_expired = '';
            $new_post->	lock_session = '';
            $new_post->id_post_old=$postId;
            $new_post->title = '【コピー】'.$new_post->title;
            $new_post->user_id = $this->currentUser->id;
            $new_post->save();

            $this->activityLog->addMeta('NewPostId', $new_post->id);

            try {
                if(!$this->currentUser->is(User::CONTRIBUTOR) && $post->site_id > 0) {
                    $apiUrl = Site::find($post->site_id)->api_url;
                    $returnData = (new CdsApiDriver($apiUrl, [
                        'content' => $new_post->content,
                        'title' => $new_post->title,
                        'categories' => [$new_post->category],
                        'post_excerpt' => $new_post->description,
                        'old_post_id' => $new_post->target_post_id,
                        'post_status' => 'draft',
                        'feature_img' => $new_post->feature_img,
                        'author_name' => !empty($author = User::find($new_post->user_id)) ? $author->username : '',
                        'cback_url' => action('Webapp\\PostController@getEdit', $new_post->id),
                        'priority' => $new_post->priority,
                    ]))->send('publish')->getResult();

                    if($returnData->data->post_id == 0) {
                        $this->activityLog->addMeta('DraftedStatus', 'failed');
                        throw new \Exception();
                    }
                    $new_post->content = Post::replaceIDButtonLink($returnData->data->post_id, $new_post->content, $new_post->target_post_id);
                    $new_post->target_post_id = $returnData->data->post_id;
                }

                $this->activityLog->addMeta('TargetPostId', $new_post->target_post_id);
                $this->_flashMessage($request, '下書きに保存しました', 'success');
            } catch(\Exception $e) {
                $new_post->content = Post::replaceIDButtonLink('__POSTID__', $new_post->content, $new_post->target_post_id);
                $new_post->target_post_id = 0;
                $this->activityLog->addMeta('DraftedStatus', 'failed');
                $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'warning');
            }
            $new_post->save();
            $pmt = new PostMeta();
            $pmt_data = $pmt->getPostMetaByPostId($post->id);
            foreach ($pmt_data as $pmt){
                $postMetaSkw = new PostMeta();
                $postMetaSkw->post_id = $new_post->id;
                $postMetaSkw->keyword_id = $pmt->kwid;
                $postMetaSkw->save();
            }
        }
        if($request->get('hasFilter', false) !== false) {
            return redirect()->back()->send();
        }
        return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
    }

    public function getWriterDelete(Request $request, $postId = 0){
        $this->activityLog->setPostId($postId);

        if(empty($post = Post::find($postId))
          || !$post->canEditBy($this->currentUser)
          || ($post->site_id > 0 && Site::find($post->site_id)->status != 1) ) {

            $this->activityLog->setInfo('Not found');
            $this->_flashMessage($request, 'この記事を削除する権限がありません!.', 'danger');
            if(!$request->get('selected_ids'))
                return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
            else
                return;
        }
        $post->is_delete = 1;
        $save = $post->save();
        if($save) {
            $this->_flashMessage($request, '削除に成功しました', 'success');
        } else {
            $this->_flashMessage($request, '削除に失敗しました。もう一度確認ください', 'danger');
        }
        return redirect()->to(action('Webapp\\PostController@getIndex'))->send();
    }

    public function postErrorLog(Request $request) {
        $postId = $request->get('post_id');
        $errorMessage = $request->get('error_msg');

        $this->activityLog->setPostId($postId);
        $this->activityLog->addMeta('AutoSaveFalse', $errorMessage);
    }
}