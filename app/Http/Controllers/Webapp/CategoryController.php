<?php

namespace App\Http\Controllers\Webapp;

use App\Http\Requests;
use App\Libs\CdsApiDriver;
use App\Models\Category;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Request;

class CategoryController extends NeedAuthController {

  public function getListCategories(Request $request) {
    $categories = Category::where('site_id', $request->get('id'))->get();

    $listCategories = [];
    foreach ($categories as $category) {
      $tmpObj = new \stdClass();

      $tmpObj->id = $category->id;
      $tmpObj->parent = ($category->parent == 0 ? '#' : $category->parent);
      $tmpObj->text = $category->name;

      array_push($listCategories, $tmpObj);
    }
    
    return response()->json($listCategories);
  }

  public function getCategorySync(Request $request) {
    if (empty($site = Site::find($request->get('id')))) {
      return json_encode([
        'status' => 'NG',
        'code' => '404',
        'data' => '404 Not found',
      ]);
    }

    $targetId = $request->get('id');
    Category::where('site_id', $targetId)->delete();
    $siteUrl = Site::find($targetId)->api_url;
    $dataResultOnTarget =  (new CdsApiDriver($siteUrl))->send('cathierarchical')->getResult();
    $dataCategoryOnTarget = $dataResultOnTarget->data;
    $updatepPostOfCat = isset($dataResultOnTarget->update_post_of_cat) ? $dataResultOnTarget->update_post_of_cat : false;
    $resDataCategoryOnTarget = $dataCategoryOnTarget;
    
    while (count($dataCategoryOnTarget) != 0) {
      foreach ($dataCategoryOnTarget as $key => $category) {
        $cexisted = Category::where('slug', $category->slug)->where('site_id', $targetId)->first();

        if (empty($cexisted)) {
          if ($category->parent === 0) {
            $cat = new Category();
            $cat->name = $category->text;
            $cat->slug = $category->slug;
            $cat->parent = 0;
            $cat->site_id = $request->get('id');
            $cat->save();
            unset($dataCategoryOnTarget[ $key ]);
          } else {
            $parent = Category::where('slug', $category->parent)->where('site_id', $targetId)->first();

            if (!empty($parent)) {
              $cat = new Category();
              $cat->name = $category->text;
              $cat->slug = $category->slug;
              $cat->parent = $parent->id;
              $cat->site_id = $request->get('id');
              $cat->save();
              unset($dataCategoryOnTarget[ $key ]);
            }
          }
        } else {
          unset($dataCategoryOnTarget[ $key ]);
        }
      }
    }

    if($updatepPostOfCat){
        foreach ($updatepPostOfCat as $slug_cat => $target_ids){
            set_time_limit(30);
            Post::where('site_id', $targetId)
                ->whereIn('target_post_id', explode(',', $target_ids))
                ->update(['category' => $slug_cat]);
        }
        (new CdsApiDriver($siteUrl))->send('removeOptionPostOfCat')->getResult();
    }

    return response()->json([
      'status' => 'OK',
      'code' => '200',
      'quantity' => count($resDataCategoryOnTarget),
      'data' => $resDataCategoryOnTarget,
    ]);
  }
}
