<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/23/15
 * Time: 4:25 PM
 */

namespace App\ModelFactory;


class ModelFactoryList
{
    private $listFactory;
    private $listIndex;
    private $indexName;
    private $count = 0;

    public function __construct($indexName = null) {
        $this->listFactory = array();
        $this->listIndex = array();
        if($indexName) {
            $this->indexName = $indexName;
        }
    }

    public function push(BaseFactory $factory) {
        $indexName = $this->indexName;

        if($indexName && isset($factory->getObject()->$indexName)) {
            $this->listIndex[$factory->getObject()->$indexName] = $this->count;
        } else {
            $this->listIndex[$this->count] = $this->count;
        }

        $this->listFactory[] = $factory;
        $this->count++;
    }

    public function get($i) {
        if(isset($this->listIndex[$i])) {
            return $this->listFactory[$this->listIndex[$i]];
        } else if(isset($this->listFactory[$i])) {
            return $this->listFactory[$i];
        } else {
            return null;
        }
    }

    public function sort($type = 'asc', $key = null) {
        function _usort_asc($a, $b) {
            $key = $a->_sortKey;
            return strcmp($a->getObject()->$key, $b->getObject()->$key);
        }

        function _usort_desc($a, $b) {
            $key = $a->_sortKey;
            return strcmp($b->getObject()->$key, $a->getObject()->$key);
        }

        foreach($this->listFactory as $factory) {
            $factory->_sortKey = ($key != null && isset($this->get(0)->getObject()->$key)) ? $key : $this->indexName;
        }

        usort($this->listFactory, "_usort_".strtolower($type));

        foreach($this->listFactory as $factory) {
            unset($factory->_sortKey);
        }
    }

    public function setIndexName($indexName) {
        $this->indexName = $indexName;

        return $this;
    }

    public function setListFactory($listFactory = array()) {
        $this->listFactory = $listFactory;

        return $this;
    }

    public function count() {
        return $this->count;
    }

    public function getList() {
        return $this->listFactory;
    }

    public function getListObject() {
        $objects = array();
        foreach($this->listFactory as $factory) {
            $objects[] = $factory->getObject();
        }

        return $objects;
    }

    public function getArrayOf($key) {
        $array = array();

        foreach($this->listFactory as $factory) {
            if(isset($factory->getObject()->$key)) {
                $array[] = $factory->getObject()->$key;
            }
        }

        return $array;
    }

    public function massBind($data = array()) {
        foreach($this->listFactory as $factory) {
            /* @var $factory \App\ModelFactory\BaseFactory */
            $factory->bind($data);
        }

        return $this;
    }

    public function massSave() {
        foreach($this->listFactory as $factory) {
            /* @var $factory \App\ModelFactory\BaseFactory */
            $factory->save();
        }

        return $this;
    }
}