<?php
/**
 *
 * Schema definition for 'enterprisepayment_gateway_detail'
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['enterprisepayment_gateway_detail'] = array(
	'id' => array(
		'type' => 'int(11)',
		'auto_increment' => true,
		'primary' => true,
	),
	'value_id' => array(
		'type' => 'int(11) unsigned',
	),
	'gid' => array(
		'type' => 'int(11) unsigned',
	),
	'payment_mode' => array(
		'type' => 'int(11) unsigned',
	),
	'live_data' => array(
		'type' => 'text',
		'is_null' => true,
	),
	'test_data' => array(
		'type' => 'text',
		'is_null' => true,
	),
	'status' => array(
		'type' => 'int(11) unsigned',
	),
);