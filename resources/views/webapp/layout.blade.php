<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow" />
    <title>記事管理システム</title>
    <link rel='stylesheet' href='{{ asset('res/css/bootstrap.min.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/font-awesome.min.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/bootstrap-theme.css') }}' />
    <link rel="stylesheet" href="{{ asset('/res/redactor/redactor.min.css') }}" />
    <link rel='stylesheet' href='{{ asset('res/css/style.css') }}?v=20180123' />
    <link rel='stylesheet' href='{{ asset('res/css/button-chicoli.css') }}?v=20170918' />
    <link rel='stylesheet' href='{{ asset('res/css/cyfile.css') }}' />
    <link rel='stylesheet' href='{{ asset('res/css/animate.css') }}' />
    <link rel="stylesheet" href="{{ asset('res/css/bootstrap-datetimepicker.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/simplePagination.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/jquery-ui.css') }}" />
    <link rel="stylesheet" href="{{ asset('/res/css/jquery.tagit.css') }}" />
    <script>var twitterIframeUrl = '{{ action('TwitterController@getIndex') }}';</script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.form.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/jquery.simplePagination.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/moment.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/custom-counter.js') }}'></script>
    <script type='text/javascript' src='{{ asset('res/js/list_generate_id.js') }}?v=20170707'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/redactor.js') }}?v=20170828'></script>
    <script type='text/javascript' src='{{ asset('res/redactor/counter.js') }}?v=201803161720'></script>
    <script type='text/javascript' src='{{ asset('res/js/create-shortcut.js') }}'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    @yield('addonScript')
    <style>
        #check{
            background: #286090;
            color: #FAFCFD;
        }
        #btn{
            background: #C9302C;
            color: #F9E9E8;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col col-lg-10 col-lg-offset-1">
            <div class="header">
                <nav class="navbar navbar-default">
                    <ul class="nav navbar-nav list-inline">
                        @if(session('session_key', false))
                            @if(session('current_user')->is('admin'))
                                <li data-controller="site"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::SITE_LIST) }}">サイト一覧</a></li>
                                <li data-controller="user"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::USER_LIST) }}">ユーザ一覧</a></li>
                            @endif
                            <li data-controller="post"><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::POST_LIST) }}">記事一覧</a></li>
                            <li><a href="{{ \App\Http\Classes\RouteMap::get(\App\Http\Classes\RouteMap::LOG_OUT) }}">ログアウト</a></li>

                            <li id="givebtn"></li>
                        @endif
                    </ul>
                </nav>
            </div>
            <div style="margin: 5px">
                @yield('mainContent')
            </div>
        </div>
        <div class="col col-lg-2"></div>
    </div>
</div>
</body>
<script type='text/javascript' src='{{ asset('res/js/bootstrap.min.js') }}'></script>
<script type="text/javascript" src="{{ asset('/res/js/main.min.js') }}"></script>
<script>$('li[data-controller="{{ strtolower($currentControllerShort) }}"]').addClass('current');</script>
<script>
 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

 ga('create', 'UA-87689214-1', 'auto');
 ga('send', 'pageview');

</script>

<script>
    $(document).ready(function() {
      $(document).on('keypress, keydown, keyup', function (e) {
        if(e.keyCode === 8 || e.keyCode === 46) {
          $(window).trigger('resize');
        }
      });

    $('#csv-submit').click(function() {
      $.cookie('checkpost', 1, { path: '/' });
      $.cookie('rememberOrderPost', 1, { path: '/' });
      $.removeCookie('csv-download-available', { path: '/' });
      $('#csvResultGenerator .csv-url').addClass('invisible');
    });

    $('#csv_file').change(function() {
      if($(this).val() != "") {
        $('#csv-submit').removeAttr('disabled');
      } else {
        if(typeof $('#csv-submit').attr('disabled') === typeof undefined) {
          $('#csv-submit').attr('disabled', true);
        }
      }
    });

    if(typeof $.cookie('csv-download-available') !== typeof undefined) {
      $('#csvResultGenerator .csv-url').removeClass('invisible').attr('href', $.cookie('csv-download-available'));
    }

    var checkerCookie = ($.cookie('checkpost') ? parseInt($.cookie('checkpost')) : false);

    if(checkerCookie !== false) {
      $('#csvFileToogle, #csv-submit').attr('disabled', true);
      $('#downloadCsvAvailable .loading-process').removeClass('hide');
      $.get('{!! url("res/post-checker/result.json") !!}', function (data) {
        ajaxGetHeader(data);
      });
    }
  });

  function ajaxGetHeader(data) {
    var i = ($.cookie('rememberOrderPost') ? parseInt($.cookie('rememberOrderPost')) : 0);

    $.ajax({
      url: '{!! action("Webapp\\AdminController@getPostStatusChecker") !!}',
      data: { order: i },
      useDefaultXhrHeader: false,
      success: function(res, status) {
        var responseData = $.parseJSON(res);

        if($('#downloadCsvAvailable .csv-checking').length === 0) {
          var resultLoading = $('<span/>').attr('class', 'csv-checking');
          resultLoading.insertAfter($('#downloadCsvAvailable .loading-process'));
        } else {
          var resultLoading = $('#downloadCsvAvailable .csv-checking');
        }

        resultLoading.text(responseData.checking);

        if(responseData.status === -1) {
          $.removeCookie('checkpost', { path: '/' });
          $.removeCookie('rememberOrderPost', { path: '/' });
          $.cookie('csv-download-available', responseData.url, { path: '/' });
          $('#csvResultGenerator .csv-url').attr('href', responseData.url).removeClass('invisible');
          $('.loading-process, .csv-checking').addClass('hide');
          $('.csv-help').hide();
          $('#csvResultGenerator').show();
          return false;
        }

        $.cookie('rememberOrderPost', (parseInt($.cookie('rememberOrderPost')) +1), { path: '/' });
        i++;
        ajaxGetHeader(data);

      }
    });
  }
</script>
</html>
