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
  @include('webapp/components/notification')
  <div class="row">
  </div>
  <div class="row">
    <form method="post" role="form">
      {{ csrf_field() }}
      <div class="form-group">
        <label for="sitename">ユーザ名</label>
        <input type="text" class="form-control" id="username" name="username" placeholder="ユーザ名" value="{!! $user->username !!}" disabled>
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
        <input type="text" class="form-control" id="fullname" name="fullname" placeholder="ライター名" value="{!! $user->fullname !!}">
      </div>
      <div class="form-group">
        <label for="crowdworks_id">クラウドワークスID</label>
        <input type="text" class="form-control" id="crowdworks_id" name="crowdworks_id" placeholder="クラウドワークスID" value="{!! $user->crowdworks_id !!}">
      </div>
      @if($user->id !== $current_user->id)
      <div class="form-group">
        <label for="">権限</label>
        <select name="role" class="form-control">
          <option value="admin" @if($user->role == 'admin') selected @endif>管理者</option>
          <option value="editor" @if($user->role == 'editor') selected @endif>エディター</option>
          <option value="contributor" @if($user->role == 'contributor') selected @endif>コントリビューター</option>
          <option value="rewriter" @if($user->role == 'rewriter') selected @endif>リライター</option>
          <option value="rewriter2" @if($user->role == 'rewriter2') selected @endif>リライター2</option>
        </select>
      </div>
      @else
        <input type="hidden" class="form-control" id="role" name="role" placeholder="ユーザ名" value="{!! $user->role !!}">
      @endif

      <button type="submit" class="btn btn-primary">送信</button>
    </form>
  </div>
@endsection