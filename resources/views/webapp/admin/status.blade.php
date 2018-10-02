@extends('webapp/layout')
@section('mainContent')
@include('webapp/components/notification')
@if(session('check_error'))
    <div class="alert alert-danger">
        このサイトはチェック出来ませんでした : {!! session('check_error') !!}
    </div>
@endif
@include('webapp/components/admin_check_tab')
@if(!empty(session('current_user')->is('admin')))
    @if(empty($data))
        <form class="text-left form-group">
            ※1CMS複数ターゲットサイトをチェックするため、チェックボタンをクリックして下さい。
            <button type="submit" class="btn btn-primary btn-ms filter-post" name="checkOK" value="OK">チェック</button>
        </form>
    @else
        <div class="row">
            <div class="table-responsive">
                <table>
                    <thead>
                    <tr style="background-color: #1eaedb; color: white; text-align: center;">
                        <td>ID</td>
                        <td>タイトル</td>
                        <td>ライター名</td>
                        <td>ターゲットで記事ID</td>
                    </tr>
                    </thead>
                    <tbody>
                    @if(count($data))
                        @foreach($data as $value)
                            <tr>
                                <td><a href="{{action('Webapp\PostController@getEdit', $value['id'])}}"> {{$value['id']}}</a></td>
                                <td>{{$value['title']}}</td>
                                <td>{{$value['fullname']}}</td>
                                <td>{!! $value['id_post_target'] !!}</td>
                            <tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4">エラーの記事がみつかりませんでした</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif
@endsection