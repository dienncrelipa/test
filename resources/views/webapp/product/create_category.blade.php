@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<script>
    $(function(){
        $('form').submit(function(event){
            event.preventDefault();

            $.post('{{ action('Webapp\\ProductController@postCreateCategory') }}', $(this).serialize(), function(data){
                if(data.error !== undefined) {
                    $('.notification').html($('<div class="alert alert-danger">'+data.error.message+'</div>'));
                    $(window).scrollTop(0);
                    return;
                }

                window.location.href = '{{ action('Webapp\\ProductController@getCategory') }}';
            });
        });
    });

</script>
<div class="row">
    <form method="post">
        {{ csrf_field() }}
        <div class="form-group">
            <label>Name</label>
            <input type="text" class="form-control" name="name" />
        </div>
        <div class="form-group">
            <label>Slug</label>
            <input type="text" class="form-control" name="slug" />
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-warning" value="送信">
        </div>
    </form>
</div>
@endsection