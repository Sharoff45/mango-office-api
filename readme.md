#Библиотека для работы с API mango-office.ru

##Установка
~~~
composer require sharoff/mango-office-api
~~~

##Инициализация
Для простой и быстрой инициализации создан Helper который хранить в себе инстанс класса работающий с API.
Для первоначальной инициализации скрипта необходимо задать API ключ и ключ шифрования.
~~~
// Подключаем автолоад
require __DIR__ . '/../vendor/autoload.php';
// Просто для короткой записи
use Sharoff\Mango\Api\MangoHelper;
// Задание API ключа и ключа шифрования
MangoHelper::setApiKey('*********************************')
           ->setApiSalt('*********************************');
~~~
Для удобства использования создан PHP DOC, который позволяет работать автозаполнению в IDE

##Получение входящих данных
Mango-office обращается к определенному URL адресу. Каждый адрес прописан в документации (http://www.mango-office.ru/upload/api/MangoOffice_VPBX_API_v1.3.pdf)
Данная библиотека поможет проверить подпись от манго и если что-то пойдет не так, сделает ответ в формате json, нужным кодом и заголовком.
Для получения данных достаточно после инициализации выполнить строчку:
~~~
$data = MangoHelper::getMethodData();
~~~
В переменную $data придет json_decode полученных данных от манго.

##Совершение звонка
Для совершения звонка достаточно знать внутренний номер сотрудника и кому вы хотите совершить вызов:
~~~
$data = MangoHelper::sendCall('10', '7912*******');
~~~

##Завершение звонка
Для завершения звонка необходимо выполнить команду:
~~~
MangoHelper::sendCallHangup($command_id, $call_id)
~~~
Где $command_id и $call_id придут при совершении звонка

##Получение статистики
В результате выполнения команды будет массив, с объектами MangoOfficeStat, с помощью которого можно получить с автокомплитом нужные параметры
Данные объекта будут в формате как и $fields
~~~
$stats = MangoHelper::getStat($date_from, $date_to, $from = 0, $from_number = null, $to = null, $to_number = null, $fields = null, $request_id = null);
foreach ($stats as $stat) {
    /** @var \Sharoff\Mango\Api\MangoOfficeStat $stat */
    echo $stat->start->format('d.m.Y H:i:s') . PHP_EOL;
}
~~~
###Параметры по умолчанию $fields
~~~
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
~~~
###Возможные поля $fields и формат полей
~~~
$available_fields = [
    // Массив с идентификаторами записей
    'records'           => 'array',
    // Будет в объекте Carbon 
    'start'             => 'timestamp',
    // Будет в объекте Carbon
    'finish'            => 'timestamp',
    // строка
    'from_extension'    => 'string',
    // строка
    'from_number'       => 'string',
    // строка
    'to_extension'      => 'string',
    // строка
    'to_number'         => 'string',
    // строка
    'disconnect_reason' => 'string',
    // строка
    'entry_id'          => 'string',
];
~~~
