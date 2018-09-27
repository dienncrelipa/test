<!DOCTYPE html>
<html>
<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'></script>
<body style="visibility: hidden">
{!! $html !!}
<script>
var caller, _timer, loaded = false;
var saveCaller = function(e) {
    if (e.data.element) {
        caller = e;
        if (loaded)
            sendResponse();
    }
};
if (window.addEventListener)
    window.addEventListener("message", saveCaller, false);
else
    window.attachEvent("onmessage", saveCaller);

function onload() {
    if (arguments.callee.done)
        return;
    arguments.callee.done = true;

    if (_timer)
        clearInterval(_timer);

    loaded = true;
    if (caller)
        sendResponse();

    autoUpdate();
}

if (document.addEventListener)
    document.addEventListener("DOMContentLoaded", onload, false);
else if (/WebKit/i.test(navigator.userAgent)) {
    _timer = setInterval(function() {
        if (/loaded|complete/.test(document.readyState))
            onload();
    }, 10);
}
else
    window.onload = onload;

function sendResponse() {
    if (caller.data.query == "height") {
        if($(document.body).height() < 100) return;
        setTimeout(function() {
            caller.source.postMessage({ element: caller.data.element,
                height: $(document.body).height() + 20 }, caller.origin);
        }, 200);
    }
}

function autoUpdate() {
    setInterval(function(){
        if($(document.body).height() < 100) return;
        caller.source.postMessage({ element: caller.data.element,
            height: $(document.body).height() + 20 }, caller.origin);
    }, 1000);
}
</script>
</body>
</html>