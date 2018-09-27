<?php
/**
 * Created by PhpStorm.
 * User: macintosh
 * Date: 6/8/16
 * Time: 2:12 PM
 */

namespace App\Libs\MessagesContainer;


class ErrorHTML
{
    public static function render(Error $error) {
        echo '<div class="alert alert-danger">'.$error->getMessage().'</div>';
    }

    public static function renderSet(ErrorSet $errorSet) {
        echo '<div class="alert alert-danger">';
        echo '<ul>';
        while($errorSet->hasNext()) {
            echo '<li>'.$errorSet->next()->getMessage().'</li>';
        }
        echo '</ul>';
        echo '</div>';
    }
}