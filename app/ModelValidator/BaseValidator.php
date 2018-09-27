<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 12/19/15
 * Time: 5:21 PM
 */

namespace App\ModelValidator;

use App\Libs\MessagesContainer\Error;
use App\Libs\MessagesContainer\ErrorSet;

abstract class BaseValidator
{
    public $error;
    public $erroSet;
    public $modelNamespace = 'App\\Models\\';
    public $modelObject;
    public $replaceFieldName = array();
    public $canNotUpdate = array();
    public $allowedNull = array();
    public $dataObject;

    public function __construct() {
        $this->error = (object)array();
        $this->errorSet = new ErrorSet();
    }

    public function validate($object, $dataObject, ErrorSet &$errorSet) {
        $errorList = array();
        $this->modelObject = $object;
        $this->dataObject = $dataObject;
        foreach(get_class_methods(get_called_class()) as $methodName) {
            if(preg_match('/^_[a-z_]+$/', $methodName) && !preg_match('/^__[a-zA-Z]+$/', $methodName)) {
                $propertyName = substr($methodName, 1);

                if((isset($object->$propertyName) && strlen($object->$propertyName) == 0) && strlen($dataObject->$propertyName) == 0
                    && array_search($propertyName, $this->allowedNull) === false) {
                    $errorList[] = trans('errors.cannot_empty', ['item' => $propertyName]);
                    continue;
                }



                if(!empty($this->modelObject->id)
                    && isset($this->dataObject->$propertyName)
                    && $this->dataObject->$propertyName != $this->modelObject->$propertyName
                    && array_search($propertyName, $this->canNotUpdate) !== false) {

                    $errorList[] = ucfirst($this->getReplaceFieldName($propertyName)).' can not be changed';
                    continue;
                }

                if(!isset($dataObject->$propertyName)) {
                    if(!$this->$methodName($object->$propertyName)) {
                        $errorList[] = $this->error->$propertyName;
                        $errorSet->add(new Error($this->error->$propertyName));
                    }
                } else {
                    if(!$this->$methodName($dataObject->$propertyName)) {
                        $errorList[] = $this->error->$propertyName;
                        $errorSet->add(new Error($this->error->$propertyName));
                    }
                }
            }
        }

        return !$errorSet->hasError();
    }

    public function getModelName() {
        $classPath = get_class($this);
        $tmp = explode('\\',$classPath);
        return str_replace("Validator", "", $tmp[count($tmp)-1]);
    }

    public function getModelClass() {
        return $this->modelNamespace.$this->getModelName();
    }

    public function getReplaceFieldName($fieldName) {
        if(isset($this->replaceFieldName) && isset($this->replaceFieldName[$fieldName])) {
            return $this->replaceFieldName[$fieldName];
        }
        return $fieldName;
    }

    public function bindError($functionName, $error) {
        $this->error->{substr($functionName, 1)} = $error;
    }
}