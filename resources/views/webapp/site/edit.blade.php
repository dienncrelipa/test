@extends('webapp/layout')

@section('addonScript')
    <link rel="stylesheet" href="{!! asset('res/css/jstree-themes/default/style.min.css') !!}"/>
    <script type="text/javascript" src="{!! asset('res/js/jstree.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('res/js/jstree.realcheckbox.js?v=20170705') !!}"></script>
@endsection

@section('mainContent')
    <div class="row">
        @include('webapp/components/notification')
    </div>
    <div class="row">
        <form method="post" role="form">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="site_name">サイト名</label>
                <input type="text" class="form-control" id="site_name" name="site_name" placeholder="サイト名" value="{{ $site->name }}">
            </div>
            <div class="form-group">
                <label for="site_url">サイトURL</label>
                <input type="text" class="form-control" id="site_url" name="site_url" placeholder="サイトURL" value="{{ $site->site_url }}">
            </div>
            <div class="form-group">
                <label for="site_api">サイトAPI URL</label>
                <input type="text" class="form-control" id="site_api" disabled placeholder="サイトAPI URL" value="{{ $site->api_url }}">
            </div>
            <div class="form-group">
                <input type="hidden" id="cmsTargetSiteId" value="{!! $site->id !!}" />
            </div>

            <div class="cate-row">
                <div class="form-group">
                    <label>カテゴリ</label>
                    <div class="input-group">
                        <input class="form-control" type="text" id="categoryTreeView_s" placeholder="カテゴリー検索" value=""/>
                        <div class="input-group-addon" id="cmsCategorySync">
                            <span class="glyphicon glyphicon-refresh"></span>
                        </div>
                    </div>
                </div>
                <div class="form-group category-tree-wrapper">
                    <div class="loading">
                        <div class="loader loader--style1" title="0">
                            <span class="glyphicon glyphicon-refresh"></span>
                        </div>
                    </div>
                    <div class="wrap">
                        <div id="categoryTreeView">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">送信</button>
        </form>
    </div>
    <script type="text/javascript">
        function initCategoryView(site_id) {
            $.get('/webapp/category/list-categories?id=' + site_id, function (data) {
                $('.loading').hide();
                $('#categoryTreeView').jstree('destroy').on("ready.jstree", function () {
                    $('#categoryTreeView').jstree('open_all');
                }).jstree({
                    "types": {
                        "default": {
                            "icon": "glyphicon glyphicon-tasks"
                        }
                    },
                    "search": {
                        "case_insensitive": true,
                        "show_only_matches": true
                    },
                    "plugins": ["themes", "json_data", "ui", "types", "search"],
                    'core': {
                        "multiple" : false,
                        "expand_selected_onload": true,
                        "themes": {
                            "variant": "large",
                            "icons": false,
                            "dots": false,
                        },
                        "animation": 0,
                        'data': data
                    }
                });

            });

            var to = false;
            $('#categoryTreeView_s').keyup(function () {
                if (to) {
                    clearTimeout(to);
                }
                to = setTimeout(function () {
                    var kw = $('#categoryTreeView_s').val();
                    $('#categoryTreeView').jstree('search', kw);
                }, 250);
            });

        }

        $(document).ready(function () {
            initCategoryView($('#cmsTargetSiteId').val())

            $('#cmsCategorySync').click(function () {
                $(this).addClass('spin');
                $('.loading').show();
                $.get('/webapp/category/category-sync?id=' + $('#cmsTargetSiteId').val(), function (data) {
                    if (data.code == 200) {
                        $('#cmsCategorySync').removeClass('spin');
                        initCategoryView($('#cmsTargetSiteId').val());
                    }
                }).fail(function (xhr, status, error) {
                    alert("An AJAX error occured: " + status + "\nError: " + error);
                });
                ;
            });
        });
    </script>
@endsection