<?php

/**
 * Class Enterprisepayment_Form_Settingform
 */
class Enterprisepayment_Form_Settingform extends Siberian_Form_Abstract {

	public function init() {
		parent::init();

		$this
			->setAction(__path("/enterprisepayment/application/settingdetail"))
			->setAttrib("id", "form-gateway");
		

		self::addClass("create", $this);


		/*$this->addSimpleHtml("control-group_return_link", $this->addLinkOfPayment());


		$this->addSimpleHtml("control_return_url", $this->addUrlOfPayment());

		$this->addSimpleHtml("add-more-link-and-url", $this->addMorePaymentFieldButton());*/

		$this->addSimpleSelect("return_link_0", __("Link"),array('-1' => __('No link'),'external_link' => __('External Links')))->addClass("return_link_0");

		$id = $this->addSimpleHidden("id_0");

		
		$this->addSimpleText("return_url_0", __("URL"))->addClass("return_url_0");

		$this->addSimpleHtml("add-more", $this->addCustomURlField());
		$this->addSimpleHtml("add-more-link-and-url", $this->addMorePaymentFieldButton());

		$this->addSimpleHtml("manage_div", $this->orURLState());
		
		$this->addSimpleText("return_state", __("State Name"))->addClass("return_state");


		$this->addSimpleText("return_value_id", __("Return value id"))->addClass("return_value_id");

		$value_id = $this->addSimpleHidden("value_id");
		$value_id->setRequired(true);

		$total_url_fields = $this->addSimpleHidden("total_url_fields")->addClass('total_url_fields');


		$this->addNav('form-test-nav',"Save",false);
	}

	public function setElementValueById($id, $value, $required = false) {
		$element = $this->getElement($id)->setValue($value);
		if ($required) {
			$element->setRequired(true);
		}
	}

	public function orURLState() {
		return '<div class="form-group sb-form-line">
					<div class="col-sm-3 no-pad">
					</div>
					<div class="col-sm-7 ornote" style="text-align: center;">
						<p>OR</p>
					</div>
				</div>';
	}

	public function addLinkOfPayment() {
		return '<label for="return_link" class="sb-form-line-title col-md-3 optional">Link</label>
			    <div class=" col-md-7">
			      <select name="return_link" id="return_link" is_form_horizontal="1" color="color-blue" label_cols="col-md-3" input_cols="col-md-7" offset_cols="col-md-offset-3" error_cols="col-md-7" class="sb-select styled-select color-blue form-control no-dk return_link">
			         <option value="-1">No link</option>
			         
			      </select>
			    </div>
			    <div class="sb-cb"></div>';
	}

	public function addUrlOfPayment() {
		return '<label for="return_url" class="sb-form-line-title col-sm-3 optional">URL</label>
			   <div class="col-sm-7">
			      <input type="text" name="return_url" id="return_url" value="" is_form_horizontal="1" color="color-blue" class="sb-input-return_url input-flat return_url">
			   </div>
			   <div class="sb-cb"></div>
			   
			   ';
	}

	public function addCustomURlField() {
		return '<div class="more-field-link-add"></div>';
	}

	public function addMorePaymentFieldButton() {
		return '
		<fieldset id="add-more-payment-field-button" class="sb-nav">
				   <dl>
				      <div class="sb-save-info-button">
				         <span class="btn pull-left default_button color-blue new-more-field-insert" color="color-blue" data-role="edit">
				         Add More <i class="fa fa-plus"></i>
				         </span>
				         
				      </div>
				   </dl>
				</fieldset>';
	}

}