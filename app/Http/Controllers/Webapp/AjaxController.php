<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/21/16
 * Time: 3:45 PM
 */

namespace App\Http\Controllers\Webapp;


use App\Libs\CdsApiDriver;
use App\Libs\HtmlValidator\Validator;
use App\Models\ActivityNotification;
use App\Models\Keywords;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Embed\Embed;

class AjaxController extends NeedAuthController
{
    public function postImage(Request $request) {
        $file = $request->file('file', null);
        $dirName = dirname($file->getRealPath());
        $newFilename = $file->getFilename().'.'.$file->getClientOriginalExtension();
        $file->move($dirName, $newFilename);
        $newPath = $dirName.'/'.$newFilename;

        $client = new \GuzzleHttp\Client();

        $submitData = [
            [
                'name'     => 'file',
                'contents' => fopen($newPath, 'r')
            ],
            [
                'name'     => 'tag_string',
                'contents' => $request->get('tag_string', '')
            ],
            [
                'name'     => 'width',
                'contents' => $request->get('width')
            ],
            [
                'name'     => 'height',
                'contents' => $request->get('height')
            ],
        ];

        if($request->get('store', false)) {
            $submitData[] = [
                'name' => 'store',
                'contents' => 'true'
            ];
        }

        try {
            $response = $client->post(env('MYPHOTOS_API_URL').'/api/image/public', [
                'multipart' => $submitData
            ]);

            $json = json_decode($response->getBody()->getContents());

            if($json === false) {
                throw new \Exception('Please try agagin');
            }

            if(isset($json->error)) {
                throw new \Exception($json->error->message);
            }

            return (array)$json->data;

        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getFetchUrl(Request $request) {
        $meta = [
            'title' => '',
            'description' => '',
            'thumbnail_url' => '',
        ];

        $client = new \GuzzleHttp\Client();

        $headers = get_headers($request->get('url'));
        preg_match( '~HTTP/1.(?:1|0) (\d{3})~', $headers[0], $matches );
        $code = $matches[1];
        
        if(intval(trim($code)) !== 401) {
            $info = Embed::create($request->get('url'));
            $content = $info->getAllProviders();

            foreach($content as $provider => $inf) {
                if(empty($meta['title']) && $inf->bag->get('title')) {
                    $meta['title'] = $inf->bag->get('title');
                }

                if(empty($meta['description']) && $inf->bag->get('description')) {
                    $meta['description'] = $inf->bag->get('description');
                }

                if(empty($meta['thumbnail_url']) && isset($content['opengraph']) && $content['opengraph']->bag->get('images') != null) {
                    if(count($content['opengraph']->bag->get('images')) > 0) {
                        $meta['thumbnail_url'] = $content['opengraph']->bag->get('images')[0];
                    }
                } else if(empty($meta['thumbnail_url']) && isset($content['twittercards'])  && $content['twittercards']->bag->get('images') != null) {
                    if(count($content['twittercards']->bag->get('images')) > 0) {
                        $meta['thumbnail_url'] = $content['twittercards']->bag->get('images')[0];
                    }
                } else if(empty($meta['thumbnail_url']) && count($inf->bag->get('images')) > 0) {
                    if(empty($meta['thumbnail_url'])) {
                        foreach($inf->bag->get('images') as $image) {
                            if(!empty($image)) {
                                $meta['thumbnail_url'] = $image;
                            }
                        }
                    }
                }
            }
            
            if(empty($meta['thumbnail_url'])) {
                $meta['thumbnail_url'] = 'https://i.imgur.com/UwS6UYf.png';
            }
        } else {
            $meta['error'] = 1;
            $meta['code'] =  $code;
            return response()->json($meta);
        }

        try {
            $response = $client->post(env('MYPHOTOS_API_URL').'/api/image/public', [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($meta['thumbnail_url'], 'r')
                    ],
                    [
                        'name'     => 'width',
                        'contents' => 75
                    ],
                    [
                        'name'     => 'height',
                       'contents' => 75
                    ]
                ]
            ]);
            $json = json_decode($response->getBody()->getContents());
            if($json === false) {
                throw new \Exception('Please try agagin');
            }
            if(isset($json->error)) {
                throw new \Exception($json->error->message);
            }
            $meta['thumbnail_url'] = $json->data->url;

        } catch(\Exception $e) {
            return $e->getMessage();
        }

        return $meta;
    }

    public function getGetButtonCss(Request $request) {
        if($request->get('site_id') == '') return false;
        $site_id = $request->get('site_id');
        $apiUrl = Site::find($site_id)->api_url;
        try {
            $returnData = (new CdsApiDriver($apiUrl, [
            ]))->send('getButtonCss')->getResult();
        } catch(\Exception $e) {
            $this->_flashMessage($request, '更新に失敗しました。もう一度ご確認ください。', 'danger');
            return $e;
        }

        return response()->json($returnData);
    }
    public function getNayjestFilterSearch(Request $request) {
        $response = array();
        $s = ($request->get('s') != '' ? $request->get('s') : '');
        $page = ($request->get('page') != '' ? $request->get('page') : 1);
        $field = ($request->get('field') != '' ? $request->get('field') : '');
        $id_user =$this->currentUser->id;
        $user_role = $this->currentUser->role;
        switch ($field) {
            case 'pid':
                $response = Post::getFilterID($s, $id_user, $page, $user_role);
                break;

            case 'kw':
                $response = Keywords::getFilterKeyword($s, $id_user, $page, $user_role);
                break;

            case 'user':
                $response = User::getFilterUser($s, $id_user, $page, $user_role);
                break;

            case 'site':
                $response = Site::getFilterSite($s, $id_user, $page, $user_role);
                break;
            // BEGIN selector filter for user, added by linhnc
            case 'user_id':
                $response = User::getFilterID($s, $id_user, $page, $user_role);
                break;
            case 'user_name':
                $response = User::getFilterUsername($s, $id_user, $page, $user_role);
                break;
            case 'user_crowdworks':
                $response = User::getFilterCrowdwork($s, $id_user, $page, $user_role);
                break;
            case 'user_role':
                $response = User::getFilterRole($s, $id_user, $page, $user_role);
                break;
            // END selector filter for user, added by linhnc
            case 'log_user_name':
                $response = ActivityNotification::getLogFilterUser($s, $id_user, $page, $user_role);
                break;
            case 'log_post_id':
                $response = ActivityNotification::getLogFilterPostId($s, $id_user, $page, $user_role);
                break;
        }
        return response()->json($response);
    }

    public function postValidatorHtml(Request $request)
    {
        $content = $request->get('content');
        if(!$content) {
            return response()->json(['error' => true, 'message' => '内容を入力してください']);
        }

        $validator = new Validator();

        $result = $validator->validateNodes($content);

        $response = ['error' => false];
        if ( $result->hasErrors() ) {
            $response = ['error' => true, 'message' => 'HTMLコードにエラーがあります。もう一度ご確認してください'];
        }

        return response()->json($response);
    }

    public function postIsDuplicatedTitle(Request $request) {
        $post = new Post();
        return ['duplicated' => !Post::isUniquePostByTitle($post, [
            'id' => $request->get('id', 0),
            'title' => $request->get('title', null),
            'site_id' => $request->get('site_id', 0),
        ])];
    }

}