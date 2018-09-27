<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\DB;

class User extends BaseModel implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletes;

    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const CONTRIBUTOR = 'contributor';
    const REWRITER = 'rewriter';
    const REWRITER_2 = 'rewriter2';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'users';
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    public static $validRole = [self::ADMIN, self::EDITOR, self::CONTRIBUTOR, self::REWRITER, self::REWRITER_2];

    public static function getUser($userId) {
        $user = self::find($userId);
        return self::returnOrEmpty($user);
    }

    public static function validateLogin($username, $password) {
        $user = self::where('username', $username)->where(function($query) use ($password) {
            $query->where('password', $password)->orWhere('password', md5($password));
        })->first();

        return self::returnOrEmpty($user);
    }

    public static function isValidRole($role) {
        return array_search($role, self::$validRole) !== false;
    }

    public function is() {
        $result = false;
        foreach(func_get_args() as $role) {
            if($role == $this->role) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function _can($permission) {
        return array_search($permission, $this->permissions[$this->role]) !== false;
    }

    public function parent() {
        $parent = User::find($this->parent_id);

        if(empty($parent)) {
            return new User();
        }

        return $parent;
    }

    public static function getUserList() {
        $users = [];
        $allUsers = User::all();
        foreach ($allUsers as $user) {
            $users[$user->id] = $user->fullname;
        }
        
        return $users;
    }
    public static function getFilterUser($txt_search = '' , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array(['fullname', '<>', '']);
        if($txt_search != ''){
            $txt_search = addslashes($txt_search);
            array_push($_where, ['fullname', 'LIKE', "%$txt_search%"]);
        }
        if(is_numeric($id_user) && !in_array($user_role, array('admin','rewriter','rewriter2'))){
            array_push($_where, ['id', $id_user]);
        }

        return User::select('id', 'fullname as value')
            ->where($_where)
            ->orderBy('fullname', 'asc')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }

    /**
     *  copied by linhnc from Post::getFilterID()
     *  filter user with id
     */
    public static function getFilterID($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array();
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['id', 'like', "$id_data%"]);
        }
        if(is_numeric($id_user) && !in_array($user_role, array('admin','rewriter'))){
            array_push($_where, ['id', $id_user]);
        }
        return User::select('id', 'id as value')
            ->where($_where)
            ->orderBy('id', 'asc')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }
    /**
     * filter user with username
     */
    public static function getFilterUsername($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array();
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['username', 'like', "$id_data%"]);
        }
        if(is_numeric($id_user) && !in_array($user_role, array('admin','rewriter'))){
            array_push($_where, ['id', $id_user]);
        }
        return User::select('id', 'username as value')
            ->where($_where)
            ->orderBy('id', 'asc')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }
    /**
     * filter user with crowdwork
     */
    public static function getFilterCrowdwork($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array(['crowdworks_id', '<>', '']);
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['crowdworks_id', 'like', "$id_data%"]);
        }
        if(is_numeric($id_user) && !in_array($user_role, array('admin','rewriter'))){
            array_push($_where, ['id', $id_user]);
        }
        return User::select('id', 'crowdworks_id as value')
            ->where($_where)
            ->orderBy('id', 'asc')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }
    /**
     * filter user with role
     */
    public static function getFilterRole($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array(['role', '<>', '']);
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['role', 'like', "$id_data%"]);
        }
        if(is_numeric($id_user) && !in_array($user_role, array('admin','rewriter'))){
            array_push($_where, ['id', $id_user]);
        }
        return User::select('id', 'role as value')
            ->where($_where)
            ->groupBy('role')
            ->paginate(10, ['*'], 'page', $page)
            ->toArray();
    }

    public function isDeactive() {
        $lastSession = UserSession::where('user_id', $this->id)->orderBy('id', 'DESC')->first();

        if(empty($lastSession)) {
            return false;
        }

        $lastActiveTime = Carbon::createFromTimestamp($lastSession->updated_at->timestamp);

        if(Carbon::now()->diffInDays($lastActiveTime) >= 30) {
            return true;
        }

        return false;
    }

    public function getLastActiveTimestamp($toString = false) {
        $lastSession = UserSession::where('user_id', $this->id)->orderBy('id', 'DESC')->first();

        if(empty($lastSession)) {
            return null;
        }

        return ($toString) ? $lastSession->updated_at->format('d/m/y H:i:s') : $lastSession->updated_at->timestamp;
    }

    public function reActivate() {
        return UserSession::newSession($this->id);
    }
}
