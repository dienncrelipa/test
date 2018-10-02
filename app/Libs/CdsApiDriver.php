<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/20/16
 * Time: 10:26 AM
 */

namespace App\Libs;


use Illuminate\Support\Facades\Log;

class CdsApiDriver
{
    public $url, $data, $content, $jsonData, $statusCode;
    public $action = [
        'ping'       => 'ping',
        'categories' => 'get_category_list',
        'publish'    => 'create_post',
        'edit'       => 'update_post',
        'delete'     => 'delete_post',
        'updateCss'  => 'update_css',
        'getCssUrl'  => 'get_css_url',
        'preview'    => 'register_preview',
        'cathierarchical' => 'get_cagetories_hierarchical',
        'getButtonCss' => 'get_button_css',
        'removeOptionPostOfCat' => 'remove_option_post_of_cat',
        'similarPostsList' => 'wp_similar_post_title_check_action'
    ];

    public $actionNeedToBeVerified = ['delete'];
    private $transactionId = null;
    private $transactionTrackingUrl = null;
    private $isSent = false;

    const TRANSACTION_OK = 1;
    const TRANSACTION_PROCESSING = 2;
    const TRANSACTION_NOT_FOUND = 3;
    const TRANSACTION_ERROR_WHEN_VALIDATE = 4;

    public function __construct($url, $data = []) {
        $this->url = $url;
        $this->data = $data;
    }

    public function send($action) {
        $this->transaction();
        $client = new \GuzzleHttp\Client(['http_errors' => false]);

        $data = $this->{"data".ucfirst($action)}();
        $data = CdsDriverEventListener\EventListener::handle($data);

        $tryRemain = 3;

        $error = false;

        while($tryRemain >= 0) {
            $response = $client->request('POST', $this->url, [
                'form_params' => [
                    'action' => $this->action[$action],
                    'data' => json_encode($data),
                    'transaction_id' => $this->transactionId
                ]
            ]);

            $this->statusCode = $response->getStatusCode();
            $this->content = $response->getBody()->getContents();
            $this->jsonData = json_decode($this->content);

            $tryRemain--;

            if($this->statusCode != 200) {
                Log::debug($action.": {$this->statusCode} return. Re-trying");
                sleep(1); // Maybe there is some trouble with network, better wait a moment
                continue;
            }

            $this->isSent = true;
            if(isset($this->jsonData->transaction)) {
                $this->transactionTrackingUrl = $this->jsonData->transaction->raw_status_url;
            }

            if(($transactionStatus = $this->getTransactionStatus($action)) != self::TRANSACTION_OK) {
                Log::debug("Transaction {$this->transactionId} status is $transactionStatus");
                continue;
            }

            if($this->transactionTrackingUrl != null) {
                $notValid = !$this->verifyResult($action);
                if($notValid) {
                    Log::debug($action." is ".(($notValid) ? "not valid" : "valid").". Request: ".json_encode($this->data).". Response: ".$this->content);
                    continue;
                }
            }

            break;
        }

        return $this;
    }

    public function transaction() {
        $this->transactionId = str_random(64);
        return $this;
    }

    public function getTransactionStatus($action) {
        if($this->transactionTrackingUrl == null || $this->isSent == false) {
            return self::TRANSACTION_OK;
        }

        $client = new \GuzzleHttp\Client(['http_errors' => false]);

        $response = $client->request('POST', $this->transactionTrackingUrl, [
            'form_params' => [
                'action' => 'transaction_status',
                'transaction_id' => $this->transactionId
            ]
        ]);

        $content = $response->getBody()->getContents();
        $jsonData = json_decode($content);

        Log::debug("Transaction {$this->transactionId} for action $action content: $content");

        if($response->getStatusCode() != 200) {
            return self::TRANSACTION_NOT_FOUND;
        }

        if(!is_object($jsonData)) {
            return self::TRANSACTION_ERROR_WHEN_VALIDATE;
        }

        if(!isset($jsonData->status)) {
            return self::TRANSACTION_ERROR_WHEN_VALIDATE;
        }

        return ($jsonData->status == 'done') ? self::TRANSACTION_OK : self::TRANSACTION_PROCESSING;
    }

    public function verifyResult($action) {
        if(array_search($action, $this->actionNeedToBeVerified) === false) {
            return true;
        }

        return $this->{"verify".ucfirst($action)}();
    }

    public function verifyDelete() {
        if(!is_object($this->jsonData)) {
            return false;
        }

        if(isset($this->jsonData->error)) {
            return false;
        }

        if(!isset($this->jsonData->result->data)) {
            return false;
        }

        return true;
    }

    public function getResult() {
        if(!is_object($this->jsonData)) {
            throw new \Exception('Data error not object');
        }

        if(!isset($this->jsonData->result)) {
            throw new \Exception('Data has error');
        }

        if(isset($this->jsonData->result->error)) {
            throw new \Exception($this->jsonData->result->error->code);
        }

        return $this->jsonData->result;
    }

    public function dataPing() {
        return [];
    }

    public function dataCategories() {
        return [];
    }
    
    public function dataCathierarchical() {
        return [];
    }

    public function dataPublish() {
        return [
            'post_content'  => base64_encode($this->convertTagsPostContent($this->data['content'])),
            'post_excerpt' => base64_encode($this->data['post_excerpt']),
            'post_title'    => base64_encode($this->data['title']),
            'categories'    => $this->data['categories'],
            'post_status'   => $this->data['post_status'],
            'feature_img'   => $this->data['feature_img'],
            'author_name'   => $this->data['author_name'],
            'cback_url'     => $this->data['cback_url'],
            'old_post_id'   => isset($this->data['old_post_id']) ? $this->data['old_post_id'] : 0,
            'priority'      => $this->data['priority'],
        ];
    }

    protected function convertTagsPostContent($postContent, $dataTagReplace = []) {
        $dataTag = [
            '<red>'     => '<strong class="red">',
            '</red>'    => '</strong>',
            '<yellow>'  => '<strong class="yellow">',
            '</yellow>' => '</strong>',
            '<red class="redactor-inline-converted">'    => '<strong class="red">',
            '<yellow class="redactor-inline-converted">' => '<strong class="yellow">'
        ];

        $dataTag = array_merge($dataTag, $dataTagReplace);

        if (empty($dataTag)) {
            return $postContent;
        }

        foreach ($dataTag as $tag => $tagConvert) {
            $postContent = str_replace($tag, $tagConvert, $postContent);
        }

        return $postContent;
    }

    public function dataEdit() {
        return array_merge($this->dataPublish(), ['ID' => $this->data['post_id']]);
    }

    public function dataDelete() {
        return [
            'ID' => $this->data['post_id']
        ];
    }

    public function dataPreview() {
        return $this->dataPublish();
    }

    public function dataUpdateCss() {
        return [
            'css' => base64_encode($this->data['css'])
        ];
    }

    public function dataGetCssUrl() {
        return [];
    }

    public function dataGetButtonCss() {
        return [];
    }

    public function dataRemoveOptionPostOfCat() {
        return [];
    }

    public function dataSimilarPostsList() {
        return [];
    }
}