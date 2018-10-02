@extends('webapp/layout')

@section('mainContent')
<div class="row">
    @include('webapp/components/notification')
</div>
<div class="row">
    <form method="post" role="form">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="sitename">ユーザ名</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="ユーザ名" value="{{ $currentRequest->get('username') }}">
        </div>
        <div class="form-group">
            <label for="site_api">パースワード</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="パースワード" value="{{ $currentRequest->get('password') }}">
        </div>
        <div class="form-group">
            <label for="fullname">ライター名</label>
            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="ライター名" value="{{ $currentRequest->get('fullname') }}">
        </div>
        <div class="form-group">
            <label for="crowdworks_id">クラウドワークスID</label>
            <input type="text" class="form-control" id="crowdworks_id" name="crowdworks_id" placeholder="クラウドワークスID" value="{{ $currentRequest->get('crowdworks_id') }}">
        </div>
        <button type="submit" class="btn btn-primary">送信</button>
    </form>
</div>
@endsection