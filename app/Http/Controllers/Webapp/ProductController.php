<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/1/16
 * Time: 1:18 PM
 */

namespace App\Http\Controllers\Webapp;


use App\Libs\ColdValidator;
use App\Libs\ProductApiDriver;
use Illuminate\Http\Request;
use Zofe\Rapyd\DataGrid\DataGrid;

class ProductController extends NeedAuthController
{
    public $api;

    public function __construct() {
        parent::__construct();
        $this->api = new ProductApiDriver();
    }

    public function getIndex(Request $request) {
        $page = ColdValidator::instance()->page();
        list($orderBy, $order) = ColdValidator::instance()->order();
        $allData = $this->api->listAll([
            'orderBy' => $orderBy,
            'order' => $order,
        ], $page);
        $totalPage = 0;
        $products = [];
        if(!isset($allData->error)) {
            $products = $allData->data;
            $totalPage = $allData->totalPage;
        }

        return view('webapp/product/index', [
            'products' => $products,
            'currentPage' => $page,
            'totalPage' => $totalPage,
        ]);
    }

    public function getSearch(Request $request) {
        $page = ColdValidator::instance()->page();
        $keyword = $request->get('keyword', '');
        list($orderBy, $order) = ColdValidator::instance()->order();
        $allData = $this->api->search([
            'keyword' => $keyword,
            'orderBy' => $orderBy,
            'order' => $order,
        ], $page);
        $totalPage = 0;
        $products = [];
        if(!isset($allData->error)) {
            $products = $allData->data;
            $totalPage = $allData->totalPage;
        }

        return view('webapp/product/index', [
            'products' => $products,
            'currentPage' => $page,
            'totalPage' => $totalPage,
            'keyword' => $keyword
        ]);
    }

    public function postSearch(Request $request) {
        return $this->getSearch($request);
    }

    public function getEdit($productId = 0) {
        $allData = $this->api->detail($productId);
        if(isset($allData->error)) {
            return 'Not found';
        }

        $product = $allData->data;
        $categories = $this->api->categories()->data;

        return view('webapp/product/edit', [
            'categories' => $categories,
            'product' => $product
        ]);
    }

    public function postEdit(Request $request, $productId = 0) {
        $data = $request->all();
        unset($data['_token']);

        $allData = $this->api->update($productId, $data);
        if(isset($allData->error)) {
            $this->_flashMessage($request, $allData->error->message, 'danger');
        } else {
            $this->_flashMessage($request, 'OK', 'success');
        }

        return redirect()->to(action('Webapp\\ProductController@getEdit', $productId))->send();
    }

    public function getCreate() {
        $categories = $this->api->categories()->data;
        return view('webapp/product/create', [
            'categories' => $categories
        ]);
    }

    public function postCreate(Request $request) {
        $data = $request->all();
        unset($data['_token']);

        $allData = $this->api->create($data);

        if(!isset($allData->error)) {
            $this->_flashMessage($request, 'OK', 'success');
        }

        return (array)$allData;
    }

    public function getCategory() {
        $categories = $this->api->categories()->data;

        return view('webapp/product/category', [
            'categories' => $categories
        ]);
    }

    public function getCreateCategory() {
        return view('webapp/product/create_category');
    }

    public function postCreateCategory(Request $request) {
        $data = $request->all();
        unset($data['_token']);

        $allData = $this->api->createCategory($data);

        if(!isset($allData->error)) {
            $this->_flashMessage($request, 'OK', 'success');
        }

        return (array)$allData;
    }

    public function getEditCategory($categoryId = 0) {
        $allData = $this->api->detailCategory($categoryId);
        if(isset($allData->error)) {
            return 'Not found';
        }

        $category = $allData->data;

        return view('webapp/product/edit_category', [
            'category' => $category,
        ]);
    }

    public function postEditCategory(Request $request, $categoryId = 0) {
        $data = $request->all();
        unset($data['_token']);

        $allData = $this->api->updateCategory($categoryId, $data);

        if(!isset($allData->error)) {
            $this->_flashMessage($request, 'OK', 'success');
        }

        return (array)$allData;
    }

    public function getDeleteCategory(Request $request, $categoryId = 0) {
        $allData = $this->api->deleteCategory($categoryId);

        if(isset($allData->error)) {
            $this->_flashMessage($request, $allData->error->message, 'danger');
        } else {
            $this->_flashMessage($request, 'OK', 'success');
        }

        return redirect()->to(action('Webapp\\ProductController@getCategory'))->send();
    }

    public function getPopup(Request $request, $option = null) {
        $keyword = $request->get('k', null);
        $categoryId = $request->get('category');
        $tag = $request->get('tag');
        $priority = $request->get('priority');
        $vars = $_GET;
        unset($vars['page']);
        $request->flashOnly('k', 'category', 'priority');

        $queryString = http_build_query($vars);

        list($orderBy, $order) = ColdValidator::instance()->order();
        $page = ColdValidator::instance()->page();
        $allData = $this->api->search([
            'keyword' => $keyword,
            'categoryId' => $categoryId,
            'tag' => $tag,
            'priority' => $priority,
            'orderBy' => $orderBy,
            'order' => $order,
        ], $page);

        $totalPage = 0;
        $products = [];
        if(!isset($allData->error)) {
            $products = $allData->data;
            $totalPage = $allData->totalPage;
        }

        $view = 'webapp/product/popup';

        if($option == 'picker') {
            $view = 'webapp/product/popup_pickurl';
        }

        $selectedTags = ($tag !== null) ? $this->api->listSelected($tag)->data : null;
	    $selectedCategory = $categoryId ? $this->api->detailCategory($categoryId)->data : null;

        return view($view, [
        	'selectedTags' => $selectedTags,
        	'selectedCategory' => $selectedCategory,
            'products' => $products,
            'totalPage' => $totalPage,
            'currentPage' => $page,
            'queryString' => $queryString,
            'picker' => $option,
            'siteId' => ($request->get('site_id') != null ? $request->get('site_id') : 0),
            'postId' => ($request->get('post_id') != null ? $request->get('post_id') : 0),
        ]);
    }

    public function getPopupProduct(Request $request, $productId = 0, $picker = null) {
        $product = $this->api->detail($productId);
        $siteId = ($request->get('site_id') != null ? $request->get('site_id') : 0);

        if(isset($product->error)) {
            return $product->error->message;
        }

        $product = $product->data;
        $embedCode = str_replace("\n", "", $product->embed_code);
        $embedCode = str_replace("'", "\\'", $embedCode);

        return view('webapp/product/popup_product', [
            'product' => $product,
            'picker' => $picker,
            'siteId' => $siteId,
            'embedCode' => $embedCode,
        ]);
    }

    public function getChildCategory(Request $request)
    {
        if ($request->ajax())
        {
            $parentId = $request->parent_id;
            $categories = $this->api->categories()->data;
            $childCate = array();
            foreach ($categories as $category)
            {
                if ($category->parent_id == $parentId && $parentId != 0)
                {
                    $childCate[] = $category;
                }
            }
            return response()->json($childCate);
        }
    }

    public function getAjaxCategoryList(Request $request)
    {
        $data = $this->api->getAjaxCategories($request->search, $request->page)->data;
        return response()->json($data);
    }

    public function getAjaxChildCategoryList(Request $request)
    {
        $data = $this->api->getAjaxChildCategories($request->search, $request->page, $request->parent_id)->data;
        return response()->json($data);
    }

    public function getAjaxTagList(Request $request)
    {
        $data = $this->api->getAjaxTags($request->search, $request->page)->data;
        return response()->json($data);
    }
}