<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 11/20/15
 * Time: 3:07 AM
 */

namespace App\APIs\DataFormatter;


class DataFormatter
{
    private $type;
    private $data;

    public function __construct($type, $data) {
        $this->type = $type;
        $this->data = $data;
    }

    public function format() {
        try {
            $class = __NAMESPACE__."\\DataTypes\\".strtoupper($this->type);
            $formatter = new $class();
            return $formatter->format($this->data);
        } catch(\Exception $e) {
            return "Error when formatting";
        }
    }
}