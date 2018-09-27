@extends('webapp/layout')

@section('addonScript')
    <style>
        .word-count {
            float: left;
            margin-right: 20px;
        }
        .btnadmin a{
            color: white;
        }
        .btnadmin a:hover{
            color: white;
            text-decoration: none;
        }
        table .tr-list-post1{
            background-color: #1EAEDB;
            color: white;
            height: 60px;
            text-align: center;
            line-height: 40px;
        }
        table .tr-list-post2{
            background-color: #1EAEDB;
            color: white;
            text-align: center;
            line-height: 45px;
        }

    </style>

@stop
@section('mainContent')
    @include('webapp/components/notification')
    @include('webapp/components/admin_check_tab')
    @if(!empty(session('current_user')->is('admin')))
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="drafted">
                <form method="post" enctype="multipart/form-data" action="{{ action('Webapp\\AdminController@postUpCsv') }}" id="csvCheckerFrm" class="form-group">
                    <label>※ターゲットサイトで公開するかどうかチェックする。 記事リストをインポートして下さい。</label>
                    <input type="file" id="csv_file" name="csv_file"/>
                    <input id="csv-submit" type="submit" value="チェック" class="btn btn-primary btn-ms filter-post" disabled/>
                </form>
                <a class="csv-help" href="https://drive.google.com/file/d/0B-baFcveMC1oeTk4eC1SOTFMOXM/view?usp=sharing">※サンプルファイルを参考して下さい。</a>
            </div>
            <div id="downloadCsvAvailable">
                <div class="download-btn" id="csvResultGenerator" style="display: none">
                    <label>※記事ステータスのチェックは完了しましたので、
                    <a href="javascript:void(0);" class="csv-url invisible">
                        <label>CSV DOWNLOAD</label>
                        <span class="ion-android-download download-ico"></span>
                    </a>
                    を押して結果ファイルを確認してください</label>
                </div>
                <div class="loading-process loading loading--double hide"></div>
            </div>
        </div>
    @endif
@stop
