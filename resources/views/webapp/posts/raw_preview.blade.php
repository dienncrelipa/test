<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>記事管理システム</title>
    <link rel='stylesheet' href='{{ asset('res/css/bootstrap.min.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/bootstrap-theme.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/style.css') }}?v=20180123' />
    <link rel='stylesheet' href='{{ asset('res/css/cyfile.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/animate.css') }}' />
    <link rel="stylesheet" href="{{ asset('res/css/bootstrap-datetimepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/simplePagination.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/redactor/redactor.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/jquery-ui.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/jquery.tagit.css') }}" />
    <script>var twitterIframeUrl = '{{ action('TwitterController@getIndex') }}';</script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.form.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.simplePagination.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/moment.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/redactor.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/counter.js') }}?v=201803161720'></script>
    <style>{{ $additionCss }}</style>
    <style>
        .redactor-editor {
            border: none;
            padding: 0px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col col-lg-8 col-lg-offset-2">
            <div style="margin: 5px">
                <h2>{{ $post->title }}</h2>
                <h3>{{ $post->description }}</h3>
                <div class="content redactor-editor">{!! $post->content !!}</div>
            </div>
        </div>
        <div class="col col-lg-2"></div>
    </div>
</div>
</body>
<script type='text/javascript' src='{{ asset('res/js/bootstrap.min.js') }}'></script>
<script type="text/javascript" src="{{ asset('/res/js/main.min.js') }}"></script>
</html>