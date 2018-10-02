@extends('webapp/layout')

@section('mainContent')
<div class="row">
    <div><a href="{{ action('Webapp\\GooglePhotoController@getRenew') }}">Googleアカウント設定</a></div>
    <div>
        <p>ID: <strong>{{ $id }}</strong></p>
        <p>メール: <strong>{{ $email }}</strong></p>
    </div>
</div>
@endsection