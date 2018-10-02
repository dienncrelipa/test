@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<?php
    $varsPaginator = $_GET;
    unset($varsPaginator['page']);
?>
<script>
    @if(isset($keyword))
    var searchKeyword = '{{ $keyword }}';
    @endif
    $(function(){
        $('#paginator').pagination({
            items: {{ $totalPage }},
            itemsOnPage: $('#limit').val(),
            cssStyle: 'bootstrap',
            currentPage: {{ $currentPage }},
            hrefTextPrefix: '?{!! http_build_query($varsPaginator) !!}&page=',
            @if(isset($keyword))
            hrefTextSuffix : '&keyword={{ $keyword }}',
            @endif
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
<div class="row">
    <div>
        <a href="{{ action('Webapp\\ProductController@getCreate') }}" class="btn btn-default">新規作成</a>
        <a href="{{ action('Webapp\\ProductController@getCategory') }}">カテゴリ</a>
    </div>
    <br />
    @if(isset($keyword))
    <h3>'{{ $keyword }}'の検索結果</h3>
    @endif
    <form method="get" action="{{ action('Webapp\\ProductController@getSearch') }}">
        <div class="form-group">
            <input type="text" name="keyword" placeholder="検索" />
            <input type="submit" value="検索" />
        </div>
    </form>
    <table>
        <tr>
            <th data-name="id">ID
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('id', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('id', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th data-name="name">商品名
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('name', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('name', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th data-name="unit_price">定価
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('unit_price', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('unit_price', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th data-name="sale_price">販売価格
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('sale_price', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('sale_price', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th data-name="affiliate_earning">アフィリエイト報酬単価
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('affiliate_earning', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('affiliate_earning', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th>アフィリエイトURL</th>
            <th>メモ</th>
            <th style="max-width: 60px;">アクション</th>
        </tr>
        @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->unit_price }}</td>
            <td>{{ $product->sale_price }}</td>
            <td>{{ $product->affiliate_earning }}</td>
            <td><a href="{{ $product->affiliate_url }}">{{ $product->affiliate_url }}</a></td>
            <td>{{ $product->note }}</td>
            <td><a href="{{ action('Webapp\\ProductController@getEdit', $product->id) }}">編集</a></td>
        </tr>
        @endforeach
    </table>
    <div id="paginator"></div>
</div>
@endsection