<div class="notification">
    @if(isset($errorsSet))
        {{ App\Libs\MessagesContainer\ErrorHTML::render($errorsSet->next()) }}
    @endif

    @if(isset($notiMessage))
    <div class="alert alert-{{ $notiMessage->getType }}">{{ $notiMessage->getMessage() }}</div>
    @endif

    <?php $flashMessageSet = session()->pull('flashMessageSet', null); ?>
    @if($flashMessageSet != null)
    <div class="alert alert-{{ $flashMessageSet->getType() }}">
        @if($flashMessageSet->hasNext())
        {!!  $flashMessageSet->next()->getMessage() !!}
        @endif
    </div>
    @endif
</div>