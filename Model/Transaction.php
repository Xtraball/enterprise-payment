<?php

/**
 * Class Enterprisepayment_Model_Transaction
 */
class Enterprisepayment_Model_Transaction extends Core_Model_Default
{
    /**
     * Enterprisepayment_Model_Transaction constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Enterprisepayment_Model_Db_Table_Transaction';
        return $this;
    }


    /**
     * @param $value_id
     * @return mixed
     */
    public function getTransactions($value_id)
    {
        return $this->getTable()->getTransactions($value_id);
    }
}