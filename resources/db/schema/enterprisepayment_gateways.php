<?php
/**
 *
 * Schema definition for 'enterprisepayment_gateways'
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['enterprisepayment_gateways'] = [
    'id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'name' => [
        'type' => 'varchar(255)',
    ],
    'code' => [
        'type' => 'varchar(255)',
    ],
    'is_premimum' => [
        'type' => 'int(11)',
    ],

];