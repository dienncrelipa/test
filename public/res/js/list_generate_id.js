var list_link_id = ['https://px.a8.net', 
                    'https://track.affiliate-b.com',
                    'https://mens-dream.net',
                    'https://link-a.net'
                    ];
function generate_id_link(url){
    var aff = '';
    $.each(list_link_id, function(i, v){
        if(url.indexOf(v) >= 0){
            aff = 'aff-p__POSTID__-b';
            return false;
        }
    });
    return aff;
}
function index_for_link(){
    $('a.btn-index').each(function(i, elm){
        var url_elm = $(elm).attr('href');
        var id = generate_id_link(url_elm);
        var site_url = $('#cmsTargetSitesList option:selected').attr('site-url').replace(/\/+$/g, '');
        if(site_url != '' && url_elm.indexOf(site_url) === 0 && url_elm.indexOf('&f=') < 0){
            $(elm).attr('href', url_elm + '&f=__POSTID__');
        }
        if(id != ''){
            $(elm).attr('id', id + (i + 1));
        }else{
            $(elm).attr('id', 'aff-p__POSTID__-b' + (i + 1));
        }
    });
}