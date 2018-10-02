@extends('webapp/layout')

@section('mainContent')
<div class="row">
    @include('webapp/components/notification')
</div>
<div class="row">
    <form method="post" role="form">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="site_name">サイト名</label>
            <input type="text" class="form-control" id="site_name" name="site_name" placeholder="サイト名">
        </div>
        <div class="form-group">
            <label for="site_url">サイトURL</label>
            <input type="text" class="form-control" id="site_url" name="site_url" placeholder="サイトURL">
        </div>
        <div class="form-group">
            <label for="site_api">サイトAPI URL</label>
            <input type="text" class="form-control" id="site_api" name="site_api" placeholder="サイトAPI URL">
        </div>
        <div class="form-group">
            <label for="">カテゴリ一覧</label>
        </div>

        <button type="submit" class="btn btn-primary">送信</button>
    </form>
</div>
@endsection