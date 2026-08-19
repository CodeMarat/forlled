<?php

return [
    'max_upload_kb' => 204800,
    'main_width' => 4200,
    'jpeg_quality' => 90,
    'webp_quality' => 95,
    'video_max_width' => 1920,
    'video_crf' => 22,
    'video_audio_bitrate' => '128k',
    'video_hls_segment_time' => 6,
    'video_hls_playlist_name' => 'master.m3u8',
    'video_hls_segment_directory' => 'segments',
    'video_extensions' => [
        'mov',
        'avi',
        'wmv',
        'webm',
        'm4v',
        'mkv',
        'ogv',
        '3gp',
        'mpg',
        'mpeg',
    ],
    'variants_directory' => 'variants',
];
