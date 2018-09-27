@extends('webapp/layout')

@section('addonScript')
    <script type='text/javascript' src='{{ asset('res/redactor/video.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/twitter.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/product.js') }}?v=20180314'></script>
    <script src="{{ asset('res/js/jquery-ui.min.js') }}" type="text/javascript" charset="utf-8"></script>
    <script type='text/javascript' src='{{ asset('res/js/tag-it.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/relipa.redactor.js') }}?v=20180123'></script>
    <link rel="stylesheet" href="{!! asset('res/css/jstree-themes/default/style.min.css') !!}"/>
    <link rel="stylesheet" href="{{ asset('res/css/207_245_257_product_box.css') }}">
    <script type="text/javascript" src="{!! asset('res/js/jstree.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('res/js/jstree.realcheckbox.js?v=20170705') !!}"></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.confirm.min.js') }}'></script>
    <script type="text/javascript" src="{{ asset('res/checksum/checksum.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('res/js/pastebin.js') }}"></script>
    <style type="text/css">

        .word-count {
            float: left;
            margin-right: 20px;
        }
    </style>
@stop

@section('mainContent')
<script type="text/javascript">
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
    currentPostId = 0;
    currentPostIdLog = 0;
    changeStatus = false;
    lockStatus = false;
    error_log_flag = '';
    NO_SCRIPT = "{{ env('NO_SCRIPT', true) }}";

    hasher = function() { return new Checksum("crc32") };

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
            window.location.href = '{{ action('Webapp\\PostController@getEdit') }}/'+currentPostId;
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
            plugins: ['source', 'video', 'alignment', 'counter', 'blockFormat', 'table', 'inlinestyle', 'chatbox', 'twitter', 'product', 'iconic2', 'htmlBox'],
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
        isRequestedNewId = false;

        function autoSave() {
            var LogAutoSaveFailed = function (error_msg) {
                $.post('{{ action('Webapp\\PostController@postErrorLog') }}', {
                    '_token': '{{ csrf_token() }}',
                    'post_id' : currentPostId,
                    'error_msg' : error_msg
                })
            };
            if(autoSaveTriggered || autoSaveChange === false) {
                return;
            }
            autoSaveTriggered = true;
            var contentDetail = getContentDetail(true);

            var autoSaveFunction = function() {
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
//                        tryAgainSave();
                        return;
                    }

                    var utcTime = moment.utc(data.time.date);
                    $('#last-saved-noti').html('記事が '+utcTime.local().format("YYYY年MM月DD日, h:mm:ss a")+' に自動保存した。');
                })
                .error(function(error_msg){
//                    tryAgainSave();
                    if (error_log_flag == error_msg.responseText) return;
                    error_log_flag = error_msg.responseText;
                    LogAutoSaveFailed(error_msg.responseText);
                })
                .always(function(){
                    autoSaveTriggered = false;
                });
            };

            autoSaveChange = false;

            if(currentPostId == 0 && isRequestedNewId == false) {
                $.post('{{ action('Webapp\\PostController@postRequestNewId') }}', {
                    '_token': '{{ csrf_token() }}'
                }, function(data){
                    currentPostId = data.id;
                    $('input[name="post_id"]').val(currentPostId);
                    isRequestedNewId = true;
                    autoSaveFunction();
                })
                .error(function(){
//                    tryAgainSave();
                });
            } else {
                autoSaveFunction();
            }
        }

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

        var lockInterval = false;

        var interval = setInterval(function(){
            autoSave();

            if(lockInterval) {
                return;
            }

            if(currentPostId == 0) {
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

        uiLockPost = function() {
            $('input[type="submit"]').attr('disabled', 'disabled');
        }

        $('div.redactor-editor.redactor-in').css('min-height', window.outerHeight);
        function changeSite(siteId) {
            $('select[name="category"]').hide().attr('disabled', 'disabled');;
            $('select.category-list[data-category="'+siteId+'"]').show().removeAttr('disabled');
        }

        $('select[name="site_id"]').change(function(){
            var siteId = $(this).val();
            changeSite(siteId);
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
            if(!checkPostDataRequireField($(this).hasClass('publish_sbm'), true)){
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

        window.onbeforeunload = function(e) {
            if(!changeStatus) {
                return;
            }

            return '変更したが保存されないコンテンツがあります。保存しますか？';
        };
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
            var contentDetail = getContentDetail();
            var checksum = contentDetail.checksum;

            $('input[name="checksum"]').val(checksum);

            var data = $('form').serializeObject();
            data['submit_type'] = $(previousSubmitButton).val();

            $.post('{{ action('Webapp\\PostController@postCreateAjax') }}', data, function(data) {
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
        }

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
        })
        .error(function() {
            loadingUi(false);
            showAutoSaveError('System failed. Please contact admin');
        });
    }
</script>
@include('webapp/components/notification')
<div id="dialog-try-again" title="エラー" style="display: none">
    <p>警告：自動保存に失敗しました。管理者までこのURL：<b><a id="backup-url" href="#"></a></b> を送ってください。その後、アラートを閉じて記事内容をコピーし、ローカルに保存してください。</p>
</div>
<div id="dialog-autosave" title="エラー" style="display: none;">
    <p></p>
</div>
<div class="row">
    <form method="post" autocomplete="off">
        {{ csrf_field() }}
        <input type="hidden" name="post_id" value="0" />
        <input type="hidden" name="checksum" value="" />
        <input type="hidden" name="word_count" value="0" />
        <div class="form-group">
            <label>ターゲットサイト</label>
            <select name="site_id" class="form-control" id="cmsTargetSitesList">
                <option value="">サイトを選んでください</option>
                @foreach($sites as $site)
                <option value="{{ $site->id }}" site-url="{{ $site->site_url }}">{{ $site->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group hide">
            <label>カテゴリ</label>
            <select name="category" class="form-control">
                <option value="">なし</option>
            </select>
            @foreach($categoriesSet as $siteId => $categories)
            <select name="category" class="form-control category-list" data-category="{{ $siteId }}" style="display: none;">
                <option value="">なし</option>
                @foreach($categories as $category)
                <option id-val="{!! $category->id !!}" value="{{ $category->slug }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @endforeach
        </div>

        <div class="cate-row" style="display: none;">
            <div class="form-group">
                <label>カテゴリ</label>
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
        <div class="form-group">
            <label>タイトル</label>
            <input type="text" class="form-control" name="post_title" autocomplete="off" placeholder="（作業シートで指定されているものを入れてください）"/>
            <label class="word-count">文字カウント：<span id="title-word-count">0</span></label>
            <div class="clearfix"></div>
        </div>
        <div class="form-group">
            <label>リード文</label>
            <textarea id="post-description" class="input-border" placeholder="リード文（キーワードは必ず１文目に入れる。タイトル下に表示される重要な部分）" name="post_description" autocomplete="off"></textarea>
            <label class="word-count">文字カウント：<span id="description-word-count">0</span></label>
            <div class="clearfix"></div>
        </div>
        <div class="form-group">
            <label>アイキャッチ画像</label>
            <input type="hidden" name="feature_img_url" id="feature_img_url" value="" />
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
                <img id="current_feature_img_url" src="" width="200" style="display: block;"/>
            </div>
        </div>
        {{-- Keyword wrapper --}}
        <div class="form-group">
            <label>キーワード</label>
            {{-- Main keywords --}}
            <div class="form-group">
                <input type="text" class="form-control" placeholder="メインキーワード" id="main_kw_tagit" name="mainkw" />
            </div>
            {{-- Sub keywords --}}
            <div class="form-group">
                <input type="text" class="form-control" placeholder="1つ目の見出し(H2)は導入部分。必ず画像とテキストをセットで作成ください。" id="sub_kw_tagit" name="subkws" />
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
                $('#final-url-uploaded-img').val(url);

                $('input[name="feature_img_url"]').val(url);
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
                $('input[name="feature_img_url"]').val($(this).val());
            });
        </script>

        <div class="form-group">
            <label>コンテンツ</label>
            <textarea id="content" name="post_content" rows="6" autocomplete="off" placeholder="1つ目の見出し(H2)は導入部分。必ず画像とテキストをセットで作成ください。"></textarea>
            <label class="word-count">文字カウント：<span id="word-count">0</span></label>
            <label class="word-count">総文字数：<span id="total-count" counter="0">0</span></label>
            <label class="word-count" id="last-saved-noti" style="float: right;"></label>
            <div class="clearfix"></div>
        </div>
        <div class="form-group group-fixed">
            @if(session('current_user')->is('admin', 'rewriter', 'rewriter2'))
                <div class="button-fixed publish_sbm_fixed">
                    <input type="submit" class="btn btn-success publish_sbm" value="公開" name="submit_type">
                    <span class="tooltip">公開</span>
                </div>
            @endif
            <div class="button-fixed draft_sbm_fixed">
                <input type="submit" class="btn btn-warning draft_sbm" value="下書きに保存" name="submit_type">
                <span class="tooltip">下書きに保存</span>
            </div>
            <div class="button-fixed preview_sbm_fixed">
                <input type="submit" class="btn btn-info preview_sbm" value="プレビュー" id="preview-btn" name="submit_type">
                <span class="tooltip">プレビュー</span>
            </div>
        </div>
    </form>
</div>

<script>
    function initCategoryView(site_id) {
        if(typeof  site_id === 'undefined' || site_id === '') { $('div.cate-row').hide(); return};
        $('div.cate-row').show();
        $('.loading').show();
        $.get('/webapp/category/list-categories?id=' + site_id, function (data) {
            $('.loading').hide();
            $('#categoryTreeView').jstree('destroy').on("ready.jstree", function () {
                $('#categoryTreeView').jstree('open_all');
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
                    "three_state": false, //required for the cascade none to work
                    "cascade": 'none' //no auto all_children<->parent selection
                },
                "plugins": ["themes", "json_data", "ui", "types", "search", "checkbox", "realcheckboxes"],
                'core': {
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

        $('#cmsTargetSitesList').change(function () {
            initCategoryView($(this).val());
        });
    });

</script>
@endsection
