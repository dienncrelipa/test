@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<div class="row" xmlns="http://www.w3.org/1999/html">
    <div>
        <a href="{{ action('Webapp\\ProductController@getCreateCategory') }}" class="btn btn-default">新規作成</a>
        <a href="{{ action('Webapp\\ProductController@getIndex') }}">商品</a>
    </div>
    <br />
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Action</th>
        </tr>
        @foreach($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td>{{ $category->name }}</td>
            <td>{{ $category->slug }}</td>
            <td>
                <a href="{{ action('Webapp\\ProductController@getEditCategory', $category->id) }}">
                    <span class="label label-warning">編集</span>
                </a>
                <a href="{{ action('Webapp\\ProductController@getDeleteCategory', $category->id) }}">
                    <span class="label label-danger">削除</span>
                </a>
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endsection