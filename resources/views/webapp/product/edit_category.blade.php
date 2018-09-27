@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<script>
    $(function(){
        $('form').submit(function(event){
            event.preventDefault();
            $('.notification').html('');

            $.post('{{ action('Webapp\\ProductController@postEditCategory') }}', $(this).serialize(), function(data){
                if(data.error !== undefined) {
                    $('.notification').html($('<div class="alert alert-danger">'+data.error.message+'</div>'));
                    $(window).scrollTop(0);
                    return;
                }

                $('.notification').html($('<div class="alert alert-success">OK</div>'));
            });
        });
    });

</script>
<div class="row">
    <div style="margin-bottom: 20px">
        <a href="{{ action('Webapp\\ProductController@getCategory') }}"><< カテゴリリストへ戻る</a>
    </div>
    <form method="post">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $category->id }}" />
        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" name="name" value="{{ $category->name }}" />
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input type="text" class="form-control" name="slug" value="{{ $category->slug }}" />
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-warning" value="送信">
        </div>
    </form>
</div>
@endsection