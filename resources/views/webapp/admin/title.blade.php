@extends('webapp/layout')
@section('mainContent')
@include('webapp/components/notification')
@include('webapp/components/admin_check_tab')

<div class="row">
    <div class="table-responsive">
        {!! $posts_grid !!}
        @if($counter == 0)
            <div class="alert alert-warning">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                リンクがない公開記事がありません
            </div>
        @endif
    </div>
</div>
@endsection