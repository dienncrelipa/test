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
    <script type="text/javascript" src="{{ asset('res/js/pastebin.js') }}"></script>
    <style>{{ $additionCss }}</style>
    <style>
        .word-count {
            float: left;
            margin-right: 20px;
        }
    </style>
    <link rel="stylesheet" href="{!! asset('res/css/jstree-themes/default/style.min.css') !!}"/>
    <link rel="stylesheet" href="{{ asset('res/css/207_245_257_product_box.css') }}">
    <script type="text/javascript" src="{!! asset('res/js/jstree.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('res/js/jstree.realcheckbox.js?v=20170705') !!}"></script>
@stop

@section('mainContent')
@include('webapp/components/notification')
<script type="text/javascript">
    $.xhrPool = [];
    $.xhrPool.abortAll = function() {
        $(this).each(function(i, jqXHR) {   //  cycle through list of recorded connection
            jqXHR.abort();  //  aborts connection
            $.xhrPool.splice(i, 1); //  removes from list by index
        });
    };
    $.ajaxSetup({
        beforeSend: function(jqXHR) { $.xhrPool.push(jqXHR); }, //  annd connection to list
        complete: function(jqXHR) {
            var i = $.xhrPool.indexOf(jqXHR);   //  get index for current connection completed
            if (i > -1) $.xhrPool.splice(i, 1); //  removes from list by index
        }
    });
    $.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };
</script>
<script type="text/javascript">
    currentPostIdLog = {!! $post->id !!};
    currentPostId = {{ $post->id }};
    lockStatus = {!! $lockStatus ? 'true' : 'false' !!};
    changeStatus = false;
    globalLockInterval = null;
    NO_SCRIPT = "{{ env('NO_SCRIPT', true) }}";

    hasher = function() { return new Checksum("crc32") };

    function changeSite(siteId) {
        $('select[name="category"]').hide().attr('disabled', 'disabled');
        $('select.category-list[data-category="'+siteId+'"]').show().removeAttr('disabled');
    }

    function uiLockPost() {
        $('input[type="submit"]').attr('disabled', 'disabled');
    }

    function uiUnlockPost() {
        $('input[type="submit"]').removeAttr('disabled');
    }

    function lockStateHandle(localState, remoteState, data) {
        if(localState == true) {
            localState = 1;
        } else {
            localState = 0;
        }

        if(remoteState == true) {
            remoteState = 3;
        } else {
            remoteState = 1;
        }

        totalState = localState + remoteState;

        if(totalState == 2) {
            alert("Post has been unlocked. Now you can edit post");
            window.location.href = '{{ action('Webapp\\PostController@getEdit', $post->id) }}';
            uiUnlockPost();
        }

        if(totalState == 3) {
            alert("他の人が引き継いで現在編集しています。OKをクリックすると一覧へ戻ります");
            $('input[type="submit"]').attr('disabled', 'disabled');
            uiLockPost();
            changeStatus = false;
            window.location.href = '{{ action('Webapp\\PostController@getIndex') }}';
        }

    }

    function getContentDetail(getByHtml) {
        var detail = {};
        if(!getByHtml || getByHtml == undefined) {
            detail.content = $('#content').redactor('code.get').replace(/background-hover/g,'');
        } else {
            detail.content = $('.redactor-editor').html().replace(/background-hover/g,'');
        }
        detail.checksum = hasher().updateStringly(detail.content).result.toString(16);
        return detail;
    }

    $(function()
    {
        $('#content').redactor({
            buttons: ['bold', 'lists', 'image', 'link'],
            paragraphize: false,
//            focus: true,
            imageUpload: '/ajax/image',
            plugins: ['source', 'video', 'alignment', 'counter', 'blockFormat', 'table', 'inlinestyle', 'chatbox', 'twitter', 'product','iconic2', 'htmlBox'],
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
        // Check alert tag script in posts
        checkHtmlViewMode();

        autoSaveTriggered = false;
        autoSaveFailed = false;
        autoSaveChange = false;

        function autoSave() {
            if(autoSaveTriggered || autoSaveChange === false) {
                return;
            }
            autoSaveTriggered = true;
            var contentDetail = getContentDetail(true);

            autoSaveChange = false;
            $.post('{{ action('Webapp\\PostController@postAutosave') }}', {
                '_token': '{{ csrf_token() }}',
                'post_id' : currentPostId,
                'post_title' : $('input[name="post_title"]').val(),
                'post_description' : $('textarea[name="post_description"]').val(),
                'post_content' : contentDetail.content,
                'checksum' : contentDetail.checksum,
                'word_count' : $('input[name=word_count]').val()
            }, function(data) {
                if(data.error !== undefined) {
//                    tryAgainSave();
                    return;
                }

                var utcTime = moment.utc(data.time.date);
                $('#last-saved-noti').html('記事が '+utcTime.local().format("YYYY年MM月DD日, h:mm:ss a")+' に自動保存した。');
            })
            .error(function(){
//                tryAgainSave();
            })
            .always(function(){
                autoSaveTriggered = false;
            });
        }

        var lockInterval = false;

        var interval = setInterval(function(){
            autoSave();

            if(lockInterval) {
                return;
            }
            lockInterval = true;

            $.post('{{ action('Webapp\\PostController@postLock') }}',
                {
                    '_token': '{{ csrf_token() }}',
                    'post_id' : currentPostId,
                    'post_id_log': currentPostIdLog,
                },
            function(data){
                if(data.error !== undefined) {
                    alert(data.error.message);
                    window.location.href = '{{ action('Webapp\\PostController@getIndex') }}';
                    return;
                }
                currentPostId = data.id;

                var remoteStatus = false;

                if(data.status == 'locked') {
                    remoteStatus = true;
                }

                lockStateHandle(lockStatus, remoteStatus, data);
                lockStatus = remoteStatus;

                lockInterval = false;
            });
        }, SE_MINUTE_AUTO_SAVE_LOG);

        // Init dev tool variable, HTML element
        var checkDevToolStatus;
        devToolCurrentStatus = 'off';
        var element = new Image();
        element.__defineGetter__('id', function() {
            checkDevToolStatus = 'on';
        });

        setInterval(function() {
            // Check dev tool
            checkDevToolStatus = 'off';
            console.log(element);
            console.clear();

            if(checkDevToolStatus != devToolCurrentStatus) {
                $.post('{!! action('Webapp\\PostController@postLoggingDevTool') !!}', {
                    '_token': '{{ csrf_token() }}',
                    'post_id': currentPostIdLog,
                    'devtool': checkDevToolStatus
                }, function(data) {
                    alert('Developer tool is changed to ' + data.devtool);
                });
            }

            devToolCurrentStatus = checkDevToolStatus;
        }, 1000);

        globalLockInterval = interval;

        function tryAgainSave() {
            autoSaveFailed = true;
            uiLockPost();
            PasteBin.paste(null, function(callbackData){
                clearInterval(interval);
                tryAgainModal(callbackData.uri);
                $.post('{{ action('Webapp\\PostController@postBackupUri') }}', {
                    'post_id': currentPostId,
                    'data': callbackData.uri,
                    '_token': '{{ csrf_token() }}'
                });
            }, function() {
                clearInterval(interval);
                tryAgainModal(null);
            });
        }

        tryAgainModal = function(backupUrl) {
            $('#backup-url').attr('href', backupUrl).html(backupUrl);
            $("#dialog-try-again").dialog({
                resizable: false,
                height: "auto",
                draggable: false,
                width: 600,
                modal: true,
                open: function(event, ui) {},
                create: function (event) {
                    $(event.target).parent().css({ 'position': 'fixed', "top": 150 });
                }
            });
        };

        var this_select = $('#cmsTargetSitesList');
        var currentSelectSiteId = this_select.val();

        this_select.change(function(){
            if(this_select.val() !== currentSelectSiteId ) {

                if (currentSelectSiteId === '' || currentSelectSiteId === null)
                {
                    currentSelectSiteId = this_select.val();
                    changeSite(currentSelectSiteId);
                    initCategoryView(this_select.val());
                }
                else
                {
                    $.confirm({
                        text: 'ターゲットサイトを変更してもよろしいでしょうか？',
                        confirmButton: "はい",
                        cancelButton: "いいえ",
                        confirm: function() {
                            currentSelectSiteId = this_select.val();
                            changeSite(currentSelectSiteId);
                            initCategoryView(this_select.val());
                        },
                        cancel: function() {
                            this_select.val(currentSelectSiteId);
                            return false;
                        }
                    });
                }
            }
        });

        $('input[name="post_title"]').keyup(function(){
            $('#title-word-count').html($.trim($(this).val().length));
            window.instanceRedactor.counter.count();
        });

        $('textarea[name="post_description"]').keyup(function(){
            $('#description-word-count').html($.trim($(this).val()).length);
            window.instanceRedactor.counter.count();
            if($(this).val().length >= 120) {
                $(this).val($(this).val().slice(0, 120));
                $('#description-word-count').html($.trim($(this).val()).length);
                return false;
            }
        });
        $('div.redactor-editor.redactor-in').css('min-height', window.outerHeight);
        $('input[name="post_title"], textarea[name="post_description"]').keydown(function(){
            changeStatus = true;
        });

        $('#preview-btn').click(function(){
            // Remove JCLRgrips in content for preview
            var htmlContent = $('.redactor-editor').html();
            if($('#contentClone').length > 0) {
              $('#contentClone').remove();
            }
            $('<div/>', { 'id': 'contentClone' }).html(htmlContent).insertAfter('form');
            $('#contentClone').find('.JCLRgrips').remove();
            var htmlClone = $('#contentClone').html();
            $('form').append($('<textarea/>', { 'name': 'post_content_clone' }).text(htmlClone));
            // End of remove JCLRgrips in content for preview

            $('form')
                .attr('action', '{{ action('Webapp\\PostController@postPreviewIframe') }}')
                .attr('target', '_blank');
        });

        $('form').submit(function(){
            window.onbeforeunload = null;
            if($(this).attr('action') === undefined || $(this).attr('action').length == 0) {
                clearInterval(interval);
                changeStatus = false;
                savePostAjax();
                return false;
            }

            setTimeout(function(){
                $('form').removeAttr('action', '').removeAttr('target', '');
            }, 500);
        });

        previousSubmitButton = null;
        $('input[type="submit"]').click(function(){
            if(!$(this).hasClass('preview_sbm')) {
              $('.redactor-editor').find('div.JCLRgrips').remove();
            }

            $('.background-hover').removeClass('background-hover');
            if(!checkPostDataRequireField($(this).hasClass('publish_sbm'), false)){
                return false;
            }
            // Check alert tag script in posts
            if (isActionScript($(this), ['publish_sbm', 'draft_sbm', 'preview_sbm'])) {
                return false;
            }

            previousSubmitButton = this;
            $.each(instanceRedactor.core.editor().find('p, li, u, strong, b, i, h1, h2, h3, h4, em, table, .table-box'), function(k,e) {
                if($(e).prop("tagName") == 'TABLE'){
                    $(e).parent('div.table-box').length ? '' : $(e).wrap("<div class='table-box'></div>");
                }else if($.trim($(e).html()) == '') {
                    $(e).remove();
                }
            });
            instanceRedactor.core.editor().find('h5, h6').replaceWith(function() {
                return $('<p/>', {
                    html: this.innerHTML
                });
            });
            if($(this).hasClass('publish_sbm') || $(this).hasClass('draft_sbm')){
                index_for_link();
            }

            var content = instanceRedactor.core.editor().html();

            $('textarea[name="post_content"]').val(content);
            var checksum = hasher().updateStringly(content).result.toString(16);
            $('input[name="checksum"]').val(hasher().updateStringly(content).result.toString(16));
        });

        changeSite($('select[name="site_id"]').val());
        $('#title-word-count').html($.trim($('input[name="post_title"]').val().length));
        $('#description-word-count').html($.trim($('textarea[name="post_description"]').val()).length);
        window.instanceRedactor.counter.count();

        if(lockStatus) {
            $( "#dialog-confirm" ).dialog({
                resizable: false,
                height: "auto",
                draggable: false,
                width: 600,
                modal: true,
                buttons: {
                    "引き継ぐ": function() {
                        $.post('{{ action('Webapp\\PostController@postOverride') }}', {
                            '_token': '{{ csrf_token() }}',
                            'post_id' : currentPostId
                        }, function(data) {
                            if(data.status == false) {
                                alert("Error when override. Please retry");
                                window.location.href = '{{ action('Webapp\\PostController@getIndex') }}';
                                return;
                            }
                            lockStatus = false;
                            uiUnlockPost();
                        });
                        $( this ).dialog( "close" );
                    },
                    "プレビュー": function() {
                        window.location.href = '{{ action('Webapp\\PostController@getPreview', $post->id) }}';
                    },
                    "一覧へ戻る": function() {
                        window.location.href = '{{ action('Webapp\\PostController@getIndex') }}';
                    }
                },
                open: function(event, ui) {
                    //hide close button.
                    $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
                },
                create: function (event) {
                    $(event.target).parent().css({ 'position': 'fixed', "top": 150 });
                }
            });

            uiLockPost();
        }

        window.onbeforeunload = function(e) {
            if(!changeStatus) {
                return;
            }

            return '変更したが保存されないコンテンツがあります。保存しますか？';
        };

        $(window).on('unload', function(){
            $.ajax({
                type: "POST",
                url: '{{ action('Webapp\\PostController@postStashChange') }}',
                data : {
                    '_token': '{{ csrf_token() }}',
                    'post_id': currentPostId
                },
                async: false
            });
        });
    });

    function loadingUi(action) {
        var loading = $('<div id="loading"><i class="fa fa-spinner fa-spin"></i></div>');
        (action == true) ? $('body').append(loading) : $('div#loading').remove();
    }

    function showAutoSaveError(message) {
        $("#dialog-autosave").find("p").html('保存に失敗しました。もう一度試してください。エラーメッセージ: <b>'+message+'。投稿内容をバックアップして、管理者まで報告してください。</b>');
        $("#dialog-autosave").dialog({
            resizable: false,
            height: "auto",
            draggable: false,
            width: 600,
            modal: true,
            open: function(event, ui) {},
            create: function (event) {
                $(event.target).parent().css({ 'position': 'fixed', "top": 150 });
            }
        });
    }

    function savePostAjax() {
        loadingUi(true);
        var afterCheckTitle = function() {
            clearInterval(globalLockInterval);

            var contentDetail = getContentDetail();
            var checksum = contentDetail.checksum;

            $('input[name="checksum"]').val(checksum);

            var data = $('form').serializeObject();
            data['submit_type'] = $(previousSubmitButton).val();

            $.post('{{ action('Webapp\\PostController@postEditAjax', $post->id) }}', data, function(data) {
                if(data.error !== undefined) {
                    loadingUi(false);
                    showAutoSaveError(data.error.message);
                    return;
                }

                if(data.data.checksum != checksum) {
                    loadingUi(false);
                    showAutoSaveError('サーバへ送信する内容にエラーがあります');
                    return;
                }

                window.location.href = data.next;
            })
            .error(function(){
                loadingUi(false);
                showAutoSaveError('サーバにエラーがあります');
            });
        };

        if(!$(previousSubmitButton).hasClass('publish_sbm')) {
            afterCheckTitle();
            return;
        }

        var postTitle = $('input[name="post_title"]').val();
        var postSiteId = $('#cmsTargetSitesList').val();

        $.post('{{ action('Webapp\\AjaxController@postIsDuplicatedTitle') }}', {id: currentPostId, title: postTitle, site_id: postSiteId}, function(data) {
            if(data.duplicated == 1) {
                $.confirm({
                    text: 'このタイトルは存在しています。公開できないのでタイトルを変更してください。',
                    title: '警告',
                    cancelButton: "OK",
                    confirm: function(button) {
                    },
                    cancel: function(button) {
                    },
                    confirmButtonClass: 'hide'
                });
                loadingUi(false);
            } else {
                afterCheckTitle();
            }
        }).
        error(function() {
            loadingUi(false);
            showAutoSaveError('System failed. Please contact admin');
        });
    }
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
        <input type="hidden" name="post_id" value="{{ $post->id }}" />
        <input type="hidden" name="checksum" value="{{ $postChecksum }}" />
        <input type="hidden" name="word_count" value="0" />
        <div class="form-group">
            <label>ターゲットサイト</label>
            <select name="site_id" class="form-control" id="cmsTargetSitesList">
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
            <input type="text" class="form-control" name="post_title" value="{{ $post->title }}" autocomplete="off" placeholder="（作業シートで指定されているものを入れてください）"/>
            <label class="word-count">文字カウント：<span id="title-word-count">0</span></label>
            <div class="clearfix"></div>
        </div>
        <div class="form-group">
            <label>アイキャッチ画像</label>
            <input type="hidden" name="new_feature_img_url" id="new_feature_img_url" value="{!! $post->feature_img !!}" />
            <div class="">
                <input type="radio" class="feature_img_type" name="_feature_img_type" value="upload" checked /> アップロード
                &nbsp;&nbsp;&nbsp;&nbsp;<button id="thumb-gphoto">画像ライブラリを使う</button>
                {{--<input type="radio" class="feature_img_type" name="_feature_img_type" value="url" /> URL--}}
            </div>
            <div class="" id="feature-img-upload">
                <input type="file" id="feature_img_file" />
                <input type="hidden" id="final-url-uploaded-img" />
            </div>
            <div class="" id="feature-img-url" style="display: none;">
                <input type="text" id="tmp_feature_img_url" class="form-control" placeholder="画像URLをペスト" />
            </div>
            <div class="" id="feature-img-display">
                <img id="current_feature_img_url" src="{!! $post->feature_img !!}" title="{!! $post->title !!}" alt="{!! $post->post_excerpt !!}" width="200" />
            </div>
        </div>
        <div class="form-group">
            <label>リード文</label>
            <textarea id="post-description" class="input-border" placeholder="リード文（キーワードは必ず１文目に入れる。タイトル下に表示される重要な部分）" name="post_description" autocomplete="off">{{ $post->description }}</textarea>
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

        <script>
            $('input.feature_img_type').click(function(){
                if($(this).val() == "upload") {
                    $('#feature-img-upload').show();
                    $('#feature-img-url').hide();
                } else {
                    $('#feature-img-upload').hide();
                    $('#feature-img-url').show();
                }
            });

            thumbInsert = function(url) {
                $('img#current_feature_img_url').attr('src', url);
                $('img#current_feature_img_url').show();
                $('#final-url-uploaded-img').val(url);

                $('input[name="new_feature_img_url"]').val(url);
            };

            $('button#thumb-gphoto').click(function(event){
                event.preventDefault();
                var left  = ($(window).width()/2)-(900/2),
                    top   = ($(window).height()/2)-(600/2),
                    popup = window.open ("/webapp/gphoto/single-pick", "popup", "width=1000, height=600, top="+top+", left="+left);
            });

            $('input[type="file"]#feature_img_file').change(function(event){
                var file = event.target.files[0];
                var formData = new FormData();

                formData.append('file', file);
                $.ajax({
                    url: '/ajax/image',
                    type: 'POST',
                    processData: false, // important
                    contentType: false, // important
                    dataType : 'json',
                    data: formData,
                    success: function(data) {
                        if(data.error !== undefined) {
                            alert(data.error);
                            $('#final-url-uploaded-img').val('');
                        } else {
                            thumbInsert(data.url);
                        }

                        $('#feature_img_file').val('');
                    }
                });
            });

            $('input#tmp_feature_img_url').change(function(){
                $('img#current_feature_img_url').attr('src', $(this).val());
                $('img#current_feature_img_url').show();
                $('input[name="new_feature_img_url"]').val($(this).val());
            });
        </script>
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
            <textarea id="content" name="post_content" rows="6" autocomplete="off" placeholder="1つ目の見出し(H2)は導入部分。必ず画像とテキストをセットで作成ください。">{!! $post->content !!}</textarea>
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
            @if($post->canPublishBy(session('current_user')))
            <div class="button-fixed publish_sbm_fixed">
                    <input type="submit" class="btn btn-success publish_sbm"
                           value="公開" name="submit_type">
                    <span class="tooltip">公開</span>
            </div>
            @endif
            @if($post->canDraftBy(session('current_user')))
            <div class="button-fixed draft_sbm_fixed">
                <input type="submit" class="btn btn-warning draft_sbm"
                       value="@if($post->published_status == 0)下書きに保存@else下書きに戻す@endif" name="submit_type" />
                <span class="tooltip">@if($post->published_status == 0)下書きに保存@else下書きに戻す@endif</span>
            </div>
            @endif
            <div class="button-fixed preview_sbm_fixed">
                <input type="submit" class="btn btn-info preview_sbm" id="preview-btn"
                       value="プレビュー" name="submit_type" />
                <span class="tooltip">プレビュー</span>
            </div>
            @if($post->canCopyBy(session('current_user')))
            <div class="button-fixed copy_sbm_fixed">
                <a href="{!! action('Webapp\PostController@getCopyPost', $post->id) !!}" class="btn btn-primary copy_sbm" onclick="if(isActionScript($(this), ['copy_sbm'])) return false;">コピー</a>
                <span class="tooltip">コピー</span>
            </div>
            @endif
            @if($post->canWriteDeleteBy(session('current_user')))
            <div class="button-fixed delete_sbm_fixed">
                <button class="btn btn-danger submit-delete-post delete_sbm" data="{!! action($action_delete, $post->id) !!}">
                    削除
                </button>
                <span class="tooltip">削除</span>
            </div>
            @endif
        </div>
    </form>
</div>

<script>
    $('.submit-delete-post').confirm({
        text: "ターゲットサイトの記事も削除されます。よろしいでしょうか？",
        title: "記事削除",
        confirmButton: "はい",
        cancelButton: "いいえ",
        post: true,
        submitForm: true,
        confirm: function(button) {
            clearInterval(globalLockInterval);
            window.location.href = button.attr('data');
        },
        cancel: function() {

        }
    });

    function featureImageMethod(method) {
        switch (method) {
            case 'url':
                $('#new_feature_img_url').val('');
                $('#feature_img_select').val('');
                break;
            case 'upload':
                $('#new_feature_img_url').val('');
                $('#featureimgExternalPreview').html('')
                break;
        }
    }

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
                $('select.category-list:not([disabled="disabled"])').find('option').prop('selected', false).removeAttr('selected');
                $('select.category-list:not([disabled="disabled"])').find('option[id-val="' + data.selected + '"]').prop('selected', true).attr('selected', 'selected');
            }).on("deselect_node.jstree", function(e, data) {
                $('select.category-list:not([disabled="disabled"])').find('option').removeAttr('selected').prop('selected', false);
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
//        $('#current_feature_img_url').attr('src') == '' ? $('#current_feature_img_url').css('display', 'none') : $('#current_feature_img_url').css('display', 'block');

        (function($) {
            if (!$.curCSS) {
                $.curCSS = $.css;
            }
        })(jQuery);

        $('#main_kw_tagit').tagit({
            allowDuplicates: false,
            readOnly: false,
            allowSpaces: true,
            singleFieldDelimiter: ',',
            placeholderText: 'メインキーワード',
            tagLimit: 1,
        });
        $('#sub_kw_tagit').tagit({
            allowDuplicates: false,
            readOnly: false,
            allowSpaces: true,
            singleFieldDelimiter: ',',
            placeholderText: 'サブキーワード',
        });

        initCategoryView($('#cmsTargetSitesList').val())

    });
</script>
@endsection
