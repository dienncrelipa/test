@extends('webapp/layout')

@section('mainContent')
<style>
    input.btn-danger {
        color: #FFF;
    }
</style>

<div class="row">
<div class="notification">
    @if(isset($successfulUsers))
        @foreach($successfulUsers as $user)
        <div class="alert alert-success"><b>{{ $user }}</b>：ユーザを作成しました</div>
        @endforeach
    @endif
    @if(isset($errorsSet))
        @foreach($errorsSet as $username => $error)
        <div class="alert alert-danger"><b>{{ $username  }}</b>：ユーザを作成できませんでした：{{ $error }}</div>
        @endforeach
    @endif

    @if(isset($notiMessage))
        <div class="alert alert-{{ $notiMessage->getType }}">{{ $notiMessage->getMessage() }}</div>
    @endif

    <?php $flashMessageSet = session()->pull('flashMessageSet', null); ?>
    @if($flashMessageSet != null)
        <div class="alert alert-{{ $flashMessageSet->getType() }}">
            @if($flashMessageSet->hasNext())
                {{ $flashMessageSet->next()->getMessage() }}
            @endif
        </div>
    @endif
</div>
</div>
<div class="row">
    <form method="post" role="form">
        {{ csrf_field() }}
        <table id="user-table">
            <thead>
                <tr>
                    <th>ユーザ名</th>
                    <th>パスワード</th>
                    <th>ライター名</th>
                    <th>クラウドワークスID</th>
                    <th>権限</th>
                    <th style="min-width: 10px">削除</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <div class="pull-right">
                            <span class="add-user-btn">
                                <input type="button" id="add-user-row" class="btn btn-default" value="追加">
                            </span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>

        <button type="submit" class="btn btn-primary">送信</button>
        <a href="javascript:;" class="btn btn-success" onclick="$('#csv_file').trigger('click')">CSVインポート</a>
        <span>※CSVファイル構築→ユーザ名・パスワード・ライター名・クラウドワークスID・権限</span>
        <input type="file" id="csv_file" style="display: none;" />
    </form>
</div>
<div style="display: none" id="hidden-row">

</div>
<script type="text/javascript">
    addMoreUser = function(data) {
        var sampleData = {
            username: '',
            password: '',
            fullname: '',
            crowdwork: '',
            role: 'editor',
        };

        var mainData = Object.assign(sampleData, data);

        $('#user-table tbody').append('<tr>'
                +'<td><input type="text" class="form-control" name="username[]" placeholder="ユーザ名" value="'+mainData.username+'"></td>'
                +'<td><input type="password" class="form-control" name="password[]" placeholder="パスワード" value="'+mainData.password+'"></td>'
                +'<td><input type="text" class="form-control" name="fullname[]" placeholder="ライター名" value="'+mainData.fullname+'"></td>'
                +'<td><input type="text" class="form-control" name="crowdworks_id[]" placeholder="例：1234567" value="'+mainData.crowdwork+'"></td>'
                +'<td>'
                +'<select name="role[]" class="form-control">'
                +'<option value="admin" '+((mainData.role=="admin") ? "selected" : "")+'>管理者</option>'
                +'<option value="editor" '+((mainData.role=="editor" || mainData.role.length == 0) ? "selected" : "")+'>エディター</option>'
                +'<option value="contributor" '+((mainData.role=="contributor") ? "selected" : "")+'>コントリビューター</option>'
                +'<option value="rewriter" '+((mainData.role=="rewriter") ? "selected" : "")+'>リライター</option>'
                +'<option value="rewriter2" '+((mainData.role=="rewriter2") ? "selected" : "")+'>リライター2</option>'
                +'</select>'
                +'</td>'
                +'<td>'
                +'<span class="del-user-btn">'
                +'<input type="button" class="btn-danger" value="削除">'
                +'</span>'
                +'</td>'
                +'</tr>');
    };


    function processData(allText) {
        var allTextLines = allText.split(/\r\n|\n|\r/);
        var headers = allTextLines[0].split(',');
        var lines = [];

        for (var i=1; i<allTextLines.length; i++) {
            var data = allTextLines[i].split(',');
            if (data.length == headers.length) {

                var tarr = [];
                for (var j=0; j<headers.length; j++) {
                    tarr.push(data[j]);
                }
                lines.push(tarr);
            }
        }

        return lines;
    }

    $(document).ready(function () {
        $('#add-user-row').click(function() {
            addMoreUser({});
        });
        $('#user-table').on('click', '.del-user-btn', function(event){
            $( event.target ).closest('tr').remove();
        });

        $('#csv_file').change(function(){
            var file = this.files[0];
            var fr = new FileReader();
            fr.onload = function(e) {
                var data = processData(e.target.result);
                $.each(data, function(k, v){
                    addMoreUser({
                        username: v[0],
                        password: v[1],
                        fullname: v[2],
                        crowdwork: v[3],
                        role: v[4]
                    });
                });
            };
            fr.readAsText(file);

        });
    });

</script>
@endsection