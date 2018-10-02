<?php

namespace App\Libs;

use Illuminate\Support\Facades\Request as Request;
use App\Models\User;

class ColdValidator {

    private static $instance;
    const INVALID_EMAIL = 10001;
    const EXISTED_EMAIL = 10002;
    const TOO_LONG_PASSWORD = 10003;
    const LOGIN_FAILED = 10004;
    const LOGIN_INVALID_FORM = 1005;

    private static $errorMsg = array(
        self::INVALID_EMAIL => 'Invalid email',
        self::EXISTED_EMAIL => 'This email is registered',
        self::TOO_LONG_PASSWORD => 'Password is too long',
        self::LOGIN_FAILED => 'Your login information is not valid',
        self::LOGIN_INVALID_FORM => 'Your login information is not valid'
    );

    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new ColdValidator();
        }
        return self::$instance;
    }

    public function inputs($fields = array()) {
        $returns = array();

        foreach ($fields as $field) {
            if (!Request::has($field)) {
                throw new \Exception("Missing $field", 9999);
            }
            $returns[] = Request::__get($field);
        }

        return $returns;
    }

    public function data($fields = array()) {
        $returns = array();

        if(!Request::has('data')) {
            throw new \Exception("Missing data", 9999);
        }

        $data = Request::__get('data');

        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Missing $field", 9999);
            }
            $returns[] = $data[$field];
        }

        return $returns;
    }

    public function inputOrDefault($fields = array()) {
        $returns = array();

        foreach ($fields as $field=>$default) {
            if (!Request::has($field)) {
                $returns[] = $default;
            } else {
                $returns[] = Request::__get($field);
            }
        }

        return $returns;
    }
    
    public function numeric($fields = array()) {
        $returns = array();

        foreach ($fields as $field) {
            if (!Request::has($field) || !is_numeric(Request::__get($field))) {
                throw new \Exception("$field is not a number or does not exist", 10000);
            }
            $returns[] = Request::__get($field);
        }
        
        return $returns;
    }
    
    public function numericOrDefault($fields = array()) {
        $returns = array();

        foreach ($fields as $field=>$default) {
            if (!Request::has($field) || !is_numeric(Request::__get($field))) {
                $returns[] = $default;
            } else {
                $returns[] = Request::__get($field);
            }
        }
        
        return $returns;
    }
    
    public function page() {
        $page = 1;
        if (Request::has('page') && is_numeric(Request::__get('page')) && Request::__get('page') > 0) {
            $page = Request::__get('page');
        }

        return $page;
    }
    
    public function files($fields = array()) {
        $returns = array();

        foreach ($fields as $field) {
            if (!Request::hasFile($field) || !Input::file($field)->isValid()) {
                throw new \Exception('PHOTO_MISSING_UPLOAD_FILE', PHOTO_MISSING_UPLOAD_FILE);
            }
            $returns[] = Input::file($field);
        }

        return $returns;
    }
    
    public function getXY() {
        $x = null;
        $y = null;
        if(Request::has('latitude') && is_numeric(Request::__get('latitude'))) {
            $x = Request::__get('latitude');
        }
        if(Request::has('longitude') && is_numeric(Request::__get('longitude'))) {
            $y = Request::__get('longitude');
        }
        return array($x, $y);
    }

    public function order() {
        if(!Request::has('ord')) {
            return array(null, null);
        }
        $orderString = Request::__get('ord');

        $orderBy = $orderString;
        $order = 'desc';

        if($orderString[0] == '-') {
            $order = 'asc';
            $orderBy = substr($orderBy, 1);
        }

        return array($orderBy, $order);
    }
    
    /**
     * Valid a string that match :
     * - "a-z","0-9",".","_"
     * - have maximum a character "."
     * 
     * @param String $username
     * @return boolean
     */
    public function isValidUsername($username) {
        return preg_match('/^[a-z0-9._]{3,15}$/', $username) && substr_count($username, '.') <= 1;
    }

    /**
     * Valid an email string
     * 
     * @param string $email
     * @return boolean
     */
    public function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 255;
    }

    /**
     * Valid an password string
     * 
     * @param string $password
     * @return boolean
     */
    public function isValidPassword($password) {
        return strlen($password) <= 255;
    }

    /**
     * Valid a hashtag
     * 
     * @param string $hashtag
     * @return boolean
     */
    public function isValidHashtag($hashtag) {
        return preg_match('/^[a-zA-Z]+$/', $hashtag);
    }

    /**
     * Valid an image
     * 
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $photo
     * @return boolean
     */
    public function isValidImage($photo) {
        $mimesAllowed = array(
            'image/gif',
            'image/jpeg',
            'image/pjpeg',
            'image/png'
        );
        return in_array($photo->getMimeType(), $mimesAllowed);
    }

    /**
     * Process username
     * 
     * @param string $username
     * @return boolean
     * @throws Exception
     */
    public function username($username) {
        if (!$this->isValidUsername($username)) {
            throw new \Exception('REGISTER_INVALID_USERNAME', REGISTER_INVALID_USERNAME);
        }

        if (User::isExistedUsername($username)) {
            throw new \Exception('REGISTER_EXISTED_USERNAME', REGISTER_EXISTED_USERNAME);
        }

        return true;
    }

    /**
     * Process email
     * 
     * @param string $email
     * @return boolean
     * @throws Exception
     */
    public function email($email) {
        if (!$this->isValidEmail($email)) {
            throw new \Exception(self::$errorMsg[self::INVALID_EMAIL], self::INVALID_EMAIL);
        }

        if (User::isExistedEmail($email)) {
            throw new \Exception(self::$errorMsg[self::EXISTED_EMAIL], self::EXISTED_EMAIL);
        }

        return true;
    }

    /**
     * Process password
     * 
     * @param string $password
     * @return boolean
     * @throws Exception
     */
    public function password($password) {
        if (!$this->isValidPassword($password)) {
            throw new \Exception(self::$errorMsg[self::TOO_LONG_PASSWORD], self::TOO_LONG_PASSWORD);
        }

        return true;
    }

    /**
     * Process image
     * 
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $photo
     * @return boolean
     * @throws Exception
     */
    public function image($photo) {
        if (!$this->isValidImage($photo)) {
            throw new \Exception('PHOTO_INVALID_FILE_TYPE', PHOTO_MISSING_UPLOAD_FILE);
        }

        return true;
    }

    /**
     * Process login
     * 
     * @param string $username
     * @param string $password
     * @return boolean
     * @throws Exception
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            throw new \Exception(self::$errorMsg[self::LOGIN_INVALID_FORM], self::LOGIN_INVALID_FORM);
        }

        $userId = User::getByEmailPassword($email, $password);

        if (empty($userId)) {
            throw new \Exception(self::$errorMsg[self::LOGIN_INVALID_FORM], self::LOGIN_INVALID_FORM);
        }

        return $userId;
    }

    /**
     * Process hashtag
     * 
     * @param array $hashtags
     * @return boolean
     * @throws Exception
     */
    public function hashtags($hashtags = array()) {
        foreach ($hashtags as $tag) {
            if (!$this->isValidHashtag($tag)) {
                throw new \Exception('HASHTAG_INVALID_HASHTAG', HASHTAG_INVALID_HASHTAG);
            }
        }

        return true;
    }

    public function isValidTimeStamp($timestamp) {
        return ((string) (int) $timestamp === $timestamp) 
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    public function isKanji($str) {
        return preg_match('/[\x{4E00}-\x{9FBF}]/u', $str) > 0;
    }

    public function isHiragana($str) {
        return preg_match('/[\x{3040}-\x{309F}]/u', $str) > 0;
    }

    public function isKatakana($str) {
        return preg_match('/[\x{30A0}-\x{30FF}]/u', $str) > 0;
    }

    public function isJapanese($str) {
        return $this->isKanji($str) || $this->isHiragana($str) || $this->isKatakana($str);
    }
}
