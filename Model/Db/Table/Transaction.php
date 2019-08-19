<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Transaction
 */
class Enterprisepayment_Model_Db_Table_Transaction extends Core_Model_Db_Table {
	protected $_name = "enterprisepayment_transactions";
	protected $_primary = 'id';
	public function getTransactions($value_id) {

		$select = $this->_db->select()->from(array('apt' => $this->_name), array('*'));
		$select->where('apt.value_id = ?', $value_id)->order('transaction_date DESC');
		$result = $this->_db->fetchAll($select);
		return $result;
	}
}
