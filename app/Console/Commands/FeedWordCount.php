<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 3/30/17
 * Time: 2:23 PM
 */

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class FeedWordCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:wordcount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach(Post::select('id', 'content', 'description')->orderBy('id', 'DESC')->cursor() as $post) {
            $descLength = mb_strlen(str_replace("\n", "", trim($post->description)), 'UTF-8');
            $contentLength = $this->countWord($post->content);
            $totalCount =  $contentLength + $descLength;
            $post->word_count = $totalCount;
            $post->save();
            echo "{$post->id} : {$totalCount} ({$contentLength} + {$descLength}) \n";
        }
    }

    private function countWord($text) {
        $cases = [
            'HTMLRegExp' => '/<\/?[a-z][^>]*?>/',
            'HTMLcommentRegExp' => '/<!--[\s\S]*?-->/',
            'spaceRegExp' => '/&nbsp;|&#160;/',
            'HTMLEntityRegExp' => '/&\S+?;/',
            'connectorRegExp' => '/--|\u2014/',
            'removeRegExp' => [
                '[',
                // Basic Latin (extract)
                '\u0021-\u0040\u005B-\u0060\u007B-\u007E',
                // Latin-1 Supplement (extract)
                '\u0080-\u00BF\u00D7\u00F7',
                // General Punctuation
                '\u2000-\u2BFF',
                // Supplemental Punctuation
                '\u2E00-\u2E7F',
                ']'
            ],
            'astralRegExp' => '/[\x{E800}-\x{EBFF}][\x{EC00}-\x{EFFF}]/u',
            'wordsRegExp' => '/\S\s+/',
            'characters_excluding_spacesRegExp' => '/\S/',
            'characters_including_spacesRegExp' => '/[^\f\n\r\t\v\x{00AD}\x{2028}\x{2029}]/u',
        ];

        $text .= "\n";
        $text = preg_replace('/ \t/', 'a', $text);
        $text = preg_replace('/\x03/', 'a', $text);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$text);
        libxml_use_internal_errors(false);

        $elementsToDelete = [];
        foreach(['style', 'script', 'blockquote', 'iframe'] as $removeTag) {
            foreach($dom->getElementsByTagName($removeTag) as $element) {
                $elementsToDelete[] = $element;
            }
        }

        $divClassesToDelete = ['ranking', 'url-debugger', 'head_title_block', 'insta_info'];
        foreach($dom->getElementsByTagName('div') as $div) {
            if($div->attributes->getNamedItem('class') === null) {
                continue;
            }
            $divClasses = explode(" ", $div->attributes->getNamedItem('class')->nodeValue);

            if(count(array_intersect($divClassesToDelete, $divClasses)) > 0) {
                $elementsToDelete[] = $div;
            }
        }

        foreach($dom->getElementsByTagName('p') as $pElement) {
            if($pElement->attributes->getNamedItem('class') === null) {
                continue;
            }
            $pClasses = explode(" ", $pElement->attributes->getNamedItem('class')->nodeValue);

            if(array_search('quote-origin-redactor', $pClasses) !== false) {
                $elementsToDelete[] = $pElement;
            }
        }

        foreach($elementsToDelete as $element) {
            $element->parentNode->removeChild($element);
        }

        $text = $dom->saveHTML($dom->documentElement);

        $text = preg_replace($cases['HTMLRegExp'], "\n", $text);
        $text = preg_replace($cases['HTMLcommentRegExp'], '', $text);
        $text = preg_replace($cases['spaceRegExp'], ' ', $text);
        $text = preg_replace($cases['HTMLEntityRegExp'], 'a', $text);
        $text = preg_replace($cases['astralRegExp'], 'a', $text);
        $text = preg_replace('/\x{200B}/u', '', $text);

        $text = strip_tags($text);

        preg_match_all($cases['characters_including_spacesRegExp'], $text, $count);

        return count($count[0]);
    }
}
