<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/20/15
 * Time: 3:12 AM
 */

namespace App\APIs\DataFormatter\DataTypes;


use App\APIs\DataFormatter\IDataType;

class JSON implements IDataType
{

    public function format($data)
    {
        if(!is_array($data)) {
            return null;
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}