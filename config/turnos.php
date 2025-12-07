<?php

return [
    'scraper' => [
        'base_url' => env('TURNOS_BASE_URL', 'https://colfarsfe.org.ar'),
        'page_url' => env('TURNOS_PAGE_URL', 'https://colfarsfe.org.ar/farmacias-de-turno/'),
        'timeout' => env('TURNOS_SCRAPER_TIMEOUT', 30),
    ],
    
    'downloader' => [
        'storage_path' => env('TURNOS_STORAGE_PATH', 'turnos/pdfs'),
        'cache_hours' => env('TURNOS_CACHE_HOURS', 24),
        'keep_last' => env('TURNOS_KEEP_LAST', 10),
        'timeout' => env('TURNOS_DOWNLOAD_TIMEOUT', 60),
    ],
    
    'auto_process' => env('TURNOS_AUTO_PROCESS', true),
];
