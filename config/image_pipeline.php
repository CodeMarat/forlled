<?php

return [
    'max_upload_kb' => 102400,
    'main_width' => 4200,
    'jpeg_quality' => 90,
    'webp_quality' => 88,
    'variants_directory' => 'variants',
    'variants' => [
        'card' => [
            'width' => 1200,
            'format' => 'source',
            'jpeg_quality' => 84,
            'webp_quality' => 82,
        ],
        'card_webp' => [
            'width' => 1200,
            'format' => 'webp',
            'quality' => 82,
        ],
        'detail_webp' => [
            'width' => 4200,
            'format' => 'webp',
            'quality' => 88,
        ],
    ],
];
