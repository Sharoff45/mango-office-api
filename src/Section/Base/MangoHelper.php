<?php
namespace Sharoff\Mango\Api\Base;

use Sharoff\Mango\Api\MangoOffice;

/**
 *
 * Class MangoHelper
 *
 * @method static MangoOffice setApiKey($key)
 * @method static MangoOffice setApiSalt($salt)
 * @method static MangoOffice checkSalt($json, $salt)
 * @method static MangoOffice getMethodData()
 * @method static MangoOffice sendCall($from, $to_number, $number = null, $command_id = null)
 * @method static MangoOffice sendCallHangup($command_id, $call_id)
 * @method static MangoOffice getStat($date_from, $date_to, $from = 0, $from_number = null, $to = null, $to_number = null, $fields = null, $request_id = null)
 *
 * @package Sharoff\Mango\Api\Base
 */
Class MangoHelper {

    /**
     * @var null|MangoOffice
     */
    static protected $instance = null;


    static protected function factory() {
        if (is_null(self::$instance)) {
            self::$instance = new MangoOffice();
        }
        return self::$instance;
    }


    /**
     *
     * @param $method
     * @param $args
     *
     * @return MangoOffice
     * @throws \Exception
     */
    static function __callStatic($method, $args) {
        return call_user_func_array(
            [
                self::factory(),
                $method
            ],
            $args
        );
    }


}