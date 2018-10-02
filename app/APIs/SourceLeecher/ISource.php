<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/22/15
 * Time: 8:56 PM
 */

namespace App\APIs\SourceLeecher;


interface ISource
{
    public function search($keyword, $page);
    public function getDownloadLink($url);
}