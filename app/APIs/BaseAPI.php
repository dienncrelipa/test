<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 2:04 PM
 */

namespace App\APIs;


class BaseAPI
{
    protected $rawData;

    /**
     * @var DataInputter\Inputter
     */
    protected $data;

    public $returnDataType = 'json'; // JSON or XML or anything

    public $isPublic = true; // Should this API be public to global

    protected $parentAction;
    protected $dataSubmitted = null;
    /**
     * @var \Illuminate\Http\Request
     */
    public $request; // Request from client

    public function __construct() {
        $this->data = new DataInputter\Inputter($this->rawData);
    }

    public function setParentAction($action) {
        $this->parentAction = $action;
    }

    public function setData($data) {
        $this->rawData = $data;
//        $this->data->setData($this->rawData);
    }

    public function action($api, $action) {
        $apiUrl = action($this->parentAction);
        return "$apiUrl/$api/$action";
    }

    public function getDataSubmitted() {
        $data = $this->request->get('data');
        if(empty($data)) {
            return array();
            $this->dataSubmitted = array();
        }
        $this->dataSubmitted = $data;
        return $data;
    }

    public function getData($key, $default = null) {
        if(!$this->dataSubmitted) {
            $this->getDataSubmitted();
        }

        if(!isset($this->dataSubmitted[$key])) {
            return $default;
        }
        return $this->dataSubmitted[$key];
    }

    public function _data($data, $otherData = array()) {
        return array_merge(array('data' => $data), $otherData);
    }
}