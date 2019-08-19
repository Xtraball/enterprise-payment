<?php

/**
 * Class Enterprisepayment_Form_Payulatam
 */
class Enterprisepayment_Form_Payulatam extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();
        $this->setAction(__path("/enterprisepayment/application/savepayulatam"));
        $this->setAttrib("id", "payu-latam");
        self::addClass("create", $this);

        $payment_mode = $this->addSimpleSelect('payment_mode', __('PayU Latam Mode'),
            ['Test', 'Live']
        );
        $payment_mode->addClass("payulatam_payment_mode");
        $payment_mode->setRequired(true);

        $live_payulatam_app_id = $this->addSimpleText("live_payulatam_app_id", __("PayU Latam App Id"));

        $live_payulatam_public_key = $this->addSimpleText("live_payulatam_public_key", __("Public API Key"));

        $live_payulatam_private_key = $this->addSimpleText("live_payulatam_private_key", __("Private API Key"));

        $test_payulatam_app_id = $this->addSimpleText("test_payulatam_app_id", __("Test PayU Latam App Id"));

        $test_payulatam_public_key = $this->addSimpleText("test_payulatam_public_key", __("Test Public API Key"));

        $test_payulatam_private_key = $this->addSimpleText("test_payulatam_private_key", __("Test Private API Key"));


        $payulatam_status = $this->addSimpleCheckbox('status', __('Enable/Disable?'));

        $value_id = $this->addSimpleHidden("value_id");
        $id = $this->addSimpleHidden("id");
        $gid = $this->addSimpleHidden("gid");

        $this->addNav("gateway-save-nav", "Submit", false);
    }

    /**
     * @param $id
     * @param $value
     * @param bool $required
     */
    public function setElementValueById($id, $value, $required = false)
    {
        $element = $this->getElement($id)->setValue($value);
        if ($required) {
            $element->setRequired(true);
        }

    }
}