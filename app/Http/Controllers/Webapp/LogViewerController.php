<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/15/17
 * Time: 10:28 AM
 */

namespace App\Http\Controllers\Webapp;


use App\Libs\ColdValidator;
use App\Models\Activity;
use App\Models\ActivityNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mockery\Matcher\Type;
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

class LogViewerController extends NeedAuthController
{
    public function __construct() {
        parent::__construct();
        if( $this->currentUser->username !== 'relipa_admin') {
            exit('401 Unauthorized!');
        }
        
        if($this->currentUser->is('admin') === false) {
            exit('404 Not Found');
        }
    }

    public function getIndex(Request $request) {
        $query = DB::getDoctrineConnection()
            ->createQueryBuilder()
            ->select([
                'ac_no.id',
                'ac_no.message',
                'u.username',
                'ss.ip_address',
                'ac_log.created_at as time'
            ])
            ->from('activity_notification', 'ac_no')
            ->leftJoin('ac_no','activity_log', 'ac_log', 'ac_no.activity_id = ac_log.id')
            ->leftJoin('ac_log','sessions', 'ss', 'ac_log.session_id = ss.id')
            ->leftJoin('ss','users', 'u', 'ss.user_id = u.id');

        $cfg = (new GridConfig())
            ->setName('filter-log')
            ->setDataProvider(
                new DbalDataProvider($query)
            )
            ->setColumns([
                (new FieldConfig)
                    ->setName('id')
                    ->setLabel('ID')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC),
                (new FieldConfig)
                    ->setName('message')
                    ->setLabel('Message')
                    ->addFilter(
                        (new SelectFilterConfig())
                            ->setName('post-id')
                            ->setOperator(FilterConfig::OPERATOR_EQ)
                            ->setDefaultValue(null)
                            ->setTemplate('webapp.components.nayjest_columns_filter')
                            ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                                $tmp_m = DB::table('activity_log')->find($val);
                                return $dap->getBuilder()->andWhere("ac_log.post_id = ".$tmp_m->post_id);
                            })
                    )
                    ->addFilter(
                        (new SelectFilterConfig())
                            ->setName('type')
                            ->setOperator(FilterConfig::OPERATOR_EQ)
                            ->setTemplate('webapp.components.filter_activity_type')
                            ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                                $filterType = [
                                    'published' => 1,
                                    'draft' => 0
                                ];

                                $type = $val;
                                if ($type == 'published' || $type == 'draft')
                                {
                                    return $dap->getBuilder()->leftJoin('ac_no', 'activity_metadata', 'am1', 'ac_no.activity_id = am1.activity_id AND am1.key = \'PreviousPublishedStatus\'')
                                        ->leftJoin('ac_no', 'activity_metadata', 'am2', 'ac_no.activity_id = am2.activity_id AND am2.key = \'NewPublishedStatus\'')
                                        ->andWhere('am1.value = '. (1-$filterType[$type]), 'am2.value ='.$filterType[$type]);
                                }
                                else if ($type == 'copy')
                                {
                                    return $dap->getBuilder()->andWhere('ac_log.action = "PostController@getCopyPost"');
                                }
                                else if ($type == 'changeSite')
                                {
                                    return $dap->getBuilder()->leftJoin('ac_no', 'activity_metadata', 'am1', 'ac_no.activity_id = am1.activity_id AND am1.key = \'PreviousSiteId\'')
                                        ->leftJoin('ac_no', 'activity_metadata', 'am2', 'ac_no.activity_id = am2.activity_id AND am2.key = \'NewSiteId\'')
                                        ->andWhere('am1.value <> am2.value');
                                }
                                else if ($type == 'autoSaveFailed')
                                {
                                    return $dap->getBuilder()->leftJoin('ac_no', 'activity_metadata', 'am1', 'ac_no.activity_id = am1.activity_id')
                                        ->andWhere('am1.key = "AutoSaveFalse"');
                                }
                                else if ($type == 'PostToTargetSiteFailed')
                                {
                                    return $dap->getBuilder()->leftJoin('ac_no', 'activity_metadata', 'am1', 'ac_no.activity_id = am1.activity_id')
                                        ->andWhere('am1.key = "FailedPostToTargetSite"');
                                }
                                return $dap->getBuilder();
                            })
                    )
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC)
                    ->setCallback(function($val, ObjectDataRow $row) {
//                        dd($row);
                        $val = preg_replace('/#([0-9]+)/', "<a href='".action('Webapp\\PostController@getEdit')."/$1'>&nbsp;$0&nbsp;</a>", $val);
                        $val = str_replace("draft", "<span style='color: red'>&nbsp;draft&nbsp;</span>", $val);
                        $val = str_replace("published", "<span style='color: green'>&nbsp;published&nbsp;</span>", $val);
                        $val = preg_replace('/`([^`]+)`/', '<b>&nbsp;$1&nbsp;</b>', $val);
                        return $val;
                    }),
                (new FieldConfig)
                    ->setName('username')
                    ->setLabel('Username')
                    ->addFilter(
                        (new SelectFilterConfig())
                            ->setName('username')
                            ->setOperator(FilterConfig::OPERATOR_EQ)
                            ->setDefaultValue(null)
                            ->setTemplate('webapp.components.nayjest_columns_filter')
                            ->setFilteringFunc(function($val, DbalDataProvider $dap) {
                                $tmp_u = User::find($val);
                                return $dap->getBuilder()->andWhere("u.username = '$tmp_u->username'");
                            })
                    )
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC),
                (new FieldConfig)
                    ->setName('ip_address')
                    ->setLabel('IP Address')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC),
                (new FieldConfig)
                    ->setName('time')
                    ->setLabel('Time')
                    ->setSortable(true)
                    ->setSorting(Grid::SORT_DESC)
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
                                        'class' => 'btn btn-success btn-sm filter-log'
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
        $grid = $grid->render();
        return view('webapp/log_viewer/index', [
            'grid' => $grid,
            'is_filter' => count(array_column($request->all(), 'filters')) ? true : false,
        ]);
    }
}