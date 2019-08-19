<?php
/**
 *
 * Schema definition for 'enterprisepayment_banksetting'
 *
 */
$schemas = (!isset($schemas)) ? [] : $schemas;
$schemas['enterprisepayment_banksetting'] = [
    'id' => [
        'type' => 'int(11)',
        'auto_increment' => true,
        'primary' => true,
    ],
    'value_id' => [
        'type' => 'int(11) unsigned',
    ],
    'field_name' => [
        'type' => 'varchar(255)',
    ],


];