PasteBin = {
    paste: function(dataObject, callback, callbackError) {
        $.ajax({
            url: "https://api.myjson.com/bins",
            type: "POST",
            data: JSON.stringify(dataObject),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function (data, textStatus, jqXHR) {
                callback(data);
            },
            error: function() {
                if(callbackError === undefined) {
                    return;
                }
                callbackError();
            }
        });
    }
};