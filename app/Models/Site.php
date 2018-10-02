<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/9/16
 * Time: 9:34 AM
 */

namespace App\Models;


use Illuminate\Support\Facades\DB;

class Site extends BaseModel
{
    public $table = 'sites';

    public function getAdditionCss() {
        $css = '';
        foreach(glob(storage_path("css/{$this->id}_*.css")) as $cssFile) {
            $css .= file_get_contents($cssFile)."\n";
        }

        return $css;
    }

    public function getApiKey() {
        parse_str(parse_url($this->api_url)['query'], $output);

        return $output['key'];
    }

    public static function getFilterSite($txt_search = '' , $id_user = 0, $page = 0, $user_role = 'admin') {
        $total = 0;
        $data = array();

        $allSites = Post::getSiteListOfPost($txt_search,$id_user, $page, $user_role);
        if($allSites) {
            foreach ($allSites as $s) {
                $data[] = array(
                    'id' => 'SITE-' . $s->id,
                    'value' => $s->name
                );
            }
            $total += $allSites->total();
        }
        $targetPostList = Post::getTargetPostList($txt_search, $id_user, $page, $user_role);
        $data = array_merge($data, $targetPostList['data']);
        $total += $targetPostList['total'];
        return array(
            'per_page' => 10,
            'total' => $total,
            'data' => $data
        );
    }
}