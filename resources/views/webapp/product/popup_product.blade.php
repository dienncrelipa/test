@extends('webapp/raw_layout')

@section('mainContent')
<div class="row" style="padding: 10px; max-width: 1000px;">
    <div><a href="javascript:window.history.back();">Back</a></div>
    <div><h4>{{ @$product->master_product_name }}</h4></div>
    <div>
        <table class="popup-product-table">
            <tr>
                <td class="td-head">マスター商品名</td>
                <td class="td-content" colspan="3">{{ $product->master_product_name }}</td>
            </tr>
            <tr>
                <td class="td-head">親カテゴリー</td>
                <td class="td-content">{{ isset($product->category)? (isset($product->category->parent) ? $product->category->parent->name : '(なし)' ) : '(なし)'}}</td>
                <td class="td-head">サブカテゴリ</td>
                <td class="td-content">{{ isset($product->category) ? $product->category->name : '(なし)' }}</td>
            </tr>
            <tr>
                <td class="td-head">優先度</td>
                <td class="td-content">{{ isset($product->priority) ? $product->priority : '(なし)' }}</td>
                <td class="td-head">ASP</td>
                <td class="td-content">{{ isset($product->asp) ? $product->asp : '(なし)' }}</td>
            </tr>
            <tr>
                <td class="td-head">アフィリエイトURL</td>
                <td class="td-content" colspan="3">
                    @foreach($product->affiliate as $affiliate)
                        {{ $affiliate->pivot->affiliate_url }}<br>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td class="td-head">メモ</td>
                <td class="td-content" colspan="3">{{ $product->note }}</td>
            </tr>
        </table>
    </div>
    <div>
        {!! str_replace('\\', '', $embedCode) !!}
    </div>
    <div>
        <a href="javascript:void(0);" id="insert-btn-link" class="btn btn-success">リンク挿入</a>
    </div>

</div>
@stop
@section('addonScript')
    <style>
        td.td-head {
            background-color: #D4E1F5;
            width: 20%;
        }
        .popup-product-images-table td {
            border: none;
            text-align: center;
        }
        .popup-product-images-table td img {
            width: 150px;
            height: 150px;
            cursor: pointer;
            border: 8px solid transparent;
            object-fit: cover;
        }
        .popup-product-images-table td img:hover {
            opacity: 0.8;
        }
        .popup-product-images-table td img.image-choosen {
            border: 8px solid #51B11D;
            border-radius: 4px;
        }
        li.type-item {
            height: 56px;
            display: inherit;
        }
    </style>
    <script>
        tag_insert = '<p>{!! $embedCode !!}</p><p><br /></p>';
        $(function () {
            $('#insert-btn-link').click(function(){
                window.opener.triggerInsert(tag_insert);
            });
        });
    </script>
@endsection