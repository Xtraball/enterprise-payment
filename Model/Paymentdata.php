<?php

/**
 * Class Enterprisepayment_Model_Paymentdata
 */
class Enterprisepayment_Model_Paymentdata extends Core_Model_Default
{
    /**
     * Enterprisepayment_Model_Enterprisepayment constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Enterprisepayment_Model_Db_Table_Paymentdata';
        return $this;
    }

    /**
     * @param $value_id
     * @return mixed
     */
    public function getAllMethods($value_id)
    {
        return $this->getTable()->getAllMethods($value_id);
    }

    /**
     * @param $value_id
     * @return mixed
     */
    public function getAllPremimumMethods($value_id)
    {
        return $this->getTable()->getAllPremimumMethods($value_id);
    }
}