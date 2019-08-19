<?php
/**
 *
 * Schema definition for 'enterprisepayment_transactions'
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['enterprisepayment_transactions'] = [
    'id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'app_id' => [
        'type' => 'int(11) unsigned',
    ],
    'customer_id' => [
        'type' => 'int(11) unsigned',
    ],
    'type' => [
        'type' => 'int(11) unsigned',
    ],
    'amount' => [
        'type' => 'varchar(255)',
    ],
    'response_data' => [
        'type' => 'text',
    ],
    'status' => [
        'type' => 'int(11) unsigned',
    ],
    'transaction_date' => [
        'type' => 'datetime',
    ],
];