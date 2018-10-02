@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<style>
    .img-thumb {
        max-width: 200px;
        height: 200px;
        border: 1px solid #CCC;
        display: table-cell;
        vertical-align: middle;
        position: relative;
    }
    .img-thumb .remove-thumb {
        position: absolute;
        top: 0px;
        right: 7px;
        font-size: 16px;
        text-shadow: 0px 0px 3px #000;
        color: #FFF;
        cursor: pointer;
    }
    .img-thumb img {
        width: 100%;
        max-height: 200px;
    }
    .img-thumb img.img-disable {
        opacity: 0.4;
    }
    .img-thumb.temp-thumb {
        width: 200px;
        height: 200px;
    }
    .img-thumb.temp-thumb img {
        display: none;
    }
    #img-thumb-list, #img-thumb-list tr, #img-thumb-list td {
        border: none;
    }
    #img-thumb-list td {
        width: 25%;
    }
</style>
<script>
    insertNewImage = function(imgSource, dataId, additionClass) {
        var parent = $('#img-thumb-list');

        // remove all empty tr
        $.each(parent.find('tr'), function(k, v){
            if($(v).children('td').length == 0) {
                $(v).remove();
            }
        });

        var lastTr = parent.find('tr:last-child');

        if(lastTr.length == 0 || lastTr.children('td').length == 4 ) {
            lastTr = $('<tr />');
            parent.append(lastTr);
        }

        var div = $('<div class="img-thumb" data-id="'+dataId+'" data-select="on">')

        lastTr.append($(
                '<td class="'+additionClass+'">' +
                    '<div class="img-thumb '+additionClass+'" data-id="'+dataId+'" data-select="on">' +
                        '<img src="'+imgSource+'" />' +
                        '<div class="remove-thumb">X</div>' +
                    '</div>' +
                '</td>'
        ));
    };

    $(function(){
        $(document).on('click', '.remove-thumb', function(){
            var parent = $(this).parent();
            if(parent.data('select') == 'on') {
                parent.children('img').addClass('img-disable');
                parent.data('select', 'off');
                $(this).html('O');
            } else {
                parent.children('img').removeClass('img-disable');
                parent.data('select', 'on');
                $(this).html('X');
            }
        });

        $('form').submit(function(){
            var form = $(this);
            $('input[name="image[]"]').remove();
            $('<input type="hidden" name="image[]" value="0" />').appendTo(form);
            $('.img-thumb').each(function(e, v){
                var id = $(v).data('id');
                var select = $(v).data('select');
                if(select != 'on') {
                    return;
                }
                $('<input type="hidden" name="image[]" value="'+id+'" />').appendTo(form);
            });
        });

        $('input#img-file-upload').change(function(event){
            var files = event.target.files;
            var formData = new FormData();

            $.each(files, function(k, v){
                formData.append('files[]', v);
                insertNewImage('#', 0, 'temp-thumb');
            });

            $.ajax({
                url: '{{ (new \App\Libs\ProductApiDriver())->url('/api/image/upload') }}',
                type: 'POST',
                processData: false, // important
                contentType: false, // important
                dataType : 'json',
                data: formData,
                success: function(data) {
                    $('.temp-thumb').remove();
                    if(data.error !== undefined) {
                        alert(data.error.message);
                        return;
                    }
                    if(!Array.isArray(data.data)) {
                        return;
                    }
                    $.each(data.data, function(k, v) {
                        insertNewImage(v.url, v.id, '');
                    });
                }
            });

            $(this).val('');
        });
    });

</script>
<div class="row">
    <form method="post">
        {{ csrf_field() }}
        <div class="form-group">
            <label>商品名</label>
            <input type="text" class="form-control" name="name" value="{{ $product->name }}"/>
        </div>
        <div class="form-group">
            <label>商品ID</label>
            <input type="text" class="form-control" name="product_code" value="{{ $product->product_code }}" />
        </div>
        <div class="form-group">
            <label>定価</label>
            <input type="text" class="form-control" name="unit_price" value="{{ $product->unit_price }}"/>
        </div>
        <div class="form-group">
            <label>販売価格</label>
            <input type="text" class="form-control" name="sale_price" value="{{ $product->sale_price }}"/>
        </div>
        <div class="form-group">
            <label>アフィリエイト報酬単価</label>
            <input type="text" class="form-control" name="affiliate_earning" value="{{ $product->affiliate_earning }}"/>
        </div>
        <div class="form-group">
            <label>アフィリエイトURL</label>
            <input type="text" class="form-control" name="affiliate_url" value="{{ $product->affiliate_url }}"/>
        </div>
        <div class="form-group">
            <label>評価</label>
            <input type="text" class="form-control" name="rate" value="{{ $product->rate }}"/>
        </div>
        <div class="form-group">
            <label>カテゴリ</label>
            <select name="category_id">
                <option value="null">(None)</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" @if($category->id == $product->category_id)selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>画像 ※複数可</label>
            <input type="file" id="img-file-upload"  multiple />
            <table id="img-thumb-list" style="border: none" border="0">
                <?php
                $count = 0;
                foreach($product->images as $image) {
                    echo ($count % 4 == 0) ? '<tr>' : '';
                    echo '<td>
                            <div class="img-thumb" data-id="'.$image->id.'" data-select="on">
                                <img src="'.$image->url.'" />
                                <div class="remove-thumb">X</div>
                            </div>
                          </td>';
                    echo ($count % 4 == 3) ? '</tr>' : '';
                    $count++;
                }
                ?>
            </table>
        </div>
        <div class="form-group">
            <label>メモ</label>
            <textarea rows="5" class="form-control" name="note">{{ $product->note }}</textarea>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-warning" value="送信">
        </div>
    </form>
</div>
@endsection