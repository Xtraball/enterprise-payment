<?php

/**
 * Class Enterprisepayment_Model_Enterprisepayment
 */
class Enterprisepayment_Model_Enterprisepayment extends Core_Model_Default {
	/**
	 * Enterprisepayment_Model_Enterprisepayment constructor.
	 * @param array $params
	 * @throws Zend_Exception
	 */
	public function __construct($params = []) {
		parent::__construct($params);
		$this->_db_table = 'Enterprisepayment_Model_Db_Table_Enterprisepayment';
		return $this;
	}

	public function getModuleDetailsByCode($code, $module, $app_id) {
		return $this->getTable()->getModuleDetailsByCode($code, $module, $app_id);
	}
}