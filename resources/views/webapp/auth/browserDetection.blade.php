<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset='utf-8' />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>記事管理システム</title>
    <link rel='stylesheet' href='{{ asset('res/css/bootstrap.min.css') }}' />
    <style>
        div.panel{
            position: fixed;
            top: 50%;
            left: 50%;
            width: 40em;
            margin-top: -9em;
            margin-left: -20em;
        }
    </style>
</head>
    <div class="row">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h1 class="panel-title" style="font-weight: bold">【注意】</h1>
            </div>
            <div class="panel-body">
                【注意】あなたのブラウザは「<b style="color: #1a92c2">{{ $browser_name }}</b>」です。<br/>
                当サイトでは<b style="color: #1a92c2">GoogleChrome</b>のブラウザを推奨しています。<br/>
                「こちらよりダウンロード(<a href="https://www.google.com/chrome/browser/desktop/">Google Chrome</a>)」してお使いください。
            </div>
        </div>
    </div>
</body>
</html>
