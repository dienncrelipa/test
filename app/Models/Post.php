<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 4:45 PM
 */

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Post extends BaseModel
{
    const POST_PER_PAGE = 25;
    public $table = 'posts';

    public function canEditBy(User $user) {
        if($this->isPublished()) {
            return ($user->is(User::ADMIN) || ($user->is(User::REWRITER) && !$this->is_delete) || ($user->is(User::REWRITER_2) && !$this->is_delete));;
        }
        return $this->canViewBy($user);
    }

    public function canCopyBy(User $user) {
        return ($user->is(User::ADMIN) || ($user->is(User::REWRITER) && !$this->is_delete));
    }

    public function canDraftBy(User $user) {
        if($this->isPublished()) {
            return $user->is(User::ADMIN) || ($user->is(User::REWRITER) && !$this->is_delete);
        }

        return $this->canViewBy($user);
    }

    public function canWriteDeleteBy(User $user) {
        return $this->canEditBy($user) && (!$user->is(User::REWRITER, User::REWRITER_2));
    }

    public function canDeleteBy(User $user) {
        return $user->is(User::ADMIN);
    }

    public function canViewBy(User $user) {
        return ($user->id == $this->user_id && !$this->is_delete) || $user->is(User::ADMIN) || ($user->is(User::REWRITER) && !$this->is_delete) || ($user->is(User::REWRITER_2) && !$this->is_delete);
    }

    public function isLock($session) {
        // Lock detect
        $lockStatus = false;

        if($this->lock_expired > Carbon::now()->toDateTimeString() && $this->lock_session != $session->session_key) {
            $lockStatus = true;
        }

        return $lockStatus;
    }

    public function lock($session) {
        $previousUpdateTime = $this->updated_at;
        $this->lock_expired = Carbon::now()->addSeconds(15)->toDateTimeString();
        $this->lock_session = $session->session_key;
        $this->timestamps = false;
        $this->updated_at = $previousUpdateTime;

        $this->save(['timestamps' => false]);
    }

    public function freeLock($session) {
        $this->lock_expired = '';
        $this->lock_session = '';
        return $this->save(['timestamps' => false]);
    }

    public function getTargetSiteUrl() {
        return Site::find($this->site_id)->site_url.'?p='.$this->target_post_id;
    }

    public function canPublishBy(User $user) {
        return $user->is(User::ADMIN) || ($user->is(User::REWRITER) && !$this->is_delete) || ($user->is(User::REWRITER_2) && !$this->is_delete);
    }

    public function saveHistory($status = PostHistory::STATUS_SAVED) {
        return PostHistory::newHistory($this, $status);
    }

    public function getChecksum() {
        return self::calculateChecksum($this->content);
    }

    public function getLastClientChecksum() {
        return PostHistory::getChecksum($this, PostHistory::STATUS_SAVED);
    }

    public function isPublished() {
        return $this->published_status == 1;
    }

    public static function getPostByUser(User $user, $page = 1) {
        $query = self::where('id', '>', 0);

        if(!$user->is(User::ADMIN)) {
            $query->where('user_id', $user->id);
        }

        $query->limit(self::POST_PER_PAGE)->offset(($page-1)*self::POST_PER_PAGE);

        return $query->get();
    }
    
    public static function replaceIDButtonLink($postID, $content, $idOldPost = 0){
        if($postID && !empty($content)){
            if($idOldPost > 0){
                return preg_replace("/(aff-p$idOldPost-b)/", "aff-p$postID-b", $content);
            }
            else
            {
                return preg_replace("/(__POSTID__)/", $postID, $content);
            }
        }
        return $content;
    }

    public static function countPostByUser(User $user) {
        $query = self::where('id', '>', 0);

        if(!$user->is(User::ADMIN)) {
            $query->where('user_id', $user->id);
        }

        return $query->count();
    }
    
    public static function getPostList() {
        $posts = [];
        $allPosts = Post::all(array('id'));
        foreach ($allPosts as $post) {
            $posts[$post->id] = $post->id;
        }
        
        return $posts;
    }
    public static function getTargetPostList($txt_search = '' , $is_user = 0, $page = 1, $user_role = 'admin') {
        $_where = array(['sites.status', 1]);
        $_having = '1=1';
        if($txt_search != ''){
            $txt_search = addslashes($txt_search);
            $_having = (" value like '%$txt_search%'");
        }
        if(is_numeric($is_user) && $user_role != 'admin'){
            array_push($_where, ['posts.is_delete', '0']);
            if($user_role != 'rewriter' && $user_role != 'rewriter2')
            array_push($_where, ['posts.user_id', $is_user]);
        }
        $total = Post::select(DB::raw("CONCAT(sites.site_url, '?p=', posts.target_post_id) as value"))
            ->leftJoin('sites', 'sites.id', '=', 'posts.site_id')
            ->where($_where)
            ->groupBy('posts.id')
            ->havingRaw($_having)
            ->get()->count();
        $allPosts = Post::select('posts.id as id', DB::raw("CONCAT(sites.site_url, '?p=', posts.target_post_id) as value"))
            ->leftJoin('sites', 'sites.id', '=', 'posts.site_id')
            ->where($_where)
            ->groupBy('posts.id')
            ->havingRaw($_having)
            ->orderBy('posts.id', 'desc')
            ->limit(10)
            ->take($page * 10)
            ->get()
            ->toArray();
        return array(
            'data' => $allPosts,
            'total' => $total
        );
    }
    public static function getSiteListOfPost($txt_search = '' , $id_user = 0, $page = 1, $user_role = 'admin') {
        $_where = array(['sites.status', 1]);
        if($txt_search != ''){
            $txt_search = addslashes($txt_search);
            array_push($_where, ['sites.name', 'LIKE', "%$txt_search%"]);
        }
        if(is_numeric($id_user) && $user_role != 'admin'){
            array_push($_where, ['posts.is_delete', '0']);
            if($user_role != 'rewriter' && $user_role != 'rewriter2')
            array_push($_where, ['posts.user_id', $id_user]);
        }
        $allSite = Post::select('sites.id as id', 'sites.name as name')
            ->leftJoin('sites', 'sites.id', '=', 'posts.site_id')
            ->where($_where)
            ->groupBy('sites.name')
            ->orderBy('sites.id', 'desc')
            ->paginate(10, ['*'], 'page', $page);
        return $allSite;
    }
    public static function getFilterID($id_data , $id_user = 0, $page = 0, $user_role = 'admin') {
        $_where = array();
        if($id_data != ''){
            $id_data = addslashes($id_data);
            array_push($_where, ['posts.id', 'like', "%$id_data%"]);
        }
        if(is_numeric($id_user) && $user_role != 'admin'){
            if($user_role != 'rewriter' && $user_role != 'rewriter2') array_push($_where, ['posts.user_id', $id_user]);
            array_push($_where, ['posts.is_delete', '0']);
        }
        return Post::select('id', 'id as value')
                ->where($_where)
                ->orderBy('id', 'asc')
                ->paginate(10, ['*'], 'page', $page)
                ->toArray();
    }

    public static function calculateChecksum($content) {
        return dechex(crc32(str_replace("\r\n", "\n", $content)));
    }

    public static function checkPostDataRequireField($data, $is_public = false){
        $mess_error = $data['site_id'] == '' ? '・ターゲットサイトを選んでください<br/>':'';
        if($is_public){
            $mess_error .= $data['title'] == '' ? '・タイトルを入力して下さい。<br/>' : '';
            $mess_error .= $data['desc'] == '' ? '・リード文を入力して下さい。<br/>' : '';
            $mess_error .= $data['thumb'] == '' ? '・サムネイル画像を設定してください。<br/>' : '';
            $mess_error .= $data['mainKey'] == '' ? '・作成した記事のキーワードを入力してください。<br/>' : '';
        }
        return $mess_error;
    }

    public static function isUniquePostByTitle(self $post, $newData = []) {
        if(env('UNIQUE_TITLE_CHECK', false) == false) {
            return true;
        }
        $clonePost = clone $post;
        foreach($newData as $key => $value) {
            $clonePost->{$key} = $value;
        }
        return self::where('title', $clonePost->title)->where('published_status', 1)->where('id', '<>', $clonePost->id)->count() == 0;
    }
}