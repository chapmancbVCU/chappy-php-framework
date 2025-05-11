<?php
namespace Core\Validators;
use Core\Validators\CustomValidator;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
/**
 * Validates input for phone numbers.  Locale abbreviation needs to be set as 
 * a rule.
 */
class PhoneNumberValidator extends CustomValidator {
    /**
     * Implements the abstract function of the same name from the parent 
     * class.  Enforces correct input for phone numbers.
     *
     * @return boolean
     */
    public function runValidation(): bool {
        $countryCode = $this->rule ? strtoupper($this->rule) : 'US';
        $number = preg_replace('/\D/', ' ', $this->_model->{$this->field});

        if($countryCode === 'US' || $countryCode === 'CA') {
            $number = '+1 ' . $number;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $numberToValidate = $phoneUtil->parse($number, $countryCode);
        } catch (NumberParseException $e) {
            var_dump($e);
        }
        $this->message .= " $countryCode";
    
        return ($phoneUtil->isValidNumber($numberToValidate)) ? true : false;

    }
}