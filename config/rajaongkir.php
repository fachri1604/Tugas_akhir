<?php

return [

'api_key' => env('RAJAONGKIR_API_KEY', ''),
'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
'origin' => env('RAJAONGKIR_ORIGIN', 4911),
'allowed_couriers' => ['jne','sicepat','jnt'],

];
