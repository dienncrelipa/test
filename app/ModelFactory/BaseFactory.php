<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/19/15
 * Time: 1:49 AM
 */

namespace App\ModelFactory;


use App\Libs\MessagesContainer\ErrorSet;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

abstract class BaseFactory
{
    static protected $modelNamespace = 'App\\Models\\';
    static protected $modelValidatorNamespace = 'App\\ModelValidator\\';

    protected $modelObject;
    protected $dataObject;
    protected $modelValidator;
    protected $error;
    protected $errorSet;

    public function __construct($modelObject, $modelValidator) {
        $this->error = array();
        $this->errorSet = new ErrorSet();
        $this->modelObject = $modelObject;
        $this->modelValidator = $modelValidator;
        $this->dataObject = (object)array();
    }

    public static function create() {
        $modelName = static::getModelName();
        $modelClass = self::$modelNamespace.$modelName;
        $modelValidatorClass = self::$modelValidatorNamespace.$modelName.'Validator';
        $ownClass = get_called_class();
        return new $ownClass(new $modelClass(), new $modelValidatorClass());
    }


    /**
     * @param array $args
     * @return ModelFactoryList
     */
    public static function find($args = array()) {
        $modelName = static::getModelName();
        $modelClass = self::$modelNamespace.$modelName;
        $modelValidatorClass = self::$modelValidatorNamespace.$modelName.'Validator';
        $ownClass = get_called_class();

        extract($args);


        if(!isset($conditions) || !is_array($conditions)) {
            return new ModelFactoryList();
        }

        $modelFactoryList = new ModelFactoryList();
        $tempFactoryClass = new $ownClass(new $modelClass(), new $modelValidatorClass());

        $queryObject = null;

        foreach($conditions as $condition) {
            $operator = key($condition);
            $params = $condition[$operator];


            // Preprocess data
            if($operator == 'where') {
                $fieldName = $params[0];
                $fieldValue = $params[1];
                if (count($params) == 3) {
                    $fieldValue = $params[2];
                }

                $methodName = '_' . $fieldName;

                if (is_callable(array($tempFactoryClass, $methodName))) {
                    $fieldValue = $tempFactoryClass->$methodName($fieldValue);
                }

                if (count($params) == 3) {
                    $params[2] = $fieldValue;
                } else {
                    $params[1] = $fieldValue;
                }
            }


            // Query
            if($queryObject == null) {
                $queryObject = forward_static_call_array(array($modelClass, $operator), $params);
            } else {
                $queryObject = call_user_func_array(array($queryObject, $operator), $params);
            }
        }

        if(isset($orderBy) && is_array($orderBy)) {
            $modelFactoryList->setIndexName($orderBy[0]);
            $queryObject->orderBy($orderBy[0], $orderBy[1]);
        }
        if(isset($paginate)) {
            Paginator::currentPageResolver(function() use ($paginate){
                return $paginate[0];
            });
            $paginator = $queryObject->paginate($paginate[1]);
            $modelFactoryList->total = $paginator->total();
            $modelFactoryList->totalPages = ceil($paginator->total() / $paginator->perPage());
        }

        $data = $queryObject->get();

        foreach($data as $modelObject) {
            $modelFactoryList->push(new $ownClass($modelObject, new $modelValidatorClass()));
        }

        return $modelFactoryList;
    }

    public static function getModelName() {
        $ownClass = get_called_class();
        $tmp = explode("\\", $ownClass);
        return str_replace("Factory", "", $tmp[count($tmp)-1]);
    }

    public function bind() {
        $args = func_get_args();
        foreach($args as $data) {
            if(!is_array($data)) {
                continue;
            }
            foreach ($data as $key => $value) {
                $this->dataObject->$key = $value;
            }
        }

        return $this;
    }


    public function save() {
        $listMethod = get_class_methods(get_called_class());

        foreach($listMethod as $methodName) {
            if(preg_match('/^auto_[a-zA-Z_]+$/', $methodName)) {
                $propertyName = substr($methodName, 5);
                if(!isset($this->modelObject->$propertyName)) {
                    $this->modelObject->$propertyName = $this->$methodName();
                    $this->dataObject->$propertyName = $this->modelObject->$propertyName;
                }
            }
        }

        if(!$this->modelValidator->validate($this->modelObject, $this->dataObject, $this->errorSet)) {
            return $this;
        }

        foreach(get_object_vars($this->dataObject) as $key => $value) {
            $methodName = '_' . $key;
            if(method_exists($this, $methodName)) {
                $this->modelObject->$key = $this->$methodName($value);
                $this->dataObject->$key = $this->modelObject->$key;
                continue;
            }
            $this->modelObject->$key = $value;
        }

        try {
            $this->modelObject->save();
        } catch(\Exception $e) {
            if($e->getCode() != '42S22') {
                $this->error[] = 'Something wrong with the system. Message: '.$e->getMessage();
            }
            $listColumn = Schema::getColumnListing($this->modelObject->table);
            foreach(get_object_vars($this->dataObject) as $propertyName => $value) {
                if(array_search((string)$propertyName, $listColumn) === false) {
                    unset($this->modelObject->$propertyName);
                    unset($this->dataObject->$propertyName);
                }
            }
            $this->modelObject->save();
        }

        return $this;
    }

    public function saved() {
        return empty($this->errorSet->hasError());
    }

    public function error() {
        return $this->errorSet;
    }

    public function getObject() {
        return $this->modelObject;
    }
}