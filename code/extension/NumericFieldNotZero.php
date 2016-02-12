<?php

/**
 * Text input field with validation for numeric values. Supports validating
 * the numeric value as to the {@link i18n::get_locale()} value, or an
 * overridden locale specific to this field.
 *
 * @package forms
 * @subpackage fields-formattedinput
 */
class NumericFieldNotZero extends NumericField
{

    /**
    * @return string
    */
    public function Value() {
        if($this->value) {
            return Convert::raw2xml((string) $this->value);
        }
        return '10';
    }

    /**
     * Validate this field
     *
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator) {

        if(!$this->value) {
            return true;
        }

        if($this->isNumeric() && $this->value > 0 ) {
            return true;
        }

        $validator->validationError(
            $this->name,
            _t(
                'NumericFieldNotZero.VALIDATION',
                "'{value}' is not a valid number, only numbers > 0 can be accepted for this field",
                array('value' => $this->value)
            ),
            "validation"
        );

        return false;
    }

}
// EOF
