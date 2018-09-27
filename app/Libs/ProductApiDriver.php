<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/1/16
 * Time: 1:20 PM
 */

namespace App\Libs;


class ProductApiDriver
{
    public $apiUrl;
    public $httpClient;

    public function __construct() {
        $this->apiUrl = env('CDS_PRODUCT_API_URL', '');
        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $this->apiUrl
        ]);
    }

    private function getResponse($response) {
        $content = $response->getBody()->getContents();
        $resJson = json_decode($content);

        return $resJson;
    }

    public function listAll($otherData, $page = 1) {
        extract($otherData);
        $orderQs = '';
        if(isset($orderBy) && !empty($orderBy)) {
            $orderQs = '&orderby='.$orderBy;
            $orderQs .= '&order='.(isset($order) ? $order : 'desc');
        };
        $response = $this->httpClient->get('/api/product/list?page='.$page.$orderQs);

        return $this->getResponse($response);
    }

    public function create($data) {
        $response = $this->httpClient->post('/api/product/create', [
            'form_params' => [
                'data' => $data
            ]
        ]);

        return $this->getResponse($response);
    }

    public function update($productId, $data) {
        $response = $this->httpClient->post('/api/product/update/'.$productId, [
            'form_params' => [
                'data' => $data
            ]
        ]);

        return $this->getResponse($response);
    }

    public function search($data, $page = 1) {
        extract($data);
        $keyword = isset($keyword) ? $keyword : '';
	    $categoryId = isset($categoryId) ? $categoryId : 0;
	    $tag = isset($tag) ? $tag : null;
	    $priority = isset($priority) ? $priority : null;
        $orderQs = '';
        if(isset($orderBy)) {
            $orderQs = '&orderby='.$orderBy;
            $orderQs .= '&order='.(isset($order) ? $order : 'desc');
        }
        $response = $this->httpClient->get("/api/product/search?keyword={$keyword}&category_id={$categoryId}&tag=".json_encode($tag)."&priority=".json_encode($priority)."&page={$page}{$orderQs}");

        return $this->getResponse($response);
    }

    public function detail($productId = 0) {
        $response = $this->httpClient->get('/api/product/detail/'.$productId);

        return $this->getResponse($response);
    }

    public function url($path) {
        return $this->apiUrl.$path;
    }

    public function categories() {
        $response = $this->httpClient->get("/api/category/list");

        return $this->getResponse($response);
    }

    public function createCategory($data) {
        $response = $this->httpClient->post('/api/category/create', [
            'form_params' => [
                'data' => $data
            ]
        ]);

        return $this->getResponse($response);
    }

    public function detailCategory($categoryId = 0) {
        $response = $this->httpClient->get('/api/category/detail/'.$categoryId);

        return $this->getResponse($response);
    }

    public function updateCategory($categoryId, $data) {
        $response = $this->httpClient->post('/api/category/update/'.$categoryId, [
            'form_params' => [
                'data' => $data
            ]
        ]);

        return $this->getResponse($response);
    }

    public function deleteCategory($categoryId = 0) {
        $response = $this->httpClient->get('/api/category/delete/'.$categoryId);

        return $this->getResponse($response);
    }

    public function getAjaxCategories($search = null, $page) {
        $response = $this->httpClient->get('/api/category/ajaxlist/?search='.$search.'&page='.$page);

        return $this->getResponse($response);
    }

    public function getAjaxChildCategories($search = null, $page, $parent_id = null) {
        $response = $this->httpClient->get('/api/category/ajaxlistchild/?search='.$search.'&page='.$page.'&parent_id='.$parent_id);

        return $this->getResponse($response);
    }

    public function getAjaxTags($search = null, $page) {
        $response = $this->httpClient->get('/api/tag/ajaxlist/?search='.$search.'&page='.$page);

        return $this->getResponse($response);
    }

    public function listSelected($tag = null) {
        $response = $this->httpClient->get('/api/tag/listselected?tag='.json_encode($tag));

        return $this->getResponse($response);
    }
}