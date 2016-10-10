<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/parser.php';
require_once __DIR__ . '/functions.php';
$config = include __DIR__ . '/config.php';

ORM::configure('mysql:host=' . $config["dbhost"] . ';dbname=' . $config["dbname"]);
ORM::configure('username', $config['dbuser']);
ORM::configure('password', $config['dbpass']);


/*
    В начале проверяем, есть ли данные по погоде 
    за последние $config['cache_time'] часа по городу $config['city']
*/
$exist_data = ORM::for_table('changes')
    ->select('id')
    ->select('created')
    ->select('city')
    ->select('description')
    ->where([
        'city' => $config['city']
    ])
    ->where_raw('`created`  > UNIX_TIMESTAMP(ADDDATE(CURRENT_TIMESTAMP(), INTERVAL -' . $config["cache_time"] . ' HOUR))')
    ->find_one();


if (!$exist_data) {
    // получаем данные с сервера yandex
    $data = parse_yandex_weather($config['city']);

    // создаем запись в БД о данных по прогнозу в городе
    set_data_temperature($data, $config);

} else {
    // Получаем данные из базы
    $data = get_data_temperature($exist_data);
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Прогноз погоды с Yandex-погоды</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="container">
    <div class="row well">
        <h3>Погода на сегодня</h3>
        <small><?= $data['current_get_data'] ?></small>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?php foreach ($data['data'] as $item): ?>
                <?php if ($item['day_name_short'] == 'сегодня'): ?>
                    <p>Сегодня - <?= $item['day_short']; ?></p>
                    <p>Ожидаемая погода - <?= $item['day_comment_short']; ?></p>
                    <p>Рассвет - <?= $item['sunrise']; ?></p>
                    <p>Закат - <?= $item['sunset']; ?></p>

                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <td>Время суток</td>
                            <td>Температура</td>
                            <td>Погодные условия</td>
                            <td>Атмосферное давление</td>
                            <td>Ветер</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($item['details'] as $detail): ?>
                            <tr>
                                <td><?= $detail['day_part'] ?></td>
                                <td><?= $detail['temp'] ?></td>
                                <td><?= $detail['condition'] ?></td>
                                <td><?= $detail['air_presure'] ?></td>
                                <td><?= $detail['type_wind'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php break; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="row well">
        <h3>Прогноз на неделю</h3>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <td>День недели</td>
                <td>Краткая информация</td>
                <td>Подробная информация</td>
            </tr>
        </thead>
        <tbody>
            <?php for($i = 0; $i < 7; $i++): ?>
                <tr>
                    <td>
                        <div class="day_item <?php if($data['data'][$i]['day_name_short'] == 'сегодня'): ?>
                                                today
                                             <?php endif; ?>
                        "><?= $data['data'][$i]['day_short']; ?></div>
                        <span class="day_weekly"><?= $data['data'][$i]['day_weekley']; ?></span>
                    </td>
                    <td>
                        <div>Ожидаемая погода - <?= $item['day_comment_short']; ?></div>
                        <div>Рассвет - <?= $item['sunrise']; ?></div>
                        <div>Закат - <?= $item['sunset']; ?></div>
                    </td>
                    <td>
                        <?php foreach ($data['data'][$i]['details'] as $detail): ?>
                            <div class="day-part">
                                <span class="day_part"><?= $detail['day_part'] ?></span> - <?= $detail['temp'] ?>,
                                <span class="condition"><?= $detail['condition'] ?></span>,
                                ветер <?= $detail['type_wind'] ?> м/с,
                                атмосферное давление <?= $detail['air_presure'] ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>

</body>
</html>