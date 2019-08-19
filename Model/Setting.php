<?php

/**
 * Class Enterprisepayment_Model_Setting
 */
class Enterprisepayment_Model_Setting extends Core_Model_Default
{
    /**
     * Enterprisepayment_Model_Enterprisepayment constructor.
     * @param array $params
     * @throws Zend_Exception
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->_db_table = 'Enterprisepayment_Model_Db_Table_Setting';
        return $this;
    }

}