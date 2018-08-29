<?php
return [
    "version"      => '1.0',
    'autoInitBean' => true,
    'bootScan'      => [
        'Queue'            => BASE_PATH . "/../src/Bootstrap",
    ],
    'beanScan'     => [
        'Queue'            => BASE_PATH . "/../src",
    ],
];