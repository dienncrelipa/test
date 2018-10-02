@extends('webapp/raw_layout')

@section('mainContent')
<form method="get" action="{{ action('Webapp\\ProductController@getPopup') }}" style="box-sizing: border-box;">
    <table id="searchForm">
        <tbody>
            <tr class="form-group">
                <td><label for="keyword">検索</label></td>
                <td>
                    <div class="col-md-6 row">
                        <input type="text" id="keyword" class="form-control" name="k" placeholder="検索" value="{{ old('k') }}"/>
                    </div>
                </td>
            </tr>
            <tr class="form-group">
                <td><label for="category">カテゴリー</label></td>
                <td>
                    <div class="col-md-6 row">
                        <select name="category" id="category" data-url="{{ action('Webapp\\ProductController@getAjaxCategoryList') }}">
                            @if (isset($selectedCategory))
                                <option value="{{ $selectedCategory->id }}" selected="selected">{{ $selectedCategory->name }}</option>
                            @endif
                        </select>
                    </div>
                </td>
            </tr>
            <tr class="form-group">
                <td><label for="tag">タグ</label></td>
                <td>
                    <div class="col-md-6 row">
                        <select name="tag[]" id="tag" multiple="multiple" data-url="{{ action('Webapp\\ProductController@getAjaxTagList') }}">
                            <option value="0">No choose</option>
                            @if (isset($selectedTags))
                                @foreach($selectedTags as $tag)
                                    <option value="{{ $tag->id }}" selected>{{ $tag->text }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </td>
            </tr>
            <tr class="form-group check-box">
                <td><label>優先度</label></td>
                <td>
                    @for( $i=1; $i<=5; $i++)
                        <label class="checkbox-inline">
                            <input value="{{ $i }}" type="checkbox" name="priority[]" @if(!empty(old('priority')) && array_search($i, old('priority')) !== false) checked @endif />{{ $i }}
                        </label>
                    @endfor
                </td>
            </tr>
            <tr class="form-group">
                <td></td>
                <td><button class="btn btn-primary">検索</button></td>
            </tr>
        </tbody>
    </table>
</form>
<table>
    <tr>
        <th>ID
            <small class="pull-right">
                <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('id', 'asc')">▲</a>
                <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('id', 'desc')">▼</a>
            </small>
        </th>
        <th>優先度
            <small class="pull-right">
                <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('priority', 'asc')">▲</a>
                <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('priority', 'desc')">▼</a>
            </small>
        </th>
        <th>カテゴリー</th>
        <th>タグ</th>
        <th>マスター商品名
            <small class="pull-right">
                <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('master_product_name', 'asc')">▲</a>
                <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('master_product_name', 'desc')">▼</a>
            </small>
        </th>
    </tr>
    @if(count($products) != 0)
        @php
            $none = '<span class="text-muted">なし</span>';
        @endphp
        @foreach($products as $product)
            <tr>
                <td><a href="{{ action('Webapp\\ProductController@getPopupProduct', $product->id) . '?site_id=' . $siteId }}">{{ $product->id }}</a></td>
                <td>{!! isset($product->priority) ? $product->priority : $none !!}</td>
                <td>{!! isset($product->category) ? $product->category->name : $none !!}</td>
                <td>{!! isset($product->tag) ? $product->tag->text : $none !!}</td>
                <td>
                    @if(isset($product->master_product_name))
                        <a href="{{ action('Webapp\\ProductController@getPopupProduct', $product->id) . '?site_id=' . $siteId }}">{{ @$product->master_product_name }}</a>
                    @else
                        {!! $none !!}
                    @endif
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="6">データがありません</td>
        </tr>
    @endif
</table>
<div id="paginator"></div>
@stop
@section('addonScript')
    <style>
        #searchForm td {
            border: 0;
            width: auto;
        }
        #searchForm tr>td:first-child {
            padding-left: 0;
            width: 8%;
        }
    </style>
    <script src="{{ asset('res/js/jquery-ui.min.js') }}" type="text/javascript" charset="utf-8"></script>
    <script type='text/javascript' src='{{ asset('res/js/tag-it.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.confirm.min.js') }}'></script>
    {!! HTML::style('res/css/select2.min.css?v=20170215') !!}
    {!! HTML::style('res/css/select2-bootstrap.min.css?v=20170215') !!}
    {!! HTML::script('res/js/select2.full.min.js?v=20170215') !!}
    <script>
        $(function(){
            var LoadDataForDropdown = function () {
                return {
                    init: function (elmTag, parent_id) {
                        $(elmTag).select2({
                            theme: "bootstrap",
                            width: "100%",
                            ajax: {
                                delay: 250,
                                method: 'GET',
                                url: elmTag.data('url'),
                                dataType: 'json',
                                data: function (params) {
                                    var query = {
                                        search: params.term,
                                        page: params.page || 1,
                                        parent_id: parent_id
                                    }
                                    return query
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1
                                    return data
                                }
                            }
                        })
                    }
                }
            }();

            LoadDataForDropdown.init($('#category'));
            LoadDataForDropdown.init($('#tag'));

            $('#paginator').pagination({
                items: {{ $totalPage }},
                itemsOnPage: $('#limit').val(),
                cssStyle: 'bootstrap',
                currentPage: {{ $currentPage }},
                hrefTextPrefix: '?{!! $queryString !!}&page=',
                prevText: '«',
                nextText: '»',
                selectOnClick: false
            }).find('ul').addClass("pagination");

            @if(isset($_GET['ord']))
            $('th[data-name="{{ $_GET['ord'][0] == '-' ? substr($_GET['ord'], 1) : $_GET['ord'] }}"] a.sort-{{ $_GET['ord'][0] == '-' ? 'asc' : 'desc' }}').addClass('text-success');
            @endif

                sortPrd = function(col, ord) {
                var nextSort = '{{ isset($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc' }}';
                if(ord !== undefined) {
                    nextSort = ord == 'asc' ? 'asc' : 'desc';
                }
                    <?php
                    $varsSort = $_GET;
                    unset($varsSort['ord']);
                    ?>
                var queryString = '?{!! http_build_query($varsSort) !!}'+'&ord='+(nextSort == 'asc' ? '-' : '')+col;
                window.location.href = queryString;
            }
        });

    </script>
@endsection