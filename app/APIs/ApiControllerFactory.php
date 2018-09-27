<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 8/5/15
 * Time: 2:07 PM
 */

namespace App\APIs;


use Exception;
use Illuminate\Http\Request;
use App\APIs\DataFormatter\DataFormatter;

class ApiControllerFactory
{
    const API_NOT_FOUND = 1;
    const API_ACTION_NOT_FOUND = 2;
    const RETURNED_DATA_WRONG_FORMAT = 3;

    private $parentAction;
    public $constructError = false;

    private static $error_msg = array(
        self::API_NOT_FOUND => 'Api not found',
        self::RETURNED_DATA_WRONG_FORMAT => 'Something wrong with returned data'
    );

    private $apiClassObject = null;
    private $httpMethod = 'get';

    /**
     * @param $code
     * @return string
     *
     * Get error message using error code
     */
    private function getErrorMsg($code) {
        if(!isset(self::$error_msg[$code])) {
            return 'Unknown error';
        }
        return self::$error_msg[$code];
    }

    /**
     * @param $type
     * @return string
     *
     * Get Content-Type header string
     */
    private function getContentType($type) {
        $types = array(
            'json' => 'application/json'
        );

        return isset($types[$type]) ? $types[$type] : 'text/html';
    }

    /**
     * @param String $action
     * @return mixed $method
     *
     * Get action name of API class
     */
    private function getAction($action) {
        $listMethod = get_class_methods($this->apiClassObject);
        foreach($listMethod as $method) {
            $methodString = strtolower($method);
            if($methodString == strtolower($this->httpMethod.$action) || $methodString == 'any'.$action) {
                return $method;
            }
        }
        return null;
    }

    public function __construct($apiName = null, Request $request) {
        $this->startTime = date("Y-m-d H:i:s");

        // Throw exception when input is null or API file does not exist
        if($apiName == null || !class_exists(__NAMESPACE__.'\\'.ucfirst($apiName).'API')) {
            throw new Exception(self::getErrorMsg(ApiControllerFactory::API_NOT_FOUND), ApiControllerFactory::API_NOT_FOUND);
        }

        $className = __NAMESPACE__.'\\'.ucfirst($apiName).'API';
        try {
            $this->apiClassObject = new $className();
        } catch(\Exception $e) {
            $this->constructError = response((new DataFormatter('json', array(
                'error' => array(
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                )
            )))->format(), 200, array(
                'Content-Type' => 'application/json'
            ));
            return;
        }

        // Throw exception when API class does not extend the BaseAPI class or API class is private
        if(get_class($this->apiClassObject) == 'BaseAPI' || $this->apiClassObject->isPublic == false) {
            throw new Exception(self::getErrorMsg(ApiControllerFactory::API_NOT_FOUND), ApiControllerFactory::API_NOT_FOUND);
        }

        $this->apiClassObject->setData($request->all()); // Set headers data
        $this->apiClassObject->request = $request;
        $this->httpMethod = $request->getMethod(); // Set requested HTTP method
    }

    /**
     * @param String $action
     * @param array $parameters
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     *
     * Execute the action
     */
    public function doAction($action = null, $parameters = array()) {
        if($action == null || ($apiAction = $this->getAction($action)) == null) {
//            echo $apiAction;
            throw new \Exception(self::getErrorMsg(ApiControllerFactory::API_NOT_FOUND), ApiControllerFactory::API_NOT_FOUND);
        }
        $this->apiClassObject->setParentAction($this->parentAction);
        try {
            $returnData = call_user_func_array(array($this->apiClassObject, $apiAction), $parameters);
        } catch(\Exception $e) {
            return response((new DataFormatter('json', array(
                'error' => array(
//                    'message' => $e->getMessage().' '.$e->getLine().' '.$e->getFile(),
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                )
            )))->format(), 200, array(
                'Content-Type' => 'application/json'
            ));
        }
        $theException = new Exception(self::getErrorMsg(ApiControllerFactory::RETURNED_DATA_WRONG_FORMAT), ApiControllerFactory::RETURNED_DATA_WRONG_FORMAT);

        if(!is_array($returnData)) {
            throw $theException;
            return response((new DataFormatter('json', array(
                'error' => array(
                    'message' => $theException->getMessage(),
                    'code' => $theException->getCode()
                )
            )))->format(), 200, array(
                'Content-Type' => 'application/json'
            ));
        }

        $returnData['startTime'] = $this->startTime;
        $returnData['endTime'] = date("Y-m-d H:i:s");
        $returnData = (new DataFormatter($this->apiClassObject->returnDataType, $returnData))->format();

        return response($returnData, 200, array(
            'Content-Type' => self::getContentType($this->apiClassObject->returnDataType)
        ));
    }

    public function setParentAction($action) {
        $this->parentAction = $action;
    }
}