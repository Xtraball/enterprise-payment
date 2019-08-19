<?php

/**
 * Class Enterprisepayment_Form_Gatewaydetail
 */
class Enterprisepayment_Form_Gatewaydetail extends Siberian_Form_Abstract {

	public function init() {
		parent::init();

		$this
			->setAction(__path("/enterprisepayment/application/savepaypaldetail"))
			->setAttrib("id", "form-gateway")
			->addNav("gateway-save-nav", "Submit")
		;

		self::addClass("create", $this);

		$paypal_payment_mode = $this->addSimpleSelect('payment_mode', __('Payment Mode'),
			['Sandbox', 'Live']
		);
		$paypal_payment_mode->addClass("select_payment_paypal");
		$paypal_payment_mode->setRequired(true);

		$name = $this->addSimpleText("username", __("Username"))->setRequired(true);
		$signature = $this->addSimpleText("signature", __("Signature"))->setRequired(true);
		$password = $this->addSimpleText("password", __("Password"))->setRequired(true);

		$sandboxusername = $this->addSimpleText("sandboxusername", __("Sandbox Username"))->setRequired(true);
		$sandboxsignature = $this->addSimpleText("sandboxsignature", __("Sandbox Signature"))->setRequired(true);
		$sandboxpassword = $this->addSimpleText("sandboxpassword", __("Sandbox Password"))->setRequired(true);

		$statuscheckbox = $this->addSimpleCheckbox('status', __('Enable/Disable?'));

		$id = $this->addSimpleHidden("id");
		$gid = $this->addSimpleHidden("gid");
		$value_id = $this->addSimpleHidden("value_id");

		$value_id->setRequired(true);
	}

	public function setElementValueById($id, $value, $required = false) {
		$element = $this->getElement($id)->setValue($value);
		if ($required) {
			$element->setRequired(true);
		}

	}

}