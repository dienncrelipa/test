<?php

namespace App\Models;
use DB;

class Keywords extends BaseModel {
  public $table = 'keywords';

  public static function getKeyword($keyword, $type) {
    return self::whereRaw('BINARY keyword = ?', [$keyword])->where('type', $type)->first();
  }


  public static function mainKeywordList($type = 0) {
    $keywords = [];
    $getKw = self::where('type', $type)->get();
    foreach ($getKw as $item) {
      $keywords[$item->id] = $item->keyword;
    }
    
    return $keywords;
  }

  public static function getFilterKeyword($txt_search = '', $id_user = 0 , $page = 0, $user_role = 'admin'){
      $_where = array(['type', 1]);
      if($txt_search != ''){
          $txt_search = addslashes($txt_search);
          array_push($_where, ['keyword', 'LIKE', "%$txt_search%"]);
      }
      if(is_numeric($id_user) && $user_role != 'admin'){
          array_push($_where, ['posts.is_delete', '0']);
          if($user_role != 'rewriter' && $user_role != 'rewriter2'){
              array_push($_where, ['posts.user_id', $id_user]);
          }
          return Keywords::select('keywords.id', 'keywords.keyword as value')
              ->leftJoin('post_meta', 'post_meta.keyword_id', '=', 'keywords.id')
              ->leftJoin('posts', 'posts.id', '=', 'post_meta.post_id')
              ->where($_where)
              ->orderBy('keywords.id', 'desc')
              ->paginate(10, ['*'], 'page', $page)
              ->toArray();
      }
      return Keywords::select('keywords.id', 'keywords.keyword as value')
                    ->where($_where)
                    ->orderBy('keywords.keyword', 'asc')
                    ->paginate(10, ['*'], 'page', $page)
                    ->toArray();
  }
}
