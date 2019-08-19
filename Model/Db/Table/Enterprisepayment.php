<?php

/**
 * Class Enterprisepayment_Model_Db_Table_Enterprisepayment
 */
class Enterprisepayment_Model_Db_Table_Enterprisepayment extends Core_Model_Db_Table {
	protected $_name = "enterprisepayment_gateways";
	protected $_primary = 'id';

    /*Get Module detail*/
	public function getModuleDetailsByCode($field_name, $field_value, $app_id, $all = false) {
        $name = 'application_option';
        $aov = 'application_option_value';
        
        try {
            $select = $this->_db->select()
            ->from(array('ap' => $name))
            ->where('ap.'.$field_name.' = ?', $field_value);
            $test = $this->_db->fetchRow($select);
            

            if ($test) {
            	$select2 = $this->_db->select()
            	->from(array('aov' => $aov ))
            	->where('aov.option_id = ?', $test['option_id'])
                ->where('aov.app_id = ?', $app_id);
            	$test2 = $this->_db->fetchRow($select2);
              	return $test2;
            } else {
              	return  false;
            }
            
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }
}
