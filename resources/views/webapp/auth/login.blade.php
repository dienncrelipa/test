@extends('webapp/layout')

@section('mainContent')
<div class="row" style="margin-top: 20px;">
    <div class="col col-lg-offset-4 col-lg-4">
        <div class="row">
            @include('webapp/components/notification')
        </div>
        <div class="row">
            <form method="post" role="form">
                {{ csrf_field() }}
                <div class="form-group">
                    <label for="username">ユーザ名</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="ユーザ名">
                </div>
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="パスワード">
                </div>
                <button type="submit" class="btn btn-default">ログイン</button>
            </form>
        </div>
    </div>
</div>
@endsection