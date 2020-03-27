<?php

/**
 * @package Sismonitor\Components
 */
class Utils {

    public static $busqueda = [
        'select'    => '*',
        'condition' => '',
        'params'    => [],
        'order'     => ''
    ];

    public static function show($data, $detenerProcesos = false, $titulo = 'Datos') {
        echo "<code><b>{$titulo} :</b></code>";
        echo "<pre>";
        print_r($data);
        echo '</pre>';
        if ($detenerProcesos) {
            die();
        }
    }

    public static function setBusqueda($buscar, $select = false, $order = false) {
        $condition = '';
        $params    = [];
        if (is_array($buscar)) {
            $and = 'AND ';
            foreach ($buscar as $key => $val) {
                $valor = Utils::reset_string($key);
                if (is_array($val)) {
                    $condition .= $val['query'] . $and;
                } else {
                    if ($val == ':isnull') {
                        $condition .= "{$key} is null {$and}";
                    } else {
                        if ($val == ':nonull') {
                            $condition .= "{$key} is not null {$and}";
                        } else {
                            $condition           .= "{$key} = :{$valor} {$and}";
                            $params[":{$valor}"] = $val;
                        }
                    }
                }
            }

            $condition                   = substr($condition, 0, -(strlen($and)));
            self::$busqueda['condition'] = $condition;
            self::$busqueda['params']    = $params;
        } else {
            $condition = $buscar;
            $params    = $buscar;
        }


        if ($order) {
            self::$busqueda['order'] = $order;
        }

        if ($select) {
            self::$busqueda['select'] = $select;
        }
    }

    public static function getBusqueda() {
        return self::$busqueda;
    }

    /**
     * Reinicia la cadena de caracteres raros.
     * @param string $string
     * @return string
     */
    public static function reset_string($string, $spaces = false) {

        $string = trim($string);

        $string = str_replace(
                array('Ã¡', 'Ã ', 'Ã¤', 'Ã¢', 'Âª', 'Ã', 'Ã€', 'Ã‚', 'Ã„'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string
        );

        $string = str_replace(
                array('Ã©', 'Ã¨', 'Ã«', 'Ãª', 'Ã‰', 'Ãˆ', 'ÃŠ', 'Ã‹'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string
        );

        $string = str_replace(
                array('Ã­', 'Ã¬', 'Ã¯', 'Ã®', 'Ã', 'ÃŒ', 'Ã', 'ÃŽ'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string
        );

        $string = str_replace(
                array('Ã³', 'Ã²', 'Ã¶', 'Ã´', 'Ã“', 'Ã’', 'Ã–', 'Ã”'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string
        );

        $string = str_replace(
                array('Ãº', 'Ã¹', 'Ã¼', 'Ã»', 'Ãš', 'Ã™', 'Ã›', 'Ãœ'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string
        );

        $string = str_replace(
                array('Ã±', 'Ã‘', 'Ã§', 'Ã‡'), array('n', 'N', 'c', 'C'), $string
        );

//Esta parte se encarga de eliminar cualquier caracter extraÃ±o
        $string = str_replace(
                array("\\", "Â¨", "Âº", "-", "~",
                    "#", "@", "|", "!", "\"",
                    "Â·", "$", "%", "&", "/",
                    "(", ")", "?", "'", "Â¡",
                    "Â¿", "^", "`",
                    "+", "}", "{", "Â¨", "Â´",
                    ">", "< ", ";", ",", ":",
                    ".", " "), '', $string
        );

        if ($spaces) {
            $string = str_replace(' ', '', $string);
        }

        return $string;
    }

    /**
     * Funcion que limita los caracteres de una cadena.
     * @param type $string cadena de texto completa
     * @param type $limit cantidad de caracteres para limitar la cadena
     * @param string $ellipsis variable que indica como terminar el texto
     * @return string
     */
    public static function limitcharacters($string, $limit = 10, $ellipsis = "...") {
        $cadena = substr($string, 0, $limit);

        $longitud = strlen($string);

        if ($longitud > $limit) {
            return $cadena . $ellipsis;
        } else {
            return $cadena;
        }
    }

    public static function getMonth($mes) {
        $meses = [
            '',
            'Ene',
            'Feb',
            'Mar',
            'Abr',
            'May',
            'Jun',
            'Jul',
            'Ago',
            'Set',
            'Oct',
            'Nov',
            'Dic',
        ];
        return $meses[$mes];
    }

    public static function getUbicacion($query) {
        $url = "https://geocoder.api.here.com/6.2/geocode.json?app_id=04pa1GDaTRk78dVz6iQk%20&app_code=Xmz8Y3-heGExorerdas-Uw%20&searchtext=" . $query;

        $json = file_get_contents($url);

        return json_decode($json, true);
    }

    public static function getIcons() {
        $iconos = Icon::model()->findAll('state = 1 ORDER BY name');
        $return = [];
        foreach ($iconos as $icono) {
            if (!(strpos($icono->name, 'Outlined') || strpos($icono->name, 'Outline') )) {
                $return[$icono->icon] = '#x' . $icono->unicode . '; ' . $icono->name;
            }
        }
        return $return;
    }

    public static function Slugify($string, $date = false, $short = false) {
        if ($string != "") {
            $characters = array(
                "Á" => "A", "Ç" => "c", "É" => "e", "Í" => "i", "Ñ" => "n", "Ó" => "o", "Ú" => "u",
                "á" => "a", "ç" => "c", "é" => "e", "í" => "i", "ñ" => "n", "ó" => "o", "ú" => "u",
                "à" => "a", "è" => "e", "ì" => "i", "ò" => "o", "ù" => "u"
            );
            if ($short) {
                $string = (strlen($string) > $short ? substr($string, 0, $short) : $string);
            }

            $string = str_replace(["\\", "Â¨", "Âº", "-", "~", "#", "@", "|", "!", "\"",
                "Â·", "$", "%", "&", "/", "(", ")", "?", "'", "Â¡", "Â¿", "^", "`", "+", "}", "{", "Â¨", "Â´",
                ">", "< ", ";", ",", ":", ".", " "], '', $string);
            $string = strtr($string, $characters);
            $string = strtolower(trim($string));
            $string = preg_replace("/[^a-z0-9-]/", "-", $string);
            $string = preg_replace("/-+/", "-", $string);

            if (substr($string, strlen($string) - 1, strlen($string)) === "-") {
                $string = substr($string, 0, strlen($string) - 1);
            }
            $string .= ($date) ? date('dmH', strtotime($date)) : '';
        }

        return $string;
    }

}
