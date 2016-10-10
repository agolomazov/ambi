<?php

function parse_yandex_weather($city = 'moscow')
{
    // Целевая страница
    $url = 'https://yandex.ru/pogoda/' . $city;
// Детальная страница
    $url_detail = $url . '/details';

// Получаем данные с сервера Yandex
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($curl);
    curl_close($curl);

// Получаем детальные данные
    $curl = curl_init($url_detail);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_COOKIEJAR, __DIR__ . '/cookie');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    $data_detail = curl_exec($curl);
    curl_close($curl);

    $doc = phpQuery::newDocument($data);
    $doc_detail = phpQuery::newDocument($data_detail);

    $items = $doc->find('li.forecast-brief__item')->not('.forecast-brief__item_gap')
        ->not('.forecast-brief__item_tile');

    $all_data = [];
    $counter = 0;

// Получаем сокращенные данные
    foreach ($items as $el) {
        $pq = pq($el);

        // Получаем значение дня
        $day = $pq->find('.forecast-brief__item-day');
        $day = strip_tags($day);
        $all_data[$counter]['day_short'] = $day;

        // Название дня
        $day_name = $pq->find('.forecast-brief__item-day-name');
        $day_name = trim(strip_tags($day_name));
        $all_data[$counter]['day_name_short'] = $day_name;

        // Пояснение к прогнозу
        $day_comment = $pq->find('.forecast-brief__item-comment');
        $day_comment = trim(strip_tags($day_comment));
        $all_data[$counter]['day_comment_short'] = $day_comment;

        // Максимальная температура днем
        $max_temp_day = $pq->find('.forecast-brief__item-temp-day');
        $max_temp_day = trim(strip_tags($max_temp_day));
        $all_data[$counter]['max_temp_day_short'] = $max_temp_day;

        // Минимальная температура ночью
        $min_temp_night = $pq->find('.forecast-brief__item-temp-night');
        $min_temp_night = trim(strip_tags($min_temp_night));
        $all_data[$counter]['min_temp_day_short'] = $min_temp_night;
        $counter++;
    }

// Получаем детальные данные
    $detail_current = $doc_detail->find('.current-weather__col.current-weather__info');

// На какое время получены данные
    $current_get_data = trim(strip_tags($detail_current->find('.current-weather__info-row.current-weather__info-row_type_time')));

// Получаем контейнер со списком данных
    $dl_detail = $doc_detail->find('dl.forecast-detailed');
    $dt_list = $dl_detail->find('dt.forecast-detailed__day');
    $counter = 0;

// Получаем данные по дню
    foreach ($dt_list as $el) {
        $pq = pq($el);

        $day_weekley = trim(strip_tags($pq->find('small.forecast-detailed__weekday')));
        $all_data[$counter]['day_weekley'] = $day_weekley;

        $day_month = trim(strip_tags($pq->find('span.forecast-detailed__day-month')));
        $all_data[$counter]['day_month'] = $day_month;

        $day_number = (int)trim(strip_tags($pq->find('strong.forecast-detailed__day-number')));
        $all_data[$counter]['day_number'] = $day_number;
        $counter++;
    }

// Получаем детальный данные по температуре
    $temp_tables = $dl_detail->find('table.weather-table');
    $counter = 0;

    foreach ($temp_tables as $el) {

        $tr = pq('tr.weather-table__row', $el);
        $inner_counter = 0;
        // данные температуры и на какую часть суток
        foreach ($tr as $elem) {
            $td = pq('td.weather-table__body-cell', $elem);
            $all_data[$counter]['details'][$inner_counter]['day_part'] = trim(strip_tags($td->find('div.weather-table__daypart')));
            $all_data[$counter]['details'][$inner_counter]['temp'] = trim(strip_tags($td->find('div.weather-table__temp')));
            $inner_counter++;
        }

        $inner_counter = 0;
        // данные о погодных условиях
        foreach ($tr as $elem) {
            $td = pq('td.weather-table__body-cell_type_condition', $elem);
            $all_data[$counter]['details'][$inner_counter]['condition'] = trim(strip_tags($td->find('div.weather-table__value')));
            $inner_counter++;
        }

        $inner_counter = 0;
        // данные о давлении
        foreach ($tr as $elem) {
            $td = pq('td.weather-table__body-cell_type_air-pressure', $elem);
            $all_data[$counter]['details'][$inner_counter]['air_presure'] = trim(strip_tags($td->find('div.weather-table__value')));
            $inner_counter++;
        }

        $inner_counter = 0;
        // данные о ветре
        foreach ($tr as $elem) {
            $td = pq('td.weather-table__body-cell_type_wind', $elem);
            $all_data[$counter]['details'][$inner_counter]['type_wind'] = trim(strip_tags($td->find('div.weather-table__value')));
            $inner_counter++;
        }


        $counter++;
    }

    $day_detail = $dl_detail->find('dd.forecast-detailed__day-info');
    $counter = 0;

    foreach ($day_detail as $el) {
        $pq = pq($el);
        $all_data[$counter]['sunset'] = trim(strip_tags($pq->find('div.forecast-detailed__sunset_simple div.forecast-detailed__value')));
        $all_data[$counter]['sunrise'] = trim(strip_tags($pq->find('div.forecast-detailed__sunrise_simple div.forecast-detailed__value')));
    }

    return [
        'data' => $all_data,
        'current_get_data' => $current_get_data
    ];
}