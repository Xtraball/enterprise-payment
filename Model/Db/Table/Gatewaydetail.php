<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Gatewaydetail
 */
class Enterprisepayment_Model_Db_Table_Gatewaydetail extends Core_Model_Db_Table {
	protected $_name = "enterprisepayment_gateway_detail";
	protected $_primary = 'id';

	/*get All gateways*/
	public function getAllMethods($value_id) {
		$selectData = array();

		$select = $this->_db->select()
			->from(array('egd' => $this->_name), array('id', 'payment_mode', 'gid', 'value_id','status'))
			->join(array('eg' => 'enterprisepayment_gateways'), 'eg.id = egd.gid', array('name','code'))
			->where('egd.value_id = ?', $value_id)
			->where('egd.status = 1');

		$selectData = $this->_db->fetchAll($select);
		return $selectData;
	}
	

}
