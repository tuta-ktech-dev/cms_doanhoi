<?php

return [

    'label' => 'Điều hướng phân trang',

    'overview' => '{1} Hiển thị 1 kết quả|[2,*] Hiển thị :first đến :last trong tổng :total kết quả',

    'fields' => [

        'records_per_page' => [

            'label' => 'Mỗi trang',

            'options' => [
                'all' => 'Tất cả',
            ],

        ],

    ],

    'actions' => [

        'first' => [
            'label' => 'Đầu tiên',
        ],

        'go_to_page' => [
            'label' => 'Tới trang :page',
        ],

        'last' => [
            'label' => 'Cuối cùng',
        ],

        'next' => [
            'label' => 'Sau',
        ],

        'previous' => [
            'label' => 'Trước',
        ],

    ],

];

