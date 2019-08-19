<?php
/**
 *
 * Schema definition for 'enterprisepayment_transactions'
 *
 */
$schemas = (!isset($schemas)) ? array() : $schemas;
$schemas['enterprisepayment_transactions'] = array(
	'id' => array(
		'type' => 'int(11)',
		'auto_increment' => true,
		'primary' => true,
	),
	'value_id' => array(
		'type' => 'int(11) unsigned',
	),
	'app_id' => array(
		'type' => 'int(11) unsigned',
	),
	'customer_id' => array(
		'type' => 'int(11) unsigned',
	),
	'type' => array(
		'type' => 'int(11) unsigned',
	),
	'amount' => array(
		'type' => 'varchar(255)',
	),
	'response_data' => array(
		'type' => 'text',
	),
	'status' => array(
		'type' => 'int(11) unsigned',
	),
	'transaction_date' => array(
		'type' => 'datetime' 
	)
);