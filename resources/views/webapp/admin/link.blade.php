@extends('webapp/layout')
@section('mainContent')
@include('webapp/components/notification')
@include('webapp/components/admin_check_tab')
@if(!empty(session('current_user')->is('admin')))
    <div class="row">
        <div class="button-download">
            <span>※全部のターゲットサイトで記事重複をチェックするため、エクスポートCSV機能を選択して下さい。</span>
            <a href="{{ URL::to("exportCsvLinkTarget") }}">
                <span class="btn-download-text">CSV DOWNLOAD</span>
                <span class="ion-android-download"></span>
            </a>
        </div>
        <div class="table-responsive">
            {!! $posts_grid !!}
            @if($counter == 0)
                <div class="alert alert-warning">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    エラーの記事がみつかりませんでした
                </div>
            @endif
        </div>
    </div>
@endif
@endsection