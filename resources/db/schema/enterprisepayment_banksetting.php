<?php
/**
 *
 * Schema definition for 'enterprisepayment_banksetting'
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['enterprisepayment_banksetting'] = array(
	'id' => array(
		'type' => 'int(11)',
		'auto_increment' => true,
		'primary' => true,
	),
	'value_id' => array(
		'type' => 'int(11) unsigned' ,
	),
	'field_name' => array(
		'type' => 'varchar(255)',
	),
	
	
);