<?php

namespace App\Models;
use DB;

class PostMeta extends BaseModel {
  public $table = 'post_meta';

  public static function getPostMeta($pid, $kwid) {
    return self::where('post_id', $pid)->where('keyword_id', $kwid)->first();
  }

  public static function getPostMetaByPostId($pid) {
    $pmt = DB::table('post_meta')
      ->select('post_meta.id as pmid', 'keywords.id as kwid', 'keyword', 'type')
      ->join('keywords', 'post_meta.keyword_id', '=', 'keywords.id')
      ->where('post_meta.post_id', '=', $pid)
      ->get();

    return $pmt;
  }

  public static function deletePostMetaByPostId($pid) {
    $response = DB::table('post_meta')
      ->where('post_id', '=', $pid)
      ->delete();

    return $response;
  }
  public static function getMainKeywordByPostId($pid) {
      return DB::table('post_meta')
        ->select('keywords.id as kwid')
        ->join('keywords', 'post_meta.keyword_id', '=', 'keywords.id')
        ->where('post_meta.post_id', '=', $pid)
        ->where('keywords.type', '=', '1')
        ->first();
  }
}
