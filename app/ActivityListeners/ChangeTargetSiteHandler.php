<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/16/17
 * Time: 8:30 AM
 */

namespace App\ActivityListeners;


use App\Models\Activity;
use App\Models\Site;
use Illuminate\Support\Facades\Log;

class ChangeTargetSiteHandler implements BaseHandler
{

    public static function event()
    {
        return ['PostController@postEditAjax'];
    }

    public function handle(Activity $activity)
    {
        if(isset($activity->meta['ChangeTargetSiteFailed'])) {
            return 'change target site failed '.$activity->meta['ChangeTargetSiteFailed'][0];
        }

        $previousSiteId = $activity->meta['PreviousSiteId'][0];
        $newSiteId = $activity->meta['NewSiteId'][0];

        if($previousSiteId == $newSiteId || empty($previousSiteId)) {
            return false;
        }

        $previousSiteName = !empty($tmpSite = Site::find($previousSiteId)) ? $tmpSite->name : $previousSiteId;
        $newSiteName = !empty($tmpSite = Site::find($newSiteId)) ? $tmpSite->name : $newSiteId;

        return "changed target site from `{$previousSiteName}` to `{$newSiteName}`";
    }
}