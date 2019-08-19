<?php

/**
 * Class Enterprisepayment_Form_Stripeform
 */
class Enterprisepayment_Form_Stripeform extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/enterprisepayment/application/savestripeform"))
            ->setAttrib("id", "form-gateway")
            ->addNav("gateway-save-nav", "Submit");

        self::addClass("create", $this);

        $payment_mode = $this->addSimpleSelect('payment_mode', __('Payment Mode'),
            ['Test', 'Live']
        );
        $payment_mode->addClass("select_stripe_paypal");
        $payment_mode->setRequired(true);

        $test_publishkey = $this->addSimpleText("test_publishkey", __("Test Publish key"))->setRequired(true);

        $test_secrete_key = $this->addSimpleText("test_secrete_key", __("Test Secret key"))->setRequired(true);


        $live_publishkey = $this->addSimpleText("live_publishkey", __("Publish key"))->setRequired(true);
        $live_secrete_key = $this->addSimpleText("live_secrete_key", __("Secret key"))->setRequired(true);

        $statuscheckbox = $this->addSimpleCheckbox('status', __('Enable/Disable?'));

        $id = $this->addSimpleHidden("id");
        $gid = $this->addSimpleHidden("gid");
        $value_id = $this->addSimpleHidden("value_id");
    }
}