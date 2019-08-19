<?php
/**
 *
 * Schema definition for 'enterprisepayment_gateways'
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['enterprisepayment_gateways'] = array(
	'id' => array(
		'type' => 'int(11)',
		'auto_increment' => true,
		'primary' => true,
	),
	'name' => array(
		'type' => 'varchar(255)',
	),
	'code' => array(
		'type' => 'varchar(255)',
	),
	'is_premimum' => array(
		'type' => 'int(11)',
	),
	
);