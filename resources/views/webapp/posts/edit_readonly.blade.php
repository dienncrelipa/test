@extends('webapp/layout')

@section('addonScript')
    <script type='text/javascript' src='{{ asset('res/redactor/video.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/twitter.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/product.js') }}?v=20180314'></script>
    <script src='{{ asset('res/js/jquery-ui.min.js') }}' type="text/javascript" charset="utf-8"></script>
    <script type='text/javascript' src='{{ asset('res/js/tag-it.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/relipa.redactor.js') }}?v=20180123'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.confirm.min.js') }}'></script>
    <script type="text/javascript" src="{{ asset('res/checksum/checksum.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('res/js/store.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('res/js/pastebin.js') }}"></script>
    <style>{{ $additionCss }}</style>
    <style>
        .word-count {
            float: left;
            margin-right: 20px;
        }
    </style>
    <link rel="stylesheet" href="{!! asset('res/css/jstree-themes/default/style.min.css') !!}"/>
    <script type="text/javascript" src="{!! asset('res/js/jstree.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('res/js/jstree.realcheckbox.js') !!}"></script>
@stop

@section('mainContent')
    @include('webapp/components/notification')
    <script type="text/javascript">
        currentPostId = {{ $post->id }};
        lockStatus = {!! $lockStatus ? 'true' : 'false' !!};
        changeStatus = false;
        globalLockInterval = null;
        NO_SCRIPT = "{{ env('NO_SCRIPT', true) }}";

        hasher = function() { return new Checksum("crc32") };

        function applyButtonsStylesheet(siteId) {
            if(typeof  siteId === 'undefined' || siteId === '') return;
            $.get('/ajax/get-button-css?site_id='+siteId, function(data){
                if(data.error !== undefined) {
                    alert(data.error);
                    return;
                } else if(data.data !== undefined) {
                    $('head link[rel=stylesheet]').each(function (index, value) {
                        var stylesheetUrl = value.href;
                        if (stylesheetUrl.search('css/buttons.css') > 0) {
                            value.remove();
                            $('head').append('<link rel="stylesheet" type="text/css" href="' + data.data + '?v=20170118"/>');
                        }
                    });
                }
            });
        }

        function changeSite(siteId) {
            $('select[name="category"]').hide().attr('disabled', 'disabled');
            $('select.category-list[data-category="'+siteId+'"]').show().removeAttr('disabled');
        }

        $(function()
        {
            $('#content').redactor({
                buttons: ['bold', 'lists', 'image', 'link'],
//            focus: true,
                imageUpload: '/ajax/image',
                plugins: ['source', 'video', 'alignment', 'counter', 'blockFormat', 'table', 'inlinestyle', 'chatbox', 'twitter', 'product','iconic2'],
                callbacks: {
                    counter: function(data)
                    {
                        var word_count = data.counterNumberOfEditor + parseInt($('#description-word-count').html());
                        $('input[name=word_count]').val(word_count);
                        $('span#word-count').html('' + word_count + '');
                        $('span#total-count').html(data.counterNumber + parseInt($('#description-word-count').html()) + parseInt($('#title-word-count').html()));
                    },
                    change: function () {
                        changeStatus = true;
                        autoSaveChange = true;

                        if($('h2.rank').length != 0) {
                            $('h2.rank').each(function() {
                                if($(this).text() == '') {
                                    $(this).html("<a href=\"#\">&nbsp;</a>");
                                }
                            });
                        }
                        if($('h3.rank').length != 0) {
                            $('h3.rank').each(function() {
                                if($(this).text() == '') {
                                    $(this).html("<a href=\"#\">&nbsp;</a>");
                                }
                            });
                        }
                        window.instanceRedactor.counter.count();
                    },
                    paste: function(html){
                        var $new_html = $('<div>').append(html);
                        $new_html.find('a').each(function(i, elm){
                            $(elm).addClass('btn-index');
                        });
                        return $new_html.html();
                    }
                },
                shortcutsAdd: createShortcut.createListShortCutForRedactor()
            });

            changeSite($('select[name="site_id"]').val());
            $('#title-word-count').html($.trim($('input[name="post_title"]').val().length));
            $('#description-word-count').html($.trim($('textarea[name="post_description"]').val()).length);
            window.instanceRedactor.counter.count();

            $('#preview-btn').click(function(){
                $('form')
                    .attr('action', '{{ action('Webapp\\PostController@postPreviewIframe') }}')
                    .attr('target', '_blank');
            });
        });
    </script>
    @include('webapp/components/notification')
    <div id="dialog-confirm" title="ロックされているコンテンツ" style="display: none;">
        <p>このコンテンツは現在ロックされています。 編集を引き継ぐと、他の人は編集を続けられなくなります。</p>
    </div>
    <div id="dialog-try-again" title="エラー" style="display: none">
        <p>警告：自動保存に失敗しました。管理者までこのURL：<b><a id="backup-url" href="#"></a></b> を送ってください。その後、アラートを閉じて記事内容をコピーし、ローカルに保存してください。</p>
    </div>
    <div id="dialog-autosave" title="エラー" style="display: none;">
        <p></p>
    </div>
    <div class="row">
        <form method="post" autocomplete="off">
            {{ csrf_field() }}
            <input type="hidden" name="read_only" value="true" />
            <input type="hidden" name="post_id" value="{{ $post->id }}" />
            <input type="hidden" name="checksum" value="{{ $postChecksum }}" />
            <input type="hidden" name="word_count" value="0" />
            <div class="form-group">
                <label>ターゲットサイト</label>
                <select name="site_id" class="form-control" id="cmsTargetSitesList" disabled>
                    <option value="">サイトを選んでください</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->id }}"@if($post->site_id == $site->id) selected="selected"@endif site-url="{{ $site->site_url }}">{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group hide">
                <label>カテゴリ</label>
                <select name="category" class="form-control">
                    <option value="">None</option>
                </select>
                @foreach($categoriesSet as $siteId => $categories)
                    <select name="category" class="form-control category-list" data-category="{{ $siteId }}" style="display: none;">
                        <option value="">None</option>
                        @foreach($categories as $category)
                            <option id-val="{!! $category->id !!}" value="{{ $category->slug }}"@if($post->category == $category->slug)selected="selected"@endif>{{ $category->name }}</option>
                        @endforeach
                    </select>
                @endforeach
            </div>

            <div class="cate-row">
                <div class="form-group">
                    <label>カテゴリ: <span id="currentCategory"></span>
                        <a href="javascript:void(0)" id="changeCategory" class="fa fa-chevron-down"></a>
                    </label>
                </div>

                <div class="category-section" id="categorySection">
                    <div class="form-group">
                        <input class="form-control" type="text" id="categoryTreeView_s" placeholder="カテゴリー検索" value=""/>
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
            </div>
            @if($post->target_post_id > 0)
                <div class="form-group">
                    <label>記事リンク</label>
                    <a href="{{ $post->site->site_url.'?p='.$post->target_post_id }}" target="_blank">{{ $post->site->site_url.'?p='.$post->target_post_id }}</a>
                </div>
            @endif
            <div class="form-group">
                <label>ステータス</label>
                <p>{!! ($post->published_status == 1) ? '<span class="label label-success">公開済み</span>' : '<span class="label label-default">下書き</span>' !!}</p>
            </div>
            <div class="form-group">
                <label>タイトル</label>
                <input type="text" class="form-control" name="post_title" value="{{ $post->title }}" autocomplete="off" placeholder="（作業シートで指定されているものを入れてください）" disabled/>
                <label class="word-count">文字カウント：<span id="title-word-count">0</span></label>
                <div class="clearfix"></div>
            </div>
            <div class="form-group">
                <label>アイキャッチ画像</label>
                <div class="" id="feature-img-display">
                    <img id="current_feature_img_url" src="{!! $post->feature_img !!}" title="{!! $post->title !!}" alt="{!! $post->post_excerpt !!}" width="200" />
                </div>
            </div>
            <div class="form-group">
                <label>リード文</label>
                <textarea id="post-description" class="input-border" placeholder="リード文（キーワードは必ず１文目に入れる。タイトル下に表示される重要な部分）" name="post_description" autocomplete="off" disabled>{{ $post->description }}</textarea>
                <label class="word-count">文字カウント：<span id="description-word-count">0</span></label>
                <div class="clearfix"></div>
            </div>
            {{-- Keyword wrapper --}}
            <div class="form-group">
                <label>キーワード</label>
                {{-- Main keywords --}}
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="メインキーワード" id="main_kw_tagit" name="mainkw" value="{!! $mainkw !!}" />
                </div>
                {{-- Sub keywords --}}
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="サブキーワード" id="sub_kw_tagit" name="subkws" value="{!! $subkws !!}" />
                </div>
            </div>
            {{-- End of keyword --}}
            <div class="form-group" id="recover-dialog" style="display: none;">
                <div class="alert alert-warning" id="recover-dialog-recover">
                    <b>保存されていない変更があります。 <a href="javascript:;" id="recover-btn">復旧します</a></b>
                </div>
                <div class="alert alert-success" id="recover-dialog-success" style="display: none;">
                    <b>復旧に成功しました。 <a href="javascript:;" id="undo-btn">元に戻す</a></b>
                </div>
            </div>
            <div class="form-group">
                <label>コンテンツ</label>
                <textarea id="content" name="post_content" rows="6" autocomplete="off" placeholder="1つ目の見出し(H2)は導入部分。必ず画像とテキストをセットで作成ください。" disabled>{!! $post->content !!}</textarea>
                <div class="form-inline">
                    <label class="word-count">文字カウント：<span id="word-count">0</span></label>
                    <label class="word-count">総文字数：<span id="total-count" counter="0">0</span></label>
                    <label class="word-count" id="last-saved-noti" style="float: right;"></label>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="form-group group-fixed">
                <div class="button-fixed new_post_sbm_fixed">
                    <a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::POST_CREATE) }}"
                       class="btn btn-default new_post_sbm">新規記事</a>
                    <span class="tooltip">新規記事</span>

                </div>
                <div class="button-fixed preview_sbm_fixed">
                    <input type="submit" class="btn btn-info preview_sbm" id="preview-btn"
                           value="プレビュー" name="submit_type" />
                    <span class="tooltip">プレビュー</span>
                </div>
            </div>
        </form>
    </div>

    <script>
        function initCategoryView(site_id) {
            if(typeof  site_id === 'undefined' || site_id === '') { $('div.cate-row').hide(); return; }
            $('div.cate-row').show();
            $('.loading').show();
            $.get('/webapp/category/list-categories?id=' + site_id, function (data) {
                $('.loading').hide();
                $('#categoryTreeView').jstree('destroy').on('loaded.jstree, ready.jstree', function(e, data) {
                    $('#categoryTreeView').jstree('open_all');
                    var nodeId = $('select.category-list:not([disabled="disabled"])').find('option[selected="selected"]').attr('id-val');

                    if(nodeId != undefined) {
                        $("#categoryTreeView").jstree("select_node", "#" + nodeId);
                        $('#categoryTreeView').jstree('active_node', nodeId);
                        $('#currentCategory').text($('#' + nodeId + '_anchor').text()).attr('scId', nodeId);

                        $('#categorySection').hide();
                        $('select.category-list:not([disabled="disabled"])').val($('select.category-list:not([disabled="disabled"])').find('option[id-val="' + nodeId + '"]').val());
                    }
                }).on("changed.jstree, select_node.jstree", function (e, data) {
                    $('select.category-list:not([disabled="disabled"])').find('option').removeAttr('selected');
                    $('select.category-list:not([disabled="disabled"])').find('option[id-val="' + data.selected + '"]').attr('selected', 'selected');
                }).on("deselect_node.jstree", function(e, data) {
                    $('select.category-list:not([disabled="disabled"])').find('option').removeAttr('selected');
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
                    'checkbox': {
                        "three_state": false,
                        "cascade": 'none'
                    },
                    "plugins": ["themes", "json_data", "ui", "types", "search", "checkbox", "realcheckboxes"],
                    'core': {
                        "check_callback": true,
                        "multiple" : false,
                        "expand_selected_onload": true,
                        "themes": {
                            "variant": "large",
                            "icons": false,
                            "dots": false
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
            $('#changeCategory').click(function() {
                $('#categorySection').slideToggle('200');
                $(this).toggleClass('expand');
                var container = $('.category-tree-wrapper .wrap');
                var scrollTo = $('#' + $('#currentCategory').attr('scId'));

                container.animate({
                    scrollTop: scrollTo.offset().top - container.offset().top + container.scrollTop()
                });
            });

            $('#feature_img_url').val($('#current_feature_img_url').attr('src'));

            (function($) {
                if (!$.curCSS) {
                    $.curCSS = $.css;
                }
            })(jQuery);

            $('#main_kw_tagit').tagit({
                allowDuplicates: false,
                readOnly: true,
                allowSpaces: true,
                singleFieldDelimiter: ',',
                placeholderText: 'メインキーワード',
                tagLimit: 1,
            });
            $('#sub_kw_tagit').tagit({
                allowDuplicates: false,
                readOnly: true,
                allowSpaces: true,
                singleFieldDelimiter: ',',
                placeholderText: 'サブキーワード',
            });

            var siteId = $('select[name=site_id] option[selected="selected"]').val();
            applyButtonsStylesheet(siteId);

            initCategoryView($('#cmsTargetSitesList').val())

            $('#cmsTargetSitesList').change(function () {
                initCategoryView($(this).val());
            });
        });
    </script>
@endsection
