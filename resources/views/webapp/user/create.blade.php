@extends('webapp/layout')
@section('addonScript')
<script>
    $(document).ready(function(){
        $('form').submit(function() {
            if($('input[name="password"]').val() != $('input[name="password_confirm"]').val()) {
                $('input[name="password"], input[name="password_confirm"]').parent('.form-group').addClass('has-error');
                alert('パスワード確認が正しくありません');
                return false;
            }

            return true;
        });
    });
</script>
@endsection
@section('mainContent')
<div class="row">
    @include('webapp/components/notification')
</div>
<div class="row">
    <form method="post" role="form">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="sitename">ユーザ名</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="ユーザ名">
        </div>
        <div class="form-group">
            <label for="site_api">新しいパスワード</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="パースワード">
        </div>
        <div class="form-group">
            <label for="site_api">新しいパスワードの確認</label>
            <input type="password" class="form-control" id="password" name="password_confirm" placeholder="パースワード">
        </div>
        <div class="form-group">
            <label for="fullname">ライター名</label>
            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="ライター名">
        </div>
        <div class="form-group">
            <label for="crowdworks_id">クラウドワークスID</label>
            <input type="text" class="form-control" id="crowdworks_id" name="crowdworks_id" placeholder="クラウドワークスID">
        </div>
        <div class="form-group">
            <label for="">権限</label>
            <select name="role" class="form-control">
                <option value="admin">管理者</option>
                <option value="editor">エディター</option>
                <option value="contributor">コントリビューター</option>
                <option value="rewriter">リライター</option>
                <option value="rewriter2">リライター2</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">送信</button>
    </form>
</div>
@endsection