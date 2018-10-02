<html>
<head>

</head>
<body>
<style>
    body {
        margin: 0;
        padding: 0;
    }
    .topbar {
        height: 30px;
        padding-left: 20px;
        background-color: #333;
        padding-top: 3px;
    }
    .topbar a {
        text-decoration: none;
        color: #EEE;
        padding-right: 20px;
    }
</style>

<div class="topbar">
    @if(!$isMobile)
    <a href="http://quirktools.com/screenfly/#u={{ urlencode(action('Webapp\\PostController@getPreviewIframe', $previewSession).'?mobile=true') }}&w=375&h=667&a=37&s=1" target="_blank">モバイルプレビューへ</a>
    @else
    <a href="#">モバイルプレビューへ</a>
    @endif
</div>
<div class="iframe">
    <iframe src="{{ $previewUrl }}" style="position:fixed; top:30px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;"></iframe>
</div>
</body>
</html>