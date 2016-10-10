<?php

/**
 *  Записываем данные о прогнозе погоды в базу данных
 *
 * @param $data - данные для записи
 * @param $config - данные конгфига
 */
function set_data_temperature($data, $config)
{
    $change = ORM::for_table('changes')->create();

    $change->city = $config['city'];
    $change->description = $data['current_get_data'];
    $change->save();
    // Сделали запись в логах

    foreach ($data['data'] as $item) {
        $weather = ORM::for_table('weathers')->create();
        $weather->day_short = $item['day_short'];
        $weather->day_name_short = $item['day_name_short'];
        $weather->day_comment_short = $item['day_comment_short'];
        $weather->max_temp_day_short = $item['max_temp_day_short'];
        $weather->min_temp_day_short = $item['min_temp_day_short'];
        $weather->day_weekley = $item['day_weekley'];
        $weather->day_month = $item['day_month'];
        $weather->day_number = $item['day_number'];
        $weather->change_id = $change->id;

        if (array_key_exists('sunset', $item)) {
            $weather->sunset = $item['sunset'];
        }

        if (array_key_exists('sunrise', $item)) {
            $weather->sunrise = $item['sunrise'];
        }

        $weather->save();

        $weather_id = $weather->id;
        foreach ($item['details'] as $weather_item) {
            $weather_detail_item = ORM::for_table('weather_details')->create();
            $weather_detail_item->day_part = $weather_item['day_part'];
            $weather_detail_item->temp = $weather_item['temp'];
            $weather_detail_item->condition = $weather_item['condition'];
            $weather_detail_item->air_presure = $weather_item['air_presure'];
            $weather_detail_item->type_wind = $weather_item['type_wind'];
            $weather_detail_item->weather_id = $weather_id;
            $weather_detail_item->save();
        }

    }
}

/**
 *  Получаем данные о прогнозах
 *
 * @param $exist_data
 *
 * @return array - данные о прогнозах
 */
function get_data_temperature($exist_data)
{

    $data = [];
    // собираем данные из бд
    $data['current_get_data'] = $exist_data->description;

    // Получаем данные о прогнозе
    $weathers = ORM::for_table('weathers')
        ->where('change_id', $exist_data->id)
        ->find_many();

    $counter = 0;
    foreach ($weathers as $weather) {
        $data['data'][$counter]['day_short'] = $weather->day_short;
        $data['data'][$counter]['day_name_short'] = $weather->day_name_short;
        $data['data'][$counter]['day_comment_short'] = $weather->day_comment_short;
        $data['data'][$counter]['max_temp_day_short'] = $weather->max_temp_day_short;
        $data['data'][$counter]['min_temp_day_short'] = $weather->min_temp_day_short;
        $data['data'][$counter]['day_weekley'] = $weather->day_weekley;
        $data['data'][$counter]['day_month'] = $weather->day_month;
        $data['data'][$counter]['day_number'] = $weather->day_number;

        if ($weather->sunset) {
            $data['data'][$counter]['sunset'] = $weather->sunset;
        }

        if ($weather->sunrise) {
            $data['data'][$counter]['sunrise'] = $weather->sunrise;
        }

        // Получаем подробные данные о прогнозе
        $weather_details = ORM::for_table('weather_details')
            ->where('weather_id', $weather->id)->find_many();
        // внутренний счетчик
        $inner_counter = 0;
        foreach ($weather_details as $detail_item) {
            $data['data'][$counter]['details'][$inner_counter]['day_part'] = $detail_item->day_part;
            $data['data'][$counter]['details'][$inner_counter]['temp'] = $detail_item->temp;
            $data['data'][$counter]['details'][$inner_counter]['condition'] = $detail_item->condition;
            $data['data'][$counter]['details'][$inner_counter]['air_presure'] = $detail_item->air_presure;
            $data['data'][$counter]['details'][$inner_counter]['type_wind'] = $detail_item->type_wind;
            $inner_counter++;
        }


        $counter++;
    }

    return $data;

}