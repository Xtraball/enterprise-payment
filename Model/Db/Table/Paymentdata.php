<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Paymentdata
 */
class Enterprisepayment_Model_Db_Table_Paymentdata extends Core_Model_Db_Table
{
    /**
     * @var string
     */
    protected $_name = "enterprisepayment_gateway_detail";
    /**
     * @var string
     */
    protected $_primary = 'id';

    /*Get Status of gateways*/
    /**
     * @param $value_id
     * @return array
     */
    public function getAllMethods($value_id)
    {
        $select1 = $this->_db->select()
            ->from(['eg' => 'enterprisepayment_gateways'], ['id', 'name', 'code', 'is_premimum'])
            ->where('eg.is_premimum = ?', '0');;

        $selectData1 = $this->_db->fetchAll($select1);

        $methodsArray = [];
        $i = 0;

        foreach ($selectData1 as $value) {

            $methodsArray[$i]['id'] = $value['id'];
            $methodsArray[$i]['name'] = $value['name'];
            $methodsArray[$i]['code'] = $value['code'];

            $select2 = $this->_db->select()
                ->from(['egd' => $this->_name], ['*'])
                ->where('egd.value_id = ?', $value_id)
                ->where('egd.gid = ?', $value['id']);

            $selectData2 = $this->_db->fetchRow($select2);

            if ($selectData2['status']) {
                $methodsArray[$i]['status'] = $selectData2['status'];
            } else {
                $methodsArray[$i]['status'] = 0;
            }
            $i++;
        }
        return $methodsArray;

    }

    /**
     * @param $value_id
     * @return array
     */
    public function getAllPremimumMethods($value_id)
    {
        $select1 = $this->_db->select()
            ->from(['eg' => 'enterprisepayment_gateways'], ['id', 'name', 'code', 'is_premimum'])
            ->where('eg.is_premimum = ?', '1');;

        $selectData1 = $this->_db->fetchAll($select1);

        $methodsArray = [];
        $i = 0;

        foreach ($selectData1 as $value) {

            $methodsArray[$i]['id'] = $value['id'];
            $methodsArray[$i]['name'] = $value['name'];
            $methodsArray[$i]['code'] = $value['code'];

            $select2 = $this->_db->select()
                ->from(['egd' => $this->_name], ['*'])
                ->where('egd.value_id = ?', $value_id)
                ->where('egd.gid = ?', $value['id']);

            $selectData2 = $this->_db->fetchRow($select2);

            if ($selectData2['status']) {
                $methodsArray[$i]['status'] = $selectData2['status'];
            } else {
                $methodsArray[$i]['status'] = 0;
            }
            $i++;
        }
        return $methodsArray;

    }

}
