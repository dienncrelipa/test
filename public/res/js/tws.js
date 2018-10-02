$.twsSubmit = function(form, config, notyConfig) {
    var mainConfig = {};
    mainConfig.beforeReadData = (config.beforeReadData !== undefined) ? config.beforeReadData : $;
    mainConfig.beforeSubmit = (config.beforeSubmit !== undefined) ? config.beforeSubmit : $;
    mainConfig.beforeData = (config.beforeData !== undefined) ? config.beforeData : $;
    mainConfig.onSuccess = (config.onSuccess !== undefined) ? config.onSuccess : $;
    mainConfig.onError = (config.onError !== undefined) ? config.onError : $;
    mainConfig.afterData = (config.afterData !== undefined) ? config.afterData : $;
    $(form).submit(function(event){
        var beforeReadData = mainConfig.beforeReadData;
        var beforeSubmit = mainConfig.beforeSubmit;
        var beforeData = mainConfig.beforeData;
        var success = mainConfig.onSuccess;
        var error = mainConfig.onError;
        var afterData = mainConfig.afterData;
        event.preventDefault();

        beforeReadData();

        var url = $(this).attr('action');
        var submittedData = $(this).serializeArray().reduce(function(obj, item){
            if(item.name[item.name.length-2] == '[' && item.name[item.name.length-1]) {
                var newName = item.name.slice(0,item.name.length-2);
                if(obj[newName] === undefined) {
                    obj[newName] = [];
                }
                obj[newName].push(item.value)
            } else {
                obj[item.name] = item.value;
            }
            return obj;
        }, {});

        beforeSubmit($(form));
        $(".notification").html($('<img />').attr('src', agari.url.asset.loading_gif));
        $(this).find("input[type='submit']").hide();
        $('form input').attr('disabled', 'disabled');

        $.post(url, {session_key: session_key, data:submittedData}, function(data) {
            beforeData();
            $(".notification img").remove();
            $('form').find("input[type='submit']").show();
            if (data.data !== undefined) {
                noty($.extend({
                    timeout: 5000,
                    text: "成功しました。",
                    type: "success",
                    killer: true
                }, notyConfig));
                success(data);
                $('form input').data('change', 'none');
            } else {
                noty($.extend({
                    timeout: 5000,
                    text: data.error.message,
                    type: "error",
                    killer: true
                }, notyConfig));
                error(data);
            }

            afterData();
            $('form input').removeAttr('disabled');
        });
    });
};

$.twsError = function(message) {
    noty({
        timeout: 5000,
        text: message,
        type: "error",
        killer: true
    });
};

$.twsDelete = function(urlDelete, id, onSuccess) {
    noty({
        text: 'よろしいでしょうか。',
        type: "confirm",
        killer: true,
        buttons :  [
            {addClass: 'btn btn-default', text: 'はい', onClick: function ($noty) {
                $noty.close();
                $.post(urlDelete, {session_key: session_key, data:{id:id}}, function(data){
                    noty({
                        text: "成功しました。",
                        type: "success",
                        killer: true
                    });
                    if(onSuccess !== undefined) {
                        onSuccess();
                    }
                });
            }
            },
            {addClass: 'btn btn-danger', text: 'いいえ', onClick: function ($noty) {
                $noty.close();
            }
            }
        ]
    });
}

$.twsTableGet = function(table, page, additionData, dataCallback) {
    var urlGet = $(table).data('get');

    var noti = noty({
        layout: "topCenter",
        type: "information",
        text: "読込み中",
        killer: true,
        animation: {
            open: {height: 'toggle'},
            close: {height: 'toggle'},
            speed: 100
        }
    });

    var submitData = {session_key: session_key, page: page};
    $.extend(submitData, additionData);

    $.get(urlGet, submitData, function(data){
        setTimeout(function () {
            noti.close();
        }, 200);

        if(data.data === undefined) {
            $.twsError(data.error.message);
            return;
        }

        var listField = [];
        var actions = $(table).data('action');
        var idField = $(table).data('id-field');

        $(table).find('th').each(function(key, e){
            var name = $(e).data('name');
            if(name !== undefined) {
                listField.push($(e).data('name'));
            }
        });

        var actionDefined = {
            Edit: function(url, actionTd, value, idField) {
                var editText = "Edit";
                if($(table).data('text-edit').length > 0) {
                    editText = $(table).data('text-edit');
                }
                actionTd.append($('<a></a>').addClass('button button-short').attr('href', url+'/'+value[idField]).html(editText));
            },
            Delete: function(url, actionTd, value, idField) {
                actionTd.append($('<a></a>').addClass('button button-short').data('id',value[idField]).attr('href', 'javascript:void(0)').html('Delete').click(function(){
                    var parent = $(this);
                    $.twsDelete(url, parent.data('id'), function(){
                        parent.parents("tr").toggle("slow").remove();
                    })
                }));
            }
        }


        $(table).find('tr.table-row').remove();
        $.each(data.data, function(key, value){
            var tr = $('<tr class="table-row"></tr>');
            $.each(listField, function(k, fieldName){
                tr.append($('<td></td>').attr('data-name',fieldName).attr('data-value', value[fieldName]).html(value[fieldName]));
            });

            var actionTd = $('<td class="action"></td>');
            if(actions.length > 0) {
                $.each(actions, function (k, v) {
                    var url = $(table).data(v.toLowerCase());
                    if (actionDefined[v] !== undefined) {
                        actionDefined[v](url, actionTd, value, $(table).data('id-field'));
                    } else {
                        actionTd.append($('<a></a>').attr('data-action', v).addClass('button button-short').attr('href', url + '/' + value[idField]).data('button-action', v).html(v));
                    }
                });
                tr.append(actionTd);
            }

            $(table).append(tr);
        });

        if(dataCallback !== undefined) {
            dataCallback(data);
        }
    }).error(function(){
        $.twsError('Something wrong when load data. Please try refresh this page');
    });
};

$.twsTableManual = function(table, data) {
    var listField = [];
    var actions = $(table).data('action');
    var idField = $(table).data('id-field');

    $(table).find('th').each(function(key, e){
        var name = $(e).data('name');
        if(name !== undefined) {
            listField.push($(e).data('name'));
        }
    });

    var actionDefined = {
        Edit: function(url, actionTd, value, idField) {
            actionTd.append($('<a></a>').attr('href', url+'/'+value[idField]).html('Edit'));
        },
        Delete: function(url, actionTd, value, idField) {
            actionTd.append($('<a></a>').addClass('delete-btn').data('id',value[idField]).attr('href', 'javascript:void(0)').html('Delete').click(function(){
                var parent = $(this);
                $.twsDelete(url, parent.data('id'), function(){
                    parent.parents("tr").toggle("slow").remove();
                })
            }));
        }
    }


    $(table).find('tr.table-row').remove();
    $.each(data.data, function(key, value) {
        var tr = $('<tr class="table-row"></tr>');
        $.each(listField, function (k, fieldName) {
            tr.append($('<td></td>').html(value[fieldName]));
        });

        var actionTd = $('<td class="action"></td>');
        $.each(actions, function (k, v) {
            var url = $(table).data(v.toLowerCase());
            if (actionDefined[v] !== undefined) {
                actionDefined[v](url, actionTd, value, $(table).data('id-field'));
            } else {
                actionTd.append($('<a></a>').attr('href', url + '/' + value[idField]).html(v));
            }
            actionTd.append(" | ");
        });

        tr.append(actionTd);
        $(table).append(tr);
    });
};

$.twsDownloadCsv = function(table) {
    var csv = $(table).table2CSV({delivery:'value'});
    window.location.href = 'data:text/csv;charset=UTF-8,'+ encodeURIComponent(csv);
};