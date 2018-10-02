@extends('webapp/raw_layout')

@section('mainContent')
<style>
    .img-thumb {
        max-width: 200px;
        height: 200px;
        display: table-cell;
        vertical-align: middle;
        position: relative;
    }
    .img-thumb img {
        width: 100%;
        max-height: 200px;
        border: 8px solid transparent;
        cursor: pointer;
    }
    .img-thumb img.image-choosen {
        border: 8px solid #51B11D;
        border-radius: 4px;
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
    div#loading {
        text-align: center;
        display: none;
    }
</style>
<script>
    insertNewImage = function(imgSource, dataId, additionClass, sourceUrl) {
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
                '<img src="'+imgSource+'" source-url="'+sourceUrl+'" />' +
                '</div>' +
                '</td>'
        ));
    };

    loadImage = function(keyword, page, callback) {
        if(isLastPage) {
            callback();
            return;
        }

        $.ajax({
            type: 'POST',
            url: '{{ action('Webapp\\MyPhotosController@postSearch') }}',
            data: {
                '_token'  : '{{ csrf_token() }}',
                'keyword' : keyword,
                'page' : page
            },
            success: function(data){
                $('#search-btn').val('検索').removeAttr('disabled');
                callback();

                if(data.error !== undefined || data.data.length == 0) {
                    isLastPage = true;
                    return;
                }

                $.each(data.data, function(k, v){
                    var imageUrl = v.source_url;
                    if(v.thumbnail !== undefined) {
                        imageUrl = v.thumbnail.url
                    }
                    insertNewImage(imageUrl, v.id, '', v.source_url);
                });

            },
            error: function () {
                window.location.href = window.location.href;
            }

        });
    };

    currentKeyword = null;
    currentPage = 1;
    isLastPage = false;

    $(function(){
        $('form').submit(function(event){
            event.preventDefault();
            currentKeyword = $('input[name="keyword"]').val();
            currentPage = 1;
            isLastPage = false;
            $('#search-btn').val('検索中...').attr('disabled', 'disabled');
            $('#insert-btn-image').attr('disabled', 'disabled');
            loadImage(currentKeyword, currentPage, function(){});
            $('#img-thumb-list').html('');
        });

        $(window).scroll(function() {
            if($(window).scrollTop() == $(document).height() - $(window).height()) {
                currentPage++;
                $('#loading').show();
                loadImage(currentKeyword, currentPage, function(){ $('#loading').hide(); });
            }
        });

        $(document).on('click', '.img-thumb img', function(){
            if($(this).attr('data-choosen') === undefined) {
                $(this).attr('data-choosen', true).addClass('image-choosen');
            } else {
                $(this).removeAttr('data-choosen').removeClass('image-choosen');
            }

            if($('.img-thumb img[data-choosen="true"]').length == 0) {
                $('#insert-btn-image').attr('disabled', 'disabled');
            } else {
                $('#insert-btn-image').removeAttr('disabled');
            }
        });

        $('#insert-btn-image').click(function(){
            if(window.opener === null) {
                return;
            }

            $.each($('img.image-choosen'), function(k, e){
                window.opener.triggerInsert('<figure><img src="'+$(e).attr('source-url')+'"></figure>');
            });

            $('.img-thumb img').removeAttr('data-choosen').removeClass('image-choosen');
            $('#insert-btn-image').attr('disabled', 'disabled');
        });
    });
</script>
<div class="row" style="padding: 10px;">
    <div class="form-group form-inline" style="position: fixed; top: 0; width: 100%; background-color: #FFF; padding: 10px; z-index: 99999;">
        <form method="post" style="float: left;">
            <input type="text" name="keyword" placeholder="キーワードを入力" class="form-control" />
            <input type="submit" value="検索" id="search-btn" class="btn-default form-control" />
        </form>
        <button class="btn btn-success" id="insert-btn-image" style="float:right; margin-right: 30px;" disabled>画像を挿入</button>
        <div class="clearfix"></div>
    </div>
    <table id="img-thumb-list" style="border: none; margin-top: 100px;" border="0"></table>
    <div id="loading"><img src="{{ asset('res/img/loading.gif') }}" /></div>
</div>
@endsection
