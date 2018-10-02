<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow" />
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
    <link rel='stylesheet' href='{{ asset('res/css/button-chicoli.css') }}?v=20170918' />
    <script type='text/javascript' src='{{ asset('res/js/jquery.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.form.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.simplePagination.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/moment.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/redactor.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/counter.js') }}?v=201803161720'></script>
    @yield('addonScript')
</head>
<body>
<div class="container-fluid">
    @yield('mainContent')
</div>
</body>
<script type='text/javascript' src='{{ asset('res/js/bootstrap.min.js') }}'></script>
<script type="text/javascript" src="{{ asset('/res/js/main.min.js') }}"></script>
</html>
