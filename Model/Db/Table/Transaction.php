<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Transaction
 */
class Enterprisepayment_Model_Db_Table_Transaction extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "enterprisepayment_transactions";
    /**
     * @var string
     */
    protected $_primary = 'id';

    /**
     * @param $value_id
     * @return array
     */
    public function getTransactions($value_id)
    {

        $select = $this->_db->select()->from(['apt' => $this->_name], ['*']);
        $select->where('apt.value_id = ?', $value_id)->order('transaction_date DESC');
        $result = $this->_db->fetchAll($select);
        return $result;
    }
}
