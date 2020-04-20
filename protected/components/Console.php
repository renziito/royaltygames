<?php

/**
 * @author Nolberto Vilchez Moreno <jnolbertovm@gmail.com>
 */
class Console
{
    public static function log($message, $exit = false, $title = 'Datos')
    {
        echo "<code class='code'><b>{$title} :</b></code>";
        echo '<pre charset="utf8">';
        print_r($message);
        echo '</pre>';

        if ($exit) {
            exit();
        }
    }

    public static function debug($message, $exit = false, $title = 'Datos')
    {
        echo "<code class='code'><b>{$title} :</b></code>";
        echo '<pre charset="utf8">';
        var_dump($message);
        echo '</pre>';

        if ($exit) {
            exit();
        }
    }
}
