<?php

class Response {

    /**
     * @param bool   $error   Error de la consulta
     * @param int    $code    Código de respuesta http
     * @param string $message Mensaje que detalla la consulta
     * @param array  $data    Datos obtenidos de la consulta
     *
     * @return string
     */
    public static function JSON($error = false, $code = 200, $message = '', $data = [], $onlyData = false) {
        $response = new stdClass;
        $response->message = $message;
        $response->error = $error;
        $response->code = (!is_numeric($code)) ? 500 : $code;

        foreach ($data as $key => $value) {
            $response->{$key} = $value;
        }

        header('Content-type: application/json; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');
        http_response_code($response->code);

        echo CJSON::encode($onlyData ? $data : $response);

        //ob_clean();
        //flush();

        Yii::app()->end();
    }

    /**
     * Responde con formato json una excepcion capturada por un catch.
     *
     * @param object $ex Parametro Exception de bloque catch
     */
    public static function Error($ex, $responseJSON = true) {
        $code = $ex->getCode();
        $codes = [
            100, 101, 102, 103,
            200, 201, 202, 203, 204, 205, 206, 207, 208,
            300, 301, 302, 303, 304, 305, 306, 307, 308,
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413,
            414, 415, 416, 417, 418, 422, 423, 424, 425, 426, 428, 429, 431, 449, 451,
            500, 501, 502, 503, 504, 505, 506, 507, 508, 509, 510, 511, 512,
        ];

        if (!in_array($code, $codes)) {
            $code = 500;
        }

        if ($responseJSON) {
            self::JSON(true, $code, $ex->getMessage());
        } else {
            throw new CHttpException($code, $ex->getMessage());
        }
    }

}
