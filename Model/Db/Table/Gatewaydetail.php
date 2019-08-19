<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Gatewaydetail
 */
class Enterprisepayment_Model_Db_Table_Gatewaydetail extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "enterprisepayment_gateway_detail";
    /**
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @param $value_id
     * @return array
     */
    public function getAllMethods($value_id)
    {
        $selectData = [];

        $select = $this->_db->select()
            ->from(['egd' => $this->_name], ['id', 'payment_mode', 'gid', 'value_id', 'status'])
            ->join(['eg' => 'enterprisepayment_gateways'], 'eg.id = egd.gid', ['name', 'code'])
            ->where('egd.value_id = ?', $value_id)
            ->where('egd.status = 1');

        $selectData = $this->_db->fetchAll($select);

        return $selectData;
    }


}
