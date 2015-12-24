<?php

namespace Sharoff\Mango\Api\Base;

Class MangoResponse {

    static function send($data, $code = 200) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        switch ($code) {
            case 420:
                header('HTTP/1.0 420 Method Failure');
                break;
            default:
                header('HTTP/1.1 200 OK');
                break;
        }
        echo $data;
        exit;
    }

}