<?php
namespace Sharoff\Mango\Api\Base;

use Carbon\Carbon;

/**
 * Class MangoOfficeStat
 *
 * @property array  $records
 * @property Carbon $start
 * @property Carbon $finish
 * @property string $from_extension
 * @property string $from_number
 * @property string $to_extension
 * @property string $to_number
 * @property string $disconnect_reason
 * @property string $entry_id
 *
 * @package Sharoff\Mango\Api\Base
 */
Class MangoOfficeStat {

    /**
     * Доступные поля
     *
     * @var array
     */
    protected $append_fields = [];

    /**
     * Поля, возможные для использования в статистике и нужные форматы
     *
     * @var array
     */
    protected $available_fields = [
        'records'           => 'array',
        'start'             => 'timestamp',
        'finish'            => 'timestamp',
        'from_extension'    => 'string',
        'from_number'       => 'string',
        'to_extension'      => 'string',
        'to_number'         => 'string',
        'disconnect_reason' => 'string',
        'entry_id'          => 'string',
    ];

    /**
     * Для получения нужной переменной
     *
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     */
    function __get($name) {
        if (isset($this->append_fields[$name])) {
            return $this->append_fields[$name];
        }
        throw new \Exception('Undefined param [' . $name . ']');
    }

    /**
     * Инициализация класса, передать массив с данными
     *
     * @param array $data
     */
    function __construct(array $data) {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    /**
     * Задание переменной и приведение ее к нужному формату
     *
     * @param $k
     * @param $v
     *
     * @return $this
     */
    protected function set($k, $v) {
        if (isset($this->available_fields[$k])) {
            switch ($this->available_fields[$k]) {
                case 'array':
                    $explode                 = explode(',', $v);
                    $this->append_fields[$k] = array_filter(
                        $explode,
                        function ($v) {
                            return (bool)$v;
                        }
                    );
                    break;
                case 'timestamp':
                    $this->append_fields[$k] = Carbon::createFromTimestamp($v);
                    break;
                default:
                    $this->append_fields[$k] = (string)$v;
                    break;
            }
        }
        return $this;
    }

}