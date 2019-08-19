<?php
try {
	$db = $this->getDefaultAdapter(); //throws exception

	/*Default payment name insert into db*/
	$enterprise_pg = $db->describeTable('enterprisepayment_gateways'); 
	$add_bank_fields_default = $db->describeTable('enterprisepayment_banksetting');
	if (!empty($enterprise_pg)) {
		$found = $this->fetchRow('SELECT id FROM enterprisepayment_gateways WHERE code="paypal"');

		if (empty($found)) {
			$sql = 'INSERT INTO enterprisepayment_gateways(name,code,is_premimum) VALUES("Paypal","paypal",0)';
			$this->query($sql);
		}
	}
	if (!empty($enterprise_pg)) {
		$found = $this->fetchRow('SELECT id FROM enterprisepayment_gateways WHERE code="stripe"');

		if (empty($found)) {
			$sql = 'INSERT INTO enterprisepayment_gateways(name,code,is_premimum) VALUES("Stripe","stripe",0)';
			$this->query($sql);
		}
	}
	if (!empty($enterprise_pg)) {
		$found = $this->fetchRow('SELECT id FROM enterprisepayment_gateways WHERE code="bank_transfer"');

		if (empty($found)) {
			$sql = 'INSERT INTO enterprisepayment_gateways(name,code,is_premimum) VALUES("Bank Transfer","bank_transfer",0)';
			$this->query($sql);
		}
	}
	if (!empty($enterprise_pg)) {
		$found = $this->fetchRow('SELECT id FROM enterprisepayment_gateways WHERE code="cash"');

		if (empty($found)) {
			$sql = 'INSERT INTO enterprisepayment_gateways(name,code,is_premimum) VALUES("Cash","cash",0)';
			$this->query($sql);
		}
	}
	if (!empty($enterprise_pg)) {
		$found = $this->fetchRow('SELECT id FROM enterprisepayment_gateways WHERE code="payu_latam"');

		if (empty($found)) {
			$sql = 'INSERT INTO enterprisepayment_gateways(name,code,is_premimum) VALUES("PayU Latam","payu_latam",1)';
			$this->query($sql);
		}
	}

	/*Insert default bank fields*/
	if (!empty($add_bank_fields_default)) {
		$found = $db->query('SELECT * FROM enterprisepayment_banksetting WHERE value_id="0"')->fetchAll();
		
		if(count($found) == 0){
			$field_names= array(
                    "Account Name",
                    "Bank Name",
                    "IBAN",
                    "SWIFT"
                );
			foreach($field_names as $name){
                $sql='INSERT INTO enterprisepayment_banksetting(field_name) VALUES("'.$name.'")';
                $this->query($sql);
            }

            
		}
	}
} catch (Exception $e) {
	print_r($e);
	die;
}