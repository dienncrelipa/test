<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 1:55 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function returnOrEmpty($model) {
        return !empty($model) ? $model : new self();
    }

    public function isValid() {
        return !empty($this->{$this->primaryKey});
    }
}