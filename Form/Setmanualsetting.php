<?php

/**
 * Class Enterprisepayment_Form_Setmanualsetting
 */
class Enterprisepayment_Form_Setmanualsetting extends Siberian_Form_Abstract
{

    /**
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path("/enterprisepayment/application/savemanualstate"))
            ->setAttrib("id", "form-gateway");


        self::addClass("create", $this);


        $id = $this->addSimpleHidden("id");

        $this->addSimpleText("return_state", __("State name"))->setRequired(true)->addClass("return_state");

        $this->addSimpleText("return_value_id", __("Return value id"))->setRequired(true)->addClass("return_value_id");

        $value_id = $this->addSimpleHidden("value_id");
        $value_id->setRequired(true);


        $this->addNav('form-test-nav', "Save", false);
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