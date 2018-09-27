<select name="filter-log[filters][type-eq]" class="form-control" id="filter-activity-log" title="activity type">
    <option data-type="all" value="all" data-url="{{ action('Webapp\\LogViewerController@getIndex').'/inde  x/all' }}">All</option>
    <option data-type="published" value="published" data-url="{{ action('Webapp\\LogViewerController@getIndex').'/index/published' }}">Published</option>
    <option data-type="draft" value="draft" data-url="{{ action('Webapp\\LogViewerController@getIndex').'/index/draft' }}">Published to Draft</option>
    <option data-type="copy" value="copy" data-url="{{ action('Webapp\\LogViewerController@getIndex').'/index/copy' }}">Copy</option>
    <option data-type="changeSite" value="changeSite" data-url="{{ action('Webapp\\LogViewerController@getIndex').'/index/changeSite' }}">Changed target site</option>
    <option data-type="autoSaveFailed" value="autoSaveFailed">Auto save failed</option>
    <option data-type="PostToTargetSiteFailed" value="PostToTargetSiteFailed">Post to taget site failed</option>
</select>
<script>
    (function(){
        var selected = "{{ isset(Request::all()['filter-log']['filters']['type-eq']) ? Request::all()['filter-log']['filters']['type-eq']: 'all' }}";
        $('option[data-type="'+selected+'"]').prop('selected', true);
    })();
</script>
