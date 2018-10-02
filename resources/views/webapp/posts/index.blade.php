@extends('webapp/layout')

@section('addonScript')
    {!! HTML::style('res/css/select2.min.css?v=20170215') !!}
    {!! HTML::style('res/css/select2-bootstrap.min.css?v=20170215') !!}
    {!! HTML::script('res/js/select2.full.min.js?v=20170215') !!}
    {!! HTML::script('res/js/cms-filter.js?v=20170918') !!}
    <style>
        .input-sm {
            padding: 0;
        }
        .form-control, .btn, ul.ui-corner-all{
            border-radius: 0px;
        }
        span.select2-selection__clear{
            width: 100%;
            height: 100%;
            opacity: 0;
            padding: 0;
            margin: 0;
        }
        span.select2-container{
            width: 100% !important;
        }
        span.select2-selection,
        span.select2-dropdown{
            border-radius: 0 !important;
        }
        span.select2-selection__placeholder{
            color: #555 !important;
        }
        .select2-container--bootstrap .select2-selection--single .select2-selection__arrow b{
            border-color: #555 transparent transparent;
        }
        td.column-published_status select,
        td.column-title input{
            height: 34px;
            line-height: 34px;
        }
        .select2-container--bootstrap .select2-results__option{
            word-break: break-all;
        }
        th.column-id{
            width: 100px;
        }
        .select2-container--bootstrap .select2-search--dropdown .select2-search__field{
            margin-bottom: 0px;
        }

    </style>
    <script type='text/javascript' src='{{ asset('res/js/jquery.confirm.min.js') }}'></script>
    <script src='{{ asset('res/js/jquery-ui.min.js') }}' type="text/javascript" charset="utf-8"></script>
    <script type='text/javascript' src='{{ asset('res/js/tag-it.min.js') }}'></script>
@stop

@section('mainContent')
    @include('webapp/components/notification')

    <div class="row" style="display: none;">
        <div class="form-inline form-group">
            <select name="action" class="form-control" id="bulk-action">
                <option value="none">一括編集</option>
                <option value="edit">編集</option>
                <option value="publish">公開</option>
                <option value="draft">下書き</option>
                <option value="{{$action_delete}}">削除</option>
            </select>
            <a class="btn btn-primary" onclick="applyBulkAction(false, false)">適用</a>
            <a class="btn btn-default" id="select-all-btn">全選択</a>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row" id="edit-post" style="display: none;">
        <fieldset class="scheduler-border">
            <legend class="scheduler-border">Edit post</legend>
            <div class="form-group">
                <label>キーワード</label>
                <input type="text" class="form-control" placeholder="メインキーワード" id="main_kw_tagit" name="mainkw" value="" />
                <input type="text" class="form-control" placeholder="サブキーワード" id="sub_kw_tagit" name="subkws" value="" />
            </div>
        </fieldset>
    </div>
    <br />
    <div class="row">
        <div style="margin-bottom: 10px;" class="e-fixed">
            <a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::POST_CREATE) }}" class="button-create-post">
                <i class="fa fa-file-text-o" aria-hidden="true"></i>
                <span class="tooltip action-top">新規記事</span>
            </a>
        </div>
        <div class="table-responsive">
            {!! $posts_grid !!}
        </div>

    </div>
    <script>
        $('.btn-delete-post').confirm({
            text: "ターゲットサイトの記事も削除されます。よろしいでしょうか？",
            confirmButton: "はい",
            cancelButton: "いいえ",
            confirm: function(button) {
                window.location.href = button.attr('href');
            },
            cancel: function() {
                // nothing to do
            }
        });

        $(document).ready(function($){

            // Change 'form' to class or ID of your specific form
            $("form").submit(function() {
                $(this).find(":input").filter(
                  function() {
                      return !this.value;
                  }).attr("disabled", "disabled");
                return true; // ensure form still submits
            });

            // Un-disable form fields when page loads, in case they click back after submission
            $( "form" ).find( ":input" ).prop( "disabled", false );
            addFilter({{ $is_filter }});

        });

        $('#select-all-btn').click(function(){
            var allCheckboxes = $('input[name="selected_ids[]"]');
            if(allCheckboxes.length == $('input[name="selected_ids[]"]:checked').length) {
                allCheckboxes.prop('checked', false);
                $(this).html('全選択');
                return;
            }
            allCheckboxes.prop('checked', true);
            $(this).html('全選択解除');
        });

        $('input[name="selected_ids[]"]').change(function(){
            var allCheckboxes = $('input[name="selected_ids[]"]');
            if(allCheckboxes.length > $('input[name="selected_ids[]"]:checked').length) {
                $('#select-all-btn').html('全選択');
                return;
            }
            $('#select-all-btn').html('全選択解除');
        });
        $('#bulk-action').change(function (){
            var selected_action = $('#bulk-action').val();
            if(selected_action == 'edit'){
                $('#edit-post').show();
            }else{
                $('#edit-post').hide();
            }
        });
        
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

        applyBulkAction = function(isNext, isConfirm) {

            var action = $('#bulk-action').val();
            if($('input[name="selected_ids[]"]:checked').length <= 0){
                alert("投稿を選択していください");
                return;
            }
            if(action == 'none'){
                alert("アクションを選択してください");
                return;
            }

            if((action == 'delete' || action== 'writerDelete') && !isConfirm && !isNext) {
                $.confirm({
                    text: "ターゲットサイトの記事も削除されます。よろしいでしょうか？",
                    confirmButton: "はい",
                    cancelButton: "いいえ",
                    confirm: function(button) {
                        applyBulkAction(true, true);
                    },
                    cancel: function() {
                        // nothing to do
                    }
                });

                return;
            }
            $('body').append('<div id="loading"><i class="fa fa-spinner fa-spin"></i></div>');
            var form = $('<form class="hide"></form>');
            form.attr('method', 'POST');
            form.attr('action', '{{ action('Webapp\\PostController@postMassAction') }}');
            form.append($('{{ csrf_field() }}'));
            form.append($('input[name="selected_ids[]"]:checked').clone());
            form.append('<input type="hidden" name="action" value="' + $('#bulk-action').val() + '"/>');
            form.append($('#main_kw_tagit'));
            form.append($('#sub_kw_tagit'));
            $('body').append(form);
            form.submit();
        };
    </script>
@endsection