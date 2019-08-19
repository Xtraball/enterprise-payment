<?php
/**
 *
 * Schema definition for 'enterprisepayment_setting'
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['enterprisepayment_setting'] = array(
	'id' => array(
		'type' => 'int(11)',
		'auto_increment' => true,
		'primary' => true,
	),
	'value_id' => array(
		'type' => 'int(11) unsigned',
	),
	'return_value_id' => array(
		'type' => 'int(11) unsigned',
	),
	'return_link' => array(
		'type' => 'varchar(255)',
	),
	'return_url' => array(
		'type' => 'varchar(255)',
	),
	'return_state' => array(
		'type' => 'varchar(255)',
	),
	
);