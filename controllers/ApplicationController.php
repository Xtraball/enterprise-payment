<?php

/**
 * Class Enterprisepayment_ApplicationController
 */
class Enterprisepayment_ApplicationController extends Application_Controller_Default {

	/*change gateway status in editor section*/
	public function changegatewaystatusAction() {
		$gid = $this->getRequest()->getParam("gid");
		$value_id = $this->getRequest()->getParam("value_id");
		try {
			$status = $this->getRequest()->getParam("is_visible");
			$gatewaysmodel = new Enterprisepayment_Model_Enterprisepayment();
			$gatewaydata = $gatewaysmodel->find(array('id' => $gid));
			$payment_code = $gatewaydata->getCode();
			$gateway_model = new Enterprisepayment_Model_Gatewaydetail();
			$gatewaydata = $gateway_model->find(array('gid' => $gid, 'value_id' => $value_id));

			/*check cash payment*/ 
			if($payment_code != 'cash'){
				if (count($gatewaydata->getdata()) > 0) {
					$setStatus = $gateway_model
					->find(array('gid' => $gid,'value_id' => $value_id))
					->setStatus($status)->save();

					$html = array(
						'success' => '1',
						'success_message' => $this->_('Update successfully'),
						'message_timeout' => 2,
						'message_button' => 0,
						'message_loader' => 0,
					);
				} else {
					throw new Exception($this->_('Please enter all credentials after clicking on edit option'));
				}
			}else{
				$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
				$postArray = array();
				$postArray['value_id'] = $value_id;
				$postArray['gid'] = $gid;
				$postArray['payment_mode'] = '';
				$postArray['live_data'] = '';
				$postArray['test_data'] = '';
				$postArray['status'] = $status;
				$gdata = $detailmodel
					->find(array('gid' => $gid, 'value_id' => $value_id))
					->setData($postArray)->save();
				$setStatus = $gateway_model
				->find(array('gid' => $gid))
				->setStatus($status)->save();
				$html = array(
					'success' => '1',
					'success_message' => $this->_('Update successfully'),
					'message_timeout' => 2,
					'message_button' => 0,
					'message_loader' => 0,
				);
			}
			
		} catch (Exception $e) {
			$html = array(
				'error' => 1,
				'message' => $e->getMessage(),
			);
		}
		$this->getLayout()->setHtml(Zend_Json::encode($html));
	}

	/*Load stripe form*/
	public function loadstripeformAction() {

		$pgid = $this->getRequest()->getParam("gid");
		$pvalue_id = $this->getRequest()->getParam("value_id");
		$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
		$gdata = $detailmodel->find(array('gid' => $pgid, 'value_id' => $pvalue_id));

		$testArray = json_decode($gdata->getTestData());
		$liveArray = json_decode($gdata->getLiveData());

		$data = array();
		if ($gdata->getId()) {
			$data['id'] = $gdata->getId();
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['live_publishkey'] = $liveArray->live_publishkey;
			$data['test_publishkey'] = $testArray->test_publishkey;
			$data['live_secrete_key'] = $liveArray->live_secrete_key;
			$data['test_secrete_key'] = $testArray->test_secrete_key;
			$data['status'] = $gdata->getStatus();
			$data['payment_mode'] = $gdata->getPaymentMode();
		} else {
			$data['id'] = null;
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['live_publishkey'] = null;
			$data['test_publishkey'] = null;
			$data['live_secrete_key'] = null;
			$data['test_secrete_key'] = null;
			$data['status'] = null;
			$data['payment_mode'] = null;
		}

		$form = new Enterprisepayment_Form_Stripeform();
		$form->populate($data);
		$form->removeNav("gateway-save-nav");
		$form->addNav("gateway-save-edit-nav", "Save", false);
		$payload = [
			'success' => true,
			'form' => $form->render(),
			'message' => __('Success.'),
			'data' => $gdata->getData(),
		];

		$this->_sendJson($payload);
	}

	/*Save stripe form into db*/
	public function savestripeformAction() {
		$values = $this->getRequest()->getPost();

		try {
			/*live mode payment*/
			if ($values['payment_mode'] == 1) {

				$errors = array();

				if (empty($values['live_publishkey'])) {
					$errors[] = "live_publishkey";
				}
				if (empty($values['live_secrete_key'])) {
					$errors[] = "live_secrete_key";
				}
				

				if (!empty($errors)) {
					$message = array(__("Please enter all stripe credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			/*Test payment mode*/
			if ($values['payment_mode'] == 0) {

				$errors = array();

				if (empty($values['test_publishkey'])) {
					$errors[] = "Test Publish key";
				}
				if (empty($values['test_secrete_key'])) {
					$errors[] = "Test Secret key";
				}
				if (!empty($errors)) {
					$message = array(__("Please enter all stripe credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			if (!empty($errors)) {
				$message = array(__("Please enter all stripe credentials after clicking on edit option"));
				foreach ($errors as $error) {
					$message[] = $error;
				}
				$message = join(',', $message);

				throw new Exception($message);
			}

			$postArray = [];

			$liveDataArray = array('live_publishkey' => $values['live_publishkey'], 'live_secrete_key' => $values['live_secrete_key']);

			$testDataArray = array('test_publishkey' => $values['test_publishkey'], 'test_secrete_key' => $values['test_secrete_key']);

			$postArray['id'] = $values['id'];
			$postArray['value_id'] = $values['value_id'];
			$postArray['gid'] = $values['gid'];
			$postArray['payment_mode'] = $values['payment_mode'];
			$postArray['live_data'] = json_encode($liveDataArray);
			$postArray['test_data'] = json_encode($testDataArray);
			$postArray['status'] = $values['status'];

			$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
			
			$gdata = $detailmodel
			->find(array('gid' => $values['gid'],'value_id' => $values['value_id']))
			->setData($postArray)
			->save();

			$data = array(
				"success" => 1,
				"message" => __("Success"),
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}

		$this->getLayout()->setHtml(Zend_Json::encode($data));
	}

	/*load paypal gateway form*/
	public function loadgatewayformAction() {

		$values = $this->getRequest()->getPost();
		$pgid = $this->getRequest()->getParam("gid");
		$pvalue_id = $this->getRequest()->getParam("value_id");

		$gid = $values['gid'];
		$value_id = $values['value_id'];

		$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
		$gdata = $detailmodel->find(array('gid' => $pgid, 'value_id' => $pvalue_id));

		$testArray = json_decode($gdata->getTestData());
		$liveArray = json_decode($gdata->getLiveData());

		$data = array();
		if ($gdata->getId()) {
			$data['id'] = $gdata->getId();
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['username'] = $liveArray->username;
			$data['sandboxusername'] = $testArray->sandboxusername;
			$data['signature'] = $liveArray->signature;
			$data['sandboxsignature'] = $testArray->sandboxsignature;
			$data['password'] = $liveArray->password;
			$data['sandboxpassword'] = $testArray->sandboxpassword;
			$data['status'] = $gdata->getStatus();
			$data['payment_mode'] = $gdata->getPaymentMode();
		} else {
			$data['id'] = null;
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['username'] = null;
			$data['sandboxusername'] = null;
			$data['signature'] = null;
			$data['sandboxsignature'] = null;
			$data['password'] = null;
			$data['sandboxpassword'] = null;
			$data['status'] = null;
			$data['payment_mode'] = null;
		}

		$form = new Enterprisepayment_Form_Gatewaydetail();
		$form->populate($data);
		$form->removeNav("gateway-save-nav");
		$form->addNav("gateway-save-edit-nav", "Save", false);
		$payload = [
			'success' => true,
			'form' => $form->render(),
			'message' => __('Success'),
			'data' => $gdata->getData(),
		];

		$this->_sendJson($payload);
	}

	

	/*Save paypal details*/
	public function savepaypaldetailAction() {
		$values = $this->getRequest()->getPost();
		try {

			if ($values['payment_mode'] == 1) {

				$errors = array();

				if (empty($values['username'])) {
					$errors[] = "Username";
				}
				if (empty($values['signature'])) {
					$errors[] = "Signature";
				}
				if (empty($values['password'])) {
					$errors[] = "Password";
				}

				if (!empty($errors)) {
					$message = array(__("Please enter all paypal credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			if ($values['payment_mode'] == 0) {

				$errors = array();

				if (empty($values['sandboxusername'])) {
					$errors[] = "Sandbox Username";
				}
				if (empty($values['sandboxsignature'])) {
					$errors[] = "Sandbox Signature";
				}
				if (empty($values['sandboxpassword'])) {
					$errors[] = "Sandbox Password";
				}

				if (!empty($errors)) {
					$message = array(__("Please enter all paypal credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			if (!empty($errors)) {
				$message = array(__("Please enter all paypal credentials after clicking on edit option"));
				foreach ($errors as $error) {
					$message[] = $error;
				}
				$message = join(',', $message);

				throw new Exception($message);
			}

			$postArray = array();

			$liveDataArray = array('username' => $values['username'], 'signature' => $values['signature'], 'password' => $values['password']);

			$sandboxDataArray = array('sandboxusername' => $values['sandboxusername'], 'sandboxsignature' => $values['sandboxsignature'], 'sandboxpassword' => $values['sandboxpassword']);

			$postArray['id'] = $values['id'];
			$postArray['value_id'] = $values['value_id'];
			$postArray['gid'] = $values['gid'];
			$postArray['payment_mode'] = $values['payment_mode'];
			$postArray['live_data'] = json_encode($liveDataArray);
			$postArray['test_data'] = json_encode($sandboxDataArray);
			$postArray['status'] = $values['status'];

			$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
			$gdata = $detailmodel->find(array('id' => $values['id'],'value_id' => $values['value_id']))
			->setData($postArray)->save();

			$data = array(
				"success" => 1,
				"message" => __("Success"),
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}

		$this->getLayout()->setHtml(Zend_Json::encode($data));
	}

	/*Get all modules name from application*/
	public function getpagesAction(){
		$application = $this->getApplication();
		$option_values = $application->getPages(50, true);
		$pages = array();
		foreach ($option_values as $option_value) {  
			if($option_value->getIsActive()){
				$get_all_active_modules = $option_value->getMobileUri();
				$get_all_new_modules = explode("/",$get_all_active_modules);
				if($get_all_new_modules[0] == "goto") {
					$pages[strtolower($option_value->getTabbarName()).'/mobile_list/index/value_id/'.$option_value->getValueId()] =  $option_value->getTabbarName();
				} else {
					$pages[$option_value->getMobileUri().'index/value_id/'.$option_value->getValueId()] =  $option_value->getTabbarName();
				}
			}    
		}       
		$this->_sendJson($pages);
	}

	/*Setting details*/
	public function settingdetailAction(){
		$values = $this->getRequest()->getPost();
		$value_id = $values['value_id'];
		$total_link_url_fields = $values['total_url_fields'];
		try {
			$get_all_link_val = array();
			for ($i=0; $i <= $total_link_url_fields; $i++) { 
				$id_url_link = $values['id_'.$i];
				if($values['return_state_'.$i]) {
					$return_state = $values['return_state_'.$i];

				} else {
					$return_state = '';
				}
				if($values['return_link_'.$i]) {
					$return_url_link = $values['return_link_'.$i];

				} else {
					$return_url_link = '';
				}
				//$return_url_link = $values['return_link_'.$i];

				if($values['return_value_id_'.$i]) {
					$return_value_id = $values['return_value_id_'.$i];
				} else {
					$return_value_id = $values['return_value_id_'.$i];
					$return_value_id = explode("/",$return_url_link);
					$return_value_id = end($return_value_id);
				}

				if(!empty($return_url_link) || !empty($return_state)) {
					$get_all_array_link_val = array('id' => $id_url_link,
						'value_id' => $value_id,
						'return_value_id' => $return_value_id,
						'return_link' => $return_url_link,
						'return_url' => $return_url_link,
						'return_state' => $return_state,
						'return_value_id' => $return_value_id
					);

					array_push($get_all_link_val, $get_all_array_link_val);
				}
			}

			$setting_model = new Enterprisepayment_Model_Setting();

			$setting_data = $setting_model->findAll(array('value_id' => $value_id));
			$get_all_return_value_id = array();
			$get_all_link = array();
			$get_all_state = array();
			foreach ($setting_data as $key => $value) {
			    $get_all_value_id = $value->getReturnValueId();
			    $get_all_link = $value->getReturnLink();
			    $get_all_state = $value->getReturnState();
			    array_push($get_all_return_value_id, $get_all_value_id);
			    array_push($get_all_link, $get_all_link);
			    array_push($get_all_state, $get_all_state);
			}

			$value_id_in_array = array();

			foreach($get_all_link_val as $key => $link_url_values){
				if(in_array($link_url_values['return_value_id'],$value_id_in_array)) {
					continue;
				} elseif (in_array($link_url_values['return_value_id'],$get_all_return_value_id) && (in_array($link_url_values['return_state'],$get_all_state) || in_array($link_url_values['return_link'],$get_all_link))) {
					continue;
				} else {
					if($link_url_values['return_value_id']) {
						$setting_model = new Enterprisepayment_Model_Setting();
						$setting_model
							->find(array('value_id' => $value_id,'id' => $link_url_values['id']))
							->setValueId($value_id)
							->setReturnLink($link_url_values['return_link'])
							->setReturnUrl($link_url_values['return_url'])
							->setReturnState($link_url_values['return_state'])
							->setReturnValueId($link_url_values['return_value_id'])
							->save();
						array_push($value_id_in_array, $link_url_values['return_value_id']);
					} else {
						$data = array(
							"error" => 1,
							"message" => __('Something went wrong'),
						);
					}
					
				}
			}
			
			$data = array(
				"success" => 1,
				"message" => __("Success"),
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);
	}

	/*Get setting tab data*/
	public function settingdataAction(){
		$values = $this->getRequest()->getPost();
		try {
			$setting_model = new Enterprisepayment_Model_Setting();
			$setting_data = $setting_model->find(array('value_id' => $values['value_id']))->getData();
			$data = array(
				"success" => 1,
				"message" => __("Success"),
				"data" => $setting_data
			);	
		}catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);

	}

	/*Save bank transfer form details*/
	public function savebanktransferformAction(){
		
		try {
			$values = $this->getRequest()->getPost();
			$value_id = $values['value_id'];
			$gid = $values['gid'];

			unset($values['value_id']);
			unset($values['gid']);

			$postArray = array();
			$postArray['value_id'] = $value_id;
			$postArray['gid'] = $gid;
			$postArray['payment_mode'] = '';
			$postArray['live_data'] = json_encode($values);
			$postArray['test_data'] = '';
			$postArray['status'] = '1';
			$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
			if($postArray != null){
				$gdata = $detailmodel->find(array('value_id' => $value_id,'gid' => $gid))
				->setData($postArray)->save();
			}
			$data = array(
				'success' => '1',
				'success_message' => $this->_('Saved successfully'),
				'message_timeout' => 2,
				'message_button' => 0,
				'message_loader' => 0,
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);
	}

	/*Edit label fields and save it to db*/
	public function editlabelfieldAction(){
		try {
			$values = $this->getRequest()->getPost();
			$field_name = $values['field_name'];
			$id = $values['id'];
			$detailmodel = new Enterprisepayment_Model_Banksetting();
			if($field_name != null){
				$gdata = $detailmodel->find(array('id' => $id))
				->setFieldName($field_name)
				->save();
			}

			$data = array(
				'success' => '1',
				'success_message' => $this->_('Saved successfully'),
				'message_timeout' => 2,
				'message_button' => 0,
				'message_loader' => 0,
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);
	}

	/*Add label fields into db*/
	public function addlabelfieldAction(){
		try {
			$values = $this->getRequest()->getPost();
			$field_name = $values['field_name'];
			$detailmodel = new Enterprisepayment_Model_Banksetting();
			if($field_name != null){
				$gdata = $detailmodel
				->setFieldName($field_name)
				->setValueId($values['value_id'])
				->save();
			}
			$getfields = $detailmodel->find(array('field_name' => $field_name));
			$getfieldata = $getfields->getData();	
			$data = array(
				'success' => '1',
				'field_id' => $getfieldata['id'],
				'field_name' => $getfieldata['field_name']
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);
	}

	/*Delete fields from db*/
	public function removefieldAction(){
		try {
			$id = $this->getRequest()->getParam('id');
			if(!empty($this->getRequest()->getParam('value_id'))){
				$detailmodel = new Enterprisepayment_Model_Banksetting();

				$detailmodel->find($id)->delete();

				$html = array(
					'success' => '1',
					'message_timeout' => 2,
					'message_button' => 0,
					'message_loader' => 0,
					'field_id' => $id,
				);
			}
		} catch (Exception $e) {
			$html = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($html);
	}

	public function deletesettingAction(){
		try {
			$id = $this->getRequest()->getParam('id');
			if(!empty($this->getRequest()->getParam('value_id'))){
				$detailmodel = new Enterprisepayment_Model_Setting();

				$detailmodel->find($id)->delete();

				$html = array(
					'success' => '1',
					'message_timeout' => 2,
					'message_button' => 0,
					'message_loader' => 0,
					'field_id' => $id,
				);
			}
		} catch (Exception $e) {
			$html = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($html);
	}

	/*Load bank transfer form*/
	public function loadbanktransferformAction(){
		$values = $this->getRequest()->getPost();
		try {
			$load_data = new Enterprisepayment_Model_Banksetting();
			$get_bank_fields = $load_data->findAll(array('value_id' => $values['value_id']));
			$get_form_fields = [];
			foreach ($get_bank_fields as $key => $value) {
				$get_form_fields[] = $value->getData();
			}
			$load_data = new Enterprisepayment_Model_Gatewaydetail();
			$get_bank_data = $load_data->find(array('value_id' => $values['value_id'],'gid' => $values['gid']));
			$get_status = $get_bank_data->getStatus();
			$i=0;
			if($get_bank_data->getData()){
				$get_form_data = $get_bank_data->getData();
				$get_form = json_decode($get_form_data['live_data']);
				foreach ($get_form as $key=>$value) {
					$get_form = $value;
					$get_form_fields[$i]['field_data'] = $get_form;
					$i++;
				}
			}
			$data = array(
				"success" => 1,
				"message" => __("Success"),
				"get_bank_fields" => $get_form_fields,
				"status" => $get_status
			);
		}catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($data);
	}

	public function loadpremiumgatewayformAction() {
		$values = $this->getRequest()->getPost();
		$pgid = $this->getRequest()->getParam("gid");
		$pvalue_id = $this->getRequest()->getParam("value_id");

		$gid = $values['gid'];
		$value_id = $values['value_id'];

		$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
		$gdata = $detailmodel->find(array('gid' => $pgid, 'value_id' => $pvalue_id));

		$testArray = json_decode($gdata->getTestData());
		$liveArray = json_decode($gdata->getLiveData());

		$data = array();
		if ($gdata->getId()) {
			$data['id'] = $gdata->getId();
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['live_payulatam_app_id'] = $liveArray->live_payulatam_app_id;
			$data['test_payulatam_app_id'] = $testArray->test_payulatam_app_id;
			$data['live_payulatam_public_key'] = $liveArray->live_payulatam_public_key;
			$data['test_payulatam_public_key'] = $testArray->test_payulatam_public_key;
			$data['live_payulatam_private_key'] = $liveArray->live_payulatam_private_key;
			$data['test_payulatam_private_key'] = $testArray->test_payulatam_private_key;
			$data['status'] = $gdata->getStatus();
			$data['payment_mode'] = $gdata->getPaymentMode();
		} else {
			$data['id'] = null;
			$data['value_id'] = $pvalue_id;
			$data['gid'] = $pgid;
			$data['live_payulatam_app_id'] = null;
			$data['test_payulatam_app_id'] = null;
			$data['live_payulatam_public_key'] = null;
			$data['test_payulatam_public_key'] = null;
			$data['live_payulatam_private_key'] = null;
			$data['test_payulatam_private_key'] = null;
			$data['status'] = null;
			$data['payment_mode'] = null;
		}

		$form = new Enterprisepayment_Form_Payulatam();
		$form->populate($data);
		$form->removeNav("gateway-save-nav");
		$form->addNav("gateway-save-edit-nav", "Save", false);
		$payload = [
			'success' => true,
			'form' => $form->render(),
			'message' => __('Success'),
			'data' => $gdata->getData(),
		];

		$this->_sendJson($payload);
	}

	/*Save paypal details*/
	public function savepayulatamAction() {
		$values = $this->getRequest()->getPost();
		try {

			if ($values['payment_mode'] == 1) {

				$errors = array();

				if (empty($values['live_payulatam_app_id'])) {
					$errors[] = "App id";
				}
				if (empty($values['live_payulatam_public_key'])) {
					$errors[] = "Public key";
				}
				if (empty($values['live_payulatam_private_key'])) {
					$errors[] = "Private key";
				}

				if (!empty($errors)) {
					$message = array(__("Please enter all payulatam credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			if ($values['payment_mode'] == 0) {

				$errors = array();

				if (empty($values['test_payulatam_app_id'])) {
					$errors[] = "Test App id";
				}
				if (empty($values['test_payulatam_public_key'])) {
					$errors[] = "Test Public key";
				}
				if (empty($values['test_payulatam_private_key'])) {
					$errors[] = "Test Private key";
				}

				if (!empty($errors)) {
					$message = array(__("Please enter all payulatam credentials after clicking on edit option"));
					foreach ($errors as $error) {
						$message[] = $error;
					}
					$message = join(',', $message);

					throw new Exception($message);
				}
			}

			if (!empty($errors)) {
				$message = array(__("Please enter all payulatam credentials after clicking on edit option"));
				foreach ($errors as $error) {
					$message[] = $error;
				}
				$message = join(',', $message);

				throw new Exception($message);
			}

			$postArray = array();

			$liveDataArray = array('live_payulatam_app_id' => $values['live_payulatam_app_id'], 'live_payulatam_public_key' => $values['live_payulatam_public_key'], 'live_payulatam_private_key' => $values['live_payulatam_private_key']);

			$testDataArray = array('test_payulatam_app_id' => $values['test_payulatam_app_id'], 'test_payulatam_public_key' => $values['test_payulatam_public_key'], 'test_payulatam_private_key' => $values['test_payulatam_private_key']);

			$postArray['id'] = $values['id'];
			$postArray['value_id'] = $values['value_id'];
			$postArray['gid'] = $values['gid'];
			$postArray['payment_mode'] = $values['payment_mode'];
			$postArray['live_data'] = json_encode($liveDataArray);
			$postArray['test_data'] = json_encode($testDataArray);
			$postArray['status'] = $values['status'];

			$detailmodel = new Enterprisepayment_Model_Gatewaydetail();
			$gdata = $detailmodel->find(array('id' => $values['id']))
			->setData($postArray)
			->save();

			$data = array(
				"success" => 1,
				"message" => __("Success"),
			);

		} catch (Exception $e) {
			$data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}

		$this->getLayout()->setHtml(Zend_Json::encode($data));
	}

	public function saveurllinkAction() {
		$values = $this->getRequest()->getPost();
		try{
			$form_save_url = new Enterprisepayment_Form_Setdynamicsetting();
			$return_url = $values['return_url'];
			$value_id = $values['value_id'];
			$return_link = $values['return_link'];
			$model_save_url = new Enterprisepayment_Model_Setting();
			$return_value_id = explode("/",$return_link);
			$return_value_id = end($return_value_id);
			$check_url_available = $model_save_url->find(array('value_id' => $value_id,'return_value_id' => $return_value_id))->getData();
			if(($values['return_link'] != '-1')){

				if($values['id']) {
					$model_save_url
						->find(array('value_id' => $value_id,'id' => $values['id']))
						->setValueId($value_id)
						->setReturnLink($return_link)
						->setReturnUrl($return_url)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}
				} else if ($check_url_available) {
					$model_save_url
						->find(array('value_id' => $value_id,'return_value_id' => $return_value_id))
						->setValueId($value_id)
						->setReturnLink($return_link)
						->setReturnUrl($return_url)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}	
				} else {
					$model_save_url
						->setValueId($value_id)
						->setReturnLink($return_link)
						->setReturnUrl($return_url)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}
				}

			} else {
				$html = array(
					"error" => 1,
					"message" => $this->_('Please enter all credentials'),  
				);
			}
			
		}catch (Exception $e) {
			$html = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($html);
	}

	public function savemanualstateAction() {
		$values = $this->getRequest()->getPost();
		try{
			$form_save_url = new Enterprisepayment_Form_Setdynamicsetting();
			$return_state = $values['return_state'];
			$return_value_id = $values['return_value_id'];
			$value_id = $values['value_id'];
			if(!empty($values['return_state'] && $return_value_id != 0)){
				$return_link = $values['return_link'];
				$model_save_url = new Enterprisepayment_Model_Setting();
				$check_url_available = $model_save_url->find(array('value_id' => $value_id,'return_value_id' => $return_value_id))->getData();

				if($value['id']) {
					$model_save_url
						->find(array('value_id' => $value_id,'id' => $value['id']))
						->setValueId($value_id)
						->setReturnState($return_state)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}
				} else if ($check_url_available) {
					$model_save_url
						->find(array('value_id' => $value_id,'return_value_id' => $return_value_id))
						->setValueId($value_id)
						->setReturnState($return_state)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}	
				} else {
					$model_save_url
						->setValueId($value_id)
						->setReturnState($return_state)
						->setReturnValueId($return_value_id)
						->save();

					if($model_save_url->getId()) {
						$html = array(
							'success' => '1',
							'success_message' => $this->_('Saved successfully'),
							'message_timeout' => 2,
							'message_button' => 0,
							'message_loader' => 0,
						);
					}
				}


			} else {
				$html = array(
					"error" => 1,
					"message" => $this->_('Please enter all credentials'),  
				);
			}
			
		}catch (Exception $e) {
			$payload = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendHtml($html);
	}

	public function loadsettingformAction() {
		try{
			$values = $this->getRequest()->getPost();
			$id = $this->getRequest()->getParam("gid");
			$value_id = $this->getRequest()->getParam("value_id");

			$detailmodel = new Enterprisepayment_Model_Setting();
			$setting_data = $detailmodel->find(array('id' => $id, 'value_id' => $value_id))->getdata();
			$getdynamic_data = array('value_id' => $value_id,'return_link' => $setting_data['return_link'],'return_url' => $setting_data['return_url'],'id' => $setting_data['id']);
			$response_data = [
				'success' => true,
				'return_link' => $setting_data['return_link']
			];
		} catch (Exception $e) {
			$response_data = array(
				"error" => 1,
				"message" => __($e->getMessage()),
			);
		}
		$this->_sendJson($response_data);
		
	}
	
}		