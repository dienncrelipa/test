<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/22/15
 * Time: 9:58 PM
 */

namespace App\APIs\SourceLeecher;


class SourceFactory implements ISource
{
    /**
     * @ISource sourceObject
     */
    private $sourceObject;

    public function __construct($sourceName) {
        $classString = __NAMESPACE__."\\Sources\\$sourceName";

        try {
            $this->sourceObject = new $classString();
        } catch(\Exception $e) {
            throw new \Exception('Source does not exist', 99991);
        }
    }

    public function search($keyword, $page)
    {
        return $this->sourceObject->search($keyword, $page);
    }
    public function getDownloadLink($url)
    {
        return $this->sourceObject->getDownloadLink($url);
    }
}