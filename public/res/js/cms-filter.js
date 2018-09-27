var filter_list = new Object();
var saveFilter = function(cls, id, data){
    if(data.length) {
        var value = data[0].id;
        var text = data[0].value;
        filter_list[cls] = {id: id, value: value, text: text}
    }else{
        delete filter_list[cls];
    }
}

var addFilter = function(is_filter){
    if(getCookie('filter_data') == '') return;
    var filter_data = JSON.parse(getCookie('filter_data'));
    if(is_filter && !$.isEmptyObject(filter_data)){
        filter_list = filter_data;
        $.each(filter_data, function(index, value) {
            $(index + ' .select2-selection__rendered').html(value.text);
        });
    }else{
        setCookie('filter_data','', 1);
    }
};
var addUserFilter = function(is_filter){
    if(getCookie('filter_user_data') == '') return;
    var filter_user_data = JSON.parse(getCookie('filter_user_data'));
    if(is_filter && !$.isEmptyObject(filter_user_data)){
        filter_list = filter_user_data;
        $.each(filter_user_data, function(index, value) {
            $(index + ' .select2-selection__rendered').html(value.text);
        });
    }else{
        setCookie('filter_user_data','', 1);
    }
};
var addLogFilter = function(is_filter){
    if(getCookie('filter_log_data') == '') return;
    var filter_user_data = JSON.parse(getCookie('filter_log_data'));
    if(is_filter && !$.isEmptyObject(filter_user_data)){
        filter_list = filter_user_data;
        $.each(filter_user_data, function(index, value) {
            $(index + ' .select2-selection__rendered').html(value.text);
        });
    }else{
        setCookie('filter_log_data','', 1);
    }
};
$(document).ready(function () {

    var remotePidConfig = function (field){
        return ({
            ajax: {
                url: "/ajax/nayjest-filter-search",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        s: params.term,
                        page: params.page,
                        field: field
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    if(params.page == 1){
                        data.data.unshift({id : '-1', value: '--//--'});
                    }

                    return {
                        results: data.data,
                        pagination: {
                            more: (params.page * data.per_page) < data.total
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; },
            minimumInputLength: 0,
            templateResult: function (data) {
                return data.value;
            },
            templateSelection: function (data) {
                if (data.id === '') {
                    return '--//--';
                }
                return data.value;
            },
            theme: "bootstrap",
            placeholder: "",
            dropdownCssClass: 'filter-drop',
            language: {
                loadingMore: function () {
                    return "...";
                },
                noResults: function () {
                    return "該当結果が見つかりませんでした";
                },
                errorLoading: function () {
                    return "データを取得できませんでした";
                }
            }
        })
    };

    $('select#filter-id-eq').select2(remotePidConfig('pid'));
    $('select#filter-id-eq').change(function(){
        saveFilter('td.column-id', 'filter_pid', $(this).select2('data'));
    });

    $('select#filter-fullname-eq').select2(remotePidConfig('user'));

    $('select#filter-fullname-eq').change(function(){
        saveFilter('td.column-fullname', 'filter_user', $(this).select2('data'));
    });

    $('select#filter-keyword_fil-eq').select2(remotePidConfig('kw'));
    $('select#filter-keyword_fil-eq').change(function(){
        saveFilter('td.column-keyword','filter_kw', $(this).select2('data'));
    });

    $('select#filter-site_id-eq').select2(remotePidConfig('site'));
    $('select#filter-site_id-eq').change(function(){
        saveFilter('td.column-name', 'filter_site', $(this).select2('data'));
    });
    $('button.filter-post').click(function(){
        setCookie('filter_data', JSON.stringify(filter_list), 1);
        $('td select').each(function (i,elm) {
            if($(this).val() == -1){
                $(this).attr('disabled', true);
            }
        });
    });

    $('.is-delete').parents('tr').addClass('post-delete');

    $('select#filter-id-eq[name*="filter-user"]').select2(remotePidConfig('user_id'));
    $('select#filter-id-eq[name*="filter-user"]').change(function(){
        saveFilter('td.column-id', 'filter_user_id', $(this).select2('data'));
    });

    $('select#filter-username-eq[name*="filter-user"]').select2(remotePidConfig('user_name'));
    $('select#filter-username-eq[name*="filter-user"]').change(function(){
        saveFilter('td.column-username', 'filter_user_name', $(this).select2('data'));
    });

    $('select#filter-crowdworks_id-eq[name*="filter-user"]').select2(remotePidConfig('user_crowdworks'));
    $('select#filter-crowdworks_id-eq[name*="filter-user"]').change(function(){
        saveFilter('td.column-crowdworks_id', 'filter_user_crowdwork', $(this).select2('data'));
    });

    $('select#filter-role-eq[name*="filter-user"]').select2(remotePidConfig('user_role'));
    $('select#filter-role-eq[name*="filter-user"]').change(function(){
        saveFilter('td.column-role', 'filter_user_role', $(this).select2('data'));
    });

    $('button.filter-user').click(function(){
        setCookie('filter_user_data', JSON.stringify(filter_list), 1);
        $('td select').each(function (i,elm) {
            if($(this).val() == -1){
                $(this).attr('disabled', true);
            }
        });
    });

    $('select#filter-post-id-eq[name*="filter-log"]').select2(remotePidConfig('log_post_id'));
    $('select#filter-post-id-eq[name*="filter-log"]').change(function(){
        saveFilter('td.column-message', 'filter_column_message', $(this).select2('data'));
    });

    $('select#filter-username-eq[name*="filter-log"]').select2(remotePidConfig('log_user_name'));
    $('select#filter-username-eq[name*="filter-log"]').change(function(){
        saveFilter('td.column-username', 'filter_log_username', $(this).select2('data'));
    });

    $('select#filter-activity-log').change(function(){
        saveFilter('td.column-message', 'filter_log_message', $(this).select2('data'));
    });

    $('button.filter-log').click(function(){
        setCookie('filter_log_data', JSON.stringify(filter_list), 1);
        $('td select').each(function (i,elm) {
            if($(this).val() == -1){
                $(this).attr('disabled', true);
            }
        });
    });

});
