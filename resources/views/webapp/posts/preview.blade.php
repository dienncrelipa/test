@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<style>{{ $additionCss }}</style>
<div class="row">
    <div>
        <a href="{{ action('Webapp\\PostController@getEdit', $post->id) }}">編集へ戻る</a>
        @if($post->canPublishBy(session('current_user')))
         | <a href="{{ action('Webapp\\PostController@getPublish', $post->id).'?from=preview' }}">公開</a>
        @endif
        | <a href="http://quirktools.com/screenfly/#u={{ urlencode(action('Webapp\\PostController@getPreview', $post->id).'?raw') }}&w=375&h=667&a=37&s=1" target="_blank">モバイルプレビューへ</a>
    </div>
    <br/>
    <h2>{{ $post->title }}</h2>
    <h3>{{ $post->description }}</h3>
    <div class="content redactor-editor">{!! $post->content !!}</div>
</div>
@endsection