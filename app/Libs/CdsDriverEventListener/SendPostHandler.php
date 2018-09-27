<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 10/19/17
 * Time: 10:32 AM
 */

namespace App\Libs\CdsDriverEventListener;

class SendPostHandler implements BaseHandler
{
    public function handle($data)
    {
        if(!isset($data['post_content'])) {
            return $data;
        }

        $postContent = base64_decode($data['post_content']);
        preg_match_all('/(?:<iframe[^>]*)(?:(?:\/>)|(?:>.*?<\/iframe>))/', $postContent, $iframeContents);

        foreach($iframeContents[0] as $iframe) {
            preg_match('/class="([^"]+)"/', $iframe, $class);
            preg_match('/data-blockquote-url="([^"]+)"/', $iframe, $blockquoteUrl);
            if(!(isset($class[1]) && isset($blockquoteUrl[1]) && $class[1] == 'pdb-product-iframe')) {
                continue;
            }

            $blockquoteContent = $this->getBlockquoteContent($blockquoteUrl[1]);
            if(!$blockquoteContent) {
                continue;
            }

            $postContent = str_replace($iframe, '<div>'.$blockquoteContent.'</div>', $postContent);
            $data['post_content'] = base64_encode($postContent);
        }

        return $data;
    }

    private function getBlockquoteContent($blockquoteUrl) {
        try {
            return file_get_contents($blockquoteUrl);
        } catch(\Exception $e) {
            return false;
        }
    }
}