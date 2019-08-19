<?php

/**
 * Class Enterprisepayment_Form_Setdynamicsetting
 */
class Enterprisepayment_Form_Setdynamicsetting extends Siberian_Form_Abstract {

	public function init() {
		parent::init();

		$this
			->setAction(__path("/enterprisepayment/application/saveurllink"))
			->setAttrib("id", "form-gateway");
		

		self::addClass("create", $this);

		$this->addSimpleSelect("return_link", __("Link"),array('-1' => __('No link'),'external_link' => __('External Links')))->addClass("return_link")->setRequired(true);

		$id = $this->addSimpleHidden("id");

		
		$this->addSimpleText("return_url", __("URL"))->addClass("return_url")->setRequired(true);

		

		$value_id = $this->addSimpleHidden("value_id");
		$value_id->setRequired(true);


		$this->addNav('form-test-nav',"Save",false);
	}

	public function setElementValueById($id, $value, $required = false) {
		$element = $this->getElement($id)->setValue($value);
		if ($required) {
			$element->setRequired(true);
		}
	}

	
}