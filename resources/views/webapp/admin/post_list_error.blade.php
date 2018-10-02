@extends('webapp/layout')
@section('mainContent')
    @include('webapp/components/notification')
    @include('webapp/components/admin_check_tab')
    @if(!empty(session('current_user')->is('admin')))
        <div class="row">
            <div class="button-download">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-default" style="border-radius: 0">Back</a>
            </div>
            <div class="table-responsive">
                {!! $posts_grid !!}
            </div>
        </div>
    @endif
@endsection