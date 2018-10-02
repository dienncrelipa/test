@extends('webapp/layout')

@section('addonScript')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.4/ace.js"></script>
<script>
    $(function(){
        window.editor = ace.edit("editor");
        window.editor.setTheme("ace/theme/tomorrow");
        window.editor.getSession().setMode("ace/mode/css");
    });

</script>
<style type="text/css" media="screen">
    #editor {
        height: 500px;
    }
</style>
@endsection
@section('mainContent')
<script>
    $(function(){
        $('form').submit(function(){
            $('#editor-txt').val(window.editor.getValue());
        });
    });
</script>
@include('webapp/components/notification')
<div class="row">
    <h3>{{ $site->name }}用のCSSエディター</h3>
    @if(!empty($targetCssUrl))<p>ターゲットサイトでのCSS：<a target="_blank" href="{{ $targetCssUrl }}">{{ $targetCssUrl }}</a></p><br />@endif
    <div id="editor">{{ $css }}</div>
    <br />
    <form method="post" action="">
        {{ csrf_field() }}
        <textarea id="editor-txt" name="content-css" style="display: none;"></textarea>
        <input type="submit" value="送信" class="btn btn-success" />
    </form>
</div>
@endsection