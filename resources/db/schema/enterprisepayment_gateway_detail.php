<?php
/**
 *
 * Schema definition for 'enterprisepayment_gateway_detail'
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['enterprisepayment_gateway_detail'] = [
    'id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'gid' => [
        'type' => 'int(11) unsigned',
    ],
    'payment_mode' => [
        'type' => 'int(11) unsigned',
    ],
    'live_data' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'test_data' => [
        'type' => 'text',
        'is_null' => true,
    ],
    'status' => [
        'type' => 'int(11) unsigned',
    ],
];