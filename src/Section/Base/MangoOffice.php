<?php
namespace Sharoff\Mango\Api\Base;

use Carbon\Carbon;
use Sharoff\Mango\Api\MangoOfficeError;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MangoOffice
 * @package Sharoff\Mango\Api\Base
 */
Class MangoOffice {

    /**
     * @var string
     */
    protected $mango_base_url = 'https://app.mango-office.ru/vpbx/';
    /**
     * @var null|string
     */
    protected $vpbx_api_key = null;
    /**
     * @var null|string
     */
    protected $vpbx_api_salt = null;
    /**
     * @var null|Request
     */
    protected $request = null;


    /**
     * Инициализация класса
     *
     * @param null $vpbx_api_key
     * @param null $vpbx_api_salt
     */
    function __construct($vpbx_api_key = null, $vpbx_api_salt = null) {
        if (!is_null($vpbx_api_key)) {
            $this->vpbx_api_key = $vpbx_api_key;
        }
        if (!is_null($vpbx_api_salt)) {
            $this->vpbx_api_salt = $vpbx_api_salt;
        }
    }

    /**
     * Получение нужной переменной из переданных $_REQUEST данных
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    protected function getFromRequest($key, $default = null) {
        if (is_null($this->request)) {
            $this->request = Request::createFromGlobals();
        }
        return $this->request->get($key, $default);
    }

    /**
     * Задание ключа для работы с API
     *
     * @param $key
     *
     * @return $this
     */
    function setApiKey($key) {
        $this->vpbx_api_key = $key;
        return $this;
    }

    /**
     * Задание ключа шифрования для работы с API
     *
     * @param $salt
     *
     * @return $this
     */
    function setApiSalt($salt) {
        $this->vpbx_api_salt = $salt;
        return $this;
    }

    /**
     * Проверка подписи
     *
     * @param $json
     * @param $sign
     *
     * @return bool
     * @throws \Exception
     */
    function checkSalt($json, $sign) {
        $this->checkKey();
        $test = $this->getSign($json);
        return $sign == $test;
    }

    /**
     * Получение подписи по данным
     *
     * @param string|array $data
     *
     * @return string
     */
    protected function getSign($data) {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return hash('sha256', $this->vpbx_api_key . $data . $this->vpbx_api_salt);
    }

    /**
     * Проверка на наличие ключей приложения и шифрования
     *
     * @throws \Exception
     */
    protected function checkKey() {
        if (is_null($this->vpbx_api_key) || is_null($this->vpbx_api_salt)) {
            throw new \Exception('Необходимо задать API key и ключ шифрования');
        }
    }

    /**
     * Получить входящие данные
     *
     * @return mixed
     * @throws \Exception
     */
    function getMethodData() {
        $this->checkKey();

        $sign = $this->getFromRequest('sign');
        $json = $this->getFromRequest('json');

        if (!($this->getFromRequest('vpbx_api_key') == $this->vpbx_api_key)) {
            return MangoOfficeError::error(3105);
        }
        if (!$this->checkSalt($json, $sign)) {
            return MangoOfficeError::error(3102);
        }

        if ('POST' != $this->request->getMethod()) {
            return MangoOfficeError::error(3101);
        }
        return json_decode($json);
    }

    /**
     * @param      $command_id
     * @param      $from - (внутренний номер) идентификатор сотрудника ВАТС. Обязательное поле. Если у сотрудника ВАТС
     *        нет идентификатора (внутреннего номера), он не сможет выполнять команду инициирования вызова.
     * @param      $to_number - номер вызываемого абонента (строка не более 128 байт). Может быть
     * идентификатором сотрудника ВАТС, внутренним номером группы операторов ВАТС
     * или любым другим номером.
     * @param null $number - номер вызывающего абонента (строка не более 128 байт).
     * Опциональный параметр. Поле следует использовать в случае, если вызов
     * должен быть инициирован с номера, отличного от номера по умолчанию
     * сотрудника ВАТС. В качестве значения можно указывать: SIP из PSTN номера,
     * но нельзя указывать внутренние номера и номера групп ВАТС. К номеру будут
     * применены правила преобразования номеров ВАТС. Если будет указан номер,
     * отличный от номеров сотрудника ВАТС, которому соответствует поле
     * "extension", на время вызова этот номер будет считаться номером сотрудника.
     */
    function sendCall($from, $to_number, $number = null, $command_id = null) {
        $data = [
            'from'      => [
                'extension' => $from,
            ],
            'to_number' => $to_number
        ];
        if (!is_null($number)) {
            $data['from']['number'] = $number;
        }
        return $this->putCmd('commands/callback', $data, $command_id);
    }

    /**
     * Функция отправки команды на сервер манго-офиса
     *
     * @param       $method
     * @param array $data
     * @param null  $command_id
     *
     * @return mixed
     */
    protected function putCmd($method, array $data, $command_id = null) {
        if (false !== $command_id) {
            if (is_null($command_id)) {
                $command_id = md5($data . $this->getSign($data));
            }
            $data['command_id'] = $command_id;
        }
        $post = [
            'vpbx_api_key' => $this->vpbx_api_key,
            'sign'         => $this->getSign($data),
            'json'         => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ];

        $url   = $this->mango_base_url . $method;
        $query = http_build_query($post);
        if (0) {
            /**
             * FILE GET CONTENTS
             */
            $opts    = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $query
                ]
            ];
            $context = stream_context_create($opts);
            $data    = @file_get_contents($url, false, $context);
            var_dump($data);
            if (!$data) {
                return false;
            }
        }
        else {
            /**
             * CURL
             */
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
                $data = curl_exec($curl);
                curl_close($curl);
            }
        }
        if ($ret = json_decode($data)) {
            return $ret;
        }
        return $data;
    }


    /**
     * Завершить вызов
     *
     * @param $command_id - идентификатор команды (строка не более 128 байт).
     * @param $call_id - идентификатор вызова, который необходимо завершить.
     *
     * @return mixed
     */
    function sendCallHangup($command_id, $call_id) {
        $data = [
            'call_id' => $call_id
        ];
        return $this->putCmd('commands/call/hangup', $data, $command_id);
    }


    /**
     * Получение статистики
     *
     * @param      $date_from - предоставить статистику с указанного времени.
     * @param      $date_to - предоставить статистику по указанное время.
     * @param null $from - идентификатор сотрудника ВАТС для вызывающего абонента
     * @param null $from_number - номер вызывающего абонента (строка)
     * @param null $to -  идентификатор сотрудника ВАТС для вызываемого абонента
     * @param null $to_number - номер вызываемого абонента (строка)
     * @param null $fields - Позволяет указать какие поля (см. список
     * возможных полей ниже)и в каком порядке необходимо включить в выгрузку. Значение
     * по умолчанию: ["records", "start", "finish", "from_extension", "from_number",
     * "to_extension", "to_number", "disconnect_reason"]
     * @param null $request_id - идентификатор запроса (строка не более 128 байт)
     *
     * @return array|bool
     */
    function getStat($date_from, $date_to, $from = 0, $from_number = null, $to = null, $to_number = null, $fields = null, $request_id = null) {

        $data = [
            'date_from' => Carbon::parse($date_from)->timestamp,
            'date_to'   => Carbon::parse($date_to)->timestamp,
        ];
        if (!is_null($from)) {
            $data['from']['extension'] = $from;
        }

        if (!is_null($from_number)) {
            $data['from']['number'] = $from_number;
        }
        if (!is_null($to)) {
            $data['to']['extension'] = $to;
        }
        if (!is_null($to_number)) {
            $data['to']['number'] = $to_number;
        }
        if (is_null($request_id)) {
            $request_id = md5($data . $this->getSign($data));
        }
        if (is_null($fields)) {
            $fields = [
                'records',
                'start',
                'finish',
                'from_extension',
                'from_number',
                'to_extension',
                'to_number',
                'disconnect_reason'
            ];
        }
        $data['fields']     = implode(',', (array)$fields);
        $data['request_id'] = $request_id;

        $stat_key_data = $this->putCmd('stats/request', $data, false);

        if (!$stat_key_data->key) {
            return false;
        }

        $data = [
            'key'        => $stat_key_data->key,
            'request_id' => $request_id
        ];
        $info = $this->putCmd('stats/result', $data, false);

        if (isset($info->code)) {
            return MangoOfficeError::error($info->code);
        }

        return $this->getCsv($info, $fields);
    }

    /**
     * Преобразование CSV файла в массив из данных переданных в $fields
     *
     * @param $info
     * @param $fields
     *
     * @return array
     */
    protected function getCsv($info, $fields) {
        $ret   = [];
        $lines = explode("\n", $info);

        if (count($lines)) {
            foreach ($lines as $line) {
                if ($line) {
                    $values = explode(';', $line);
                    if (count($values)) {
                        $values = array_map(function ($v) { return trim(trim(trim($v), '['), ']'); }, $values);
                        $data   = array_combine(array_values($fields), array_values($values));
                        $ret[]  = new MangoOfficeStat($data);
                    }
                }
            }
        }
        return $ret;
    }
}