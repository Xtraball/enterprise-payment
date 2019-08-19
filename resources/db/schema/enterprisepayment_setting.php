<?php
/**
 *
 * Schema definition for 'enterprisepayment_setting'
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['enterprisepayment_setting'] = [
    'id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'return_value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'return_link' => [
        'type' => 'varchar(255)',
    ],
    'return_url' => [
        'type' => 'varchar(255)',
    ],
    'return_state' => [
        'type' => 'varchar(255)',
    ],

];