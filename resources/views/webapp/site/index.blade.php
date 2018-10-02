@extends('webapp/layout')

@section('mainContent')
@include('webapp/components/notification')
<div class="row">
    <div style="margin-bottom: 10px;">
        <a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::SITE_CREATE) }}" class="btn btn-default">新規サイト</a>
    </div>
    <table>
    <tr>
        <th>ID</th>
        <th>サイト名</th>
        <th>サイトAPI</th>
        <th>ステータス</th>
        <th>編集</th>
    </tr>
    @foreach($sites as $site)
    <tr>
        <td>{{ $site->id }}</td>
        <td><a href="{{ $site->site_url }}">{{ $site->name }}</a></td>
        <td>{{ $site->api_url }}</td>
        <td>{!!  ($site->status == 1) ? '<span class="label label-success">アクティブ</span>' : '<span class="label label-default">保留</span>'   !!}</td>
        <td>
            <div class="btn-toolbar" role="toolbar" aria-label="Action button group toolbar">
                <div class="btn-group" role="group" aria-label="Action group button">
                    @if($site->status == 1)
                    <a href="{{ action('Webapp\SiteController@getStatus', ['disable', $site->id]) }}"><span class="label label-danger">保留</span></a>
                    @else
                    <a href="{{ action('Webapp\SiteController@getStatus', ['enable', $site->id]) }}"><span class="label label-success">アクティブ</span></a>
                    @endif
                    <a href="{{ action('Webapp\SiteController@getEdit', $site->id) }}"><span class="label label-warning">編集</span></a>
                </div>
            </div>
        </td>
    </tr>
    @endforeach
    </table>
</div>
@endsection