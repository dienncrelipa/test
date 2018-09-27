@extends('webapp/raw_layout')

@section('mainContent')
<script>
    $(function(){
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
<div class="row" style="padding: 10px; max-width: 1000px;">
    <div>
        <form method="get" action="{{ action('Webapp\\ProductController@getPopup', 'picker') }}">
            <input type="text" id="keyword" name="k" placeholder="検索" value="{{ $keyword }}"/>
            <input type="submit" value="検索" />
        </form>
    </div>
    <div>
        @foreach($categories as $category)
        <a href="{{ action('Webapp\\ProductController@getPopup', 'picker').'?c='.$category->id }}">{{ $category->name }}</a>&nbsp;&nbsp;&nbsp;
        @endforeach
    </div>
    <table>
        <tr>
            <th data-name="id">カテゴリ
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('category_id', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('category_id', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th data-name="rate">評価
                <small style="white-space: nowrap">
                    <a title="Sort ascending" class="sort-asc" href="javascript:sortPrd('rate', 'asc')">
                        ▲
                    </a>
                    <a title="Sort descending" class="sort-desc" href="javascript:sortPrd('rate', 'desc')">
                        ▼
                    </a>
                </small>
            </th>
            <th>商品名</th>
            <th></th>
        </tr>
        @foreach($products as $product)
        <tr>
            <td>{{ ($product->category) ? $product->category->name : '(なし)' }}</td>
            <td>{{ $product->rate }}</td>
            <td><a href="{{ action('Webapp\\ProductController@getPopupProduct', [$product->id, $picker]) }}">{{ $product->name }}</a></td>
            <td><a href="javascript:;" class="btn btn-default" onclick="window.opener.rankingPickUrlProd('{{ $product->affiliate_url }}')">リンク挿入</a></td>
        </tr>
        @endforeach
    </table>
    <div id="paginator"></div>
</div>
@endsection