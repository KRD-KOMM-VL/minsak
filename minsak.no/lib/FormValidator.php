<?php
/*
Copyright 2013 Kommunal- og regionaldepartementet.

This file is part of minsak.no.

Minsak.no is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 3 as published by the Free Software Foundation.

Minsak.no is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with minsak.no. If not, see http://www.gnu.org/licenses/gpl-3.0.html.


*/
?><?php

/**
 * Simple html form validator
 */
class FormValidator {
    /**
     * List of validators
     * @var Array
     */
    private $validators = Array();
    
    /**
     * Map of file parameters
     * @var Array
     */
    private $isFile = Array();
    
    /**
     * Map of checkbox parameters
     * @var Array
     */
    private $isCheckbox = Array();
    
    /**
     * The html id of the form
     * @var String
     */
    private $formId;
    
    /**
     * Constructor..
     * Create a FormValidator object
     * @param Array $postParams map of parameters to test
     * @param String $formId the html form id
     * @throws Error if arguments are invalid
     */
    public function __construct($postParams, $formId) {
        $this->formId = $formId;
        foreach ($postParams as $param => $validationInfo) {
            $this->validators[$param] = Array();
            if (array_key_exists('isFile', $validationInfo) && $validationInfo['isFile'] === true) {
                $this->isFile[$param] = true;
            }
            if (array_key_exists('isCheckbox', $validationInfo) && $validationInfo['isCheckbox'] === true) {
                $this->isCheckbox[$param] = true;
            }
            foreach ($validationInfo['validators'] as $validatorInfo) {
                switch ($validatorInfo['type']) {
                    case 'string':
                        $this->validators[$param][] = new StringValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'checkbox':
                        $this->validators[$param][] = new CheckboxValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'set':
                        $this->validators[$param][] = new SetValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'email':
                        $this->validators[$param][] = new EmailValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'filetype':
                        $this->validators[$param][] = new FiletypeValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'fileuploaded':
                        $this->validators[$param][] = new FileuploadedValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'fileisimage':
                        $this->validators[$param][] = new FileIsAcceptableImageValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'youtube':
                        $this->validators[$param][] = new YoutubeValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'recaptcha':
                        $this->validators[$param][] = new ReCaptchaValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'date':
                        $this->validators[$param][] = new DateValidator($validatorInfo['params'], $validatorInfo['message']);
                        break;
                    case 'checkpassword':
                    	$this->validators[$param][] = new PasswordCheckValidator($validatorInfo['params'], $validatorInfo['message']);
                    	break;
                    case 'passwordchange':
                    	$this->validators[$param][] = new PasswordChangeValidator($validatorInfo['params'], $validatorInfo['message']);
                    	break;
                    case 'passwordverify':
                    	$this->validators[$param][] = new PasswordVerifyValidator($validatorInfo['params'], $validatorInfo['message']);
                    	break;
                    default:
                        throw new Error('invalid validation type: ' . $validationInfo['type']);
                }
            }
        }
    }
    
    /**
     * Perform the validation
     * @return Array containing list of errors and missing fields
     */
    public function validatePostParameters() {
        $errors = Array();
        $missing = Array();
        foreach ($this->validators as $name => $validatorArray) {
            if (array_key_exists($name, $_POST) || array_key_exists($name, $_FILES) || array_key_exists($name, $this->isCheckbox)) {
                $value = array_key_exists($name, $this->isFile) ? $_FILES[$name]['name'] : (array_key_exists($name, $_POST) ? $_POST[$name] : false);
                foreach ($validatorArray as $v) {
                    if ($error = $v->getError($name, $value)) {
                        if (!array_key_exists($name, $errors)) {
                            $errors[$name] = Array();
                        }
                        $errors[$name][] = $error;
                    }
                }
            } else {
                $missing[$name] = true;
            }
        }
        return Array('errors' => $errors, 'missing' => $missing);
    }
    
    public function getJavascriptValidatorFunctionName() {
    	return 'validateForm'.sha1($this->formId);
    }
    
    public function getJavascriptValidator() {
        ob_start();
?>
function <?php echo $this->getJavascriptValidatorFunctionName();?>() {
    var isErrors = false;
    var errors;
    var value;
<?php
        foreach ($this->validators as $name => $validatorArray) {
?>

    errors = new Array();
    value = $('#<?php echo $name; ?>').val();
<?php
            foreach ($validatorArray as $v) {
                if ($v instanceof CheckboxValidator ) {
                    echo 'if (!$(\'#'.$name.'\')[0].checked) value = \'\';';
                }
                echo $v->getJavascript();
            }
?>
    if (errors.length > 0) {
        isErrors = true;
        var list = $('<ul>').addClass('errors');
        $.each(errors, function(index, value) {
            list.append($('<li>').append(value));
        });
        $('#<?php echo $name; ?>_errors').html(list);
    } else {
        $('#<?php echo $name; ?>_errors').empty();
    }
<?php
        }
?>
    return !isErrors;
}
<?php
        $result = ob_get_clean();
        return $result;
    }
    
    /**
     * Utility function to get value of a post parameter
     * @param String $name the name of the parameter
     * @param any $default the default value to return if the parameter is not present
     * @return string|unknown
     */
    function getPostValue($name, $default='') {
        if (array_key_exists($name, $_POST)) {
            return htmlspecialchars($_POST[$name]);
        } else {
            return $default;
        }
    }
    
    /**
     * Get a html block containing errors for the specified form field
     * @param String $name the form field name
     * @param boolean $isCommit true if it is a commit
     * @param Array $validationErrors the validation errors
     * @param Array $validationMissing the missing form fields
     * @param boolean $allErrors if true, display all validation errors for the given form field, if false, only display first
     * @return string html block
     */
    function getErrorBlock($name, $isCommit, $validationErrors, $validationMissing, $allErrors=true) {
        $block = '<div id="' . $name . '_errors" class="form-error-list-container">';
        if ($isCommit) {
            $messages = Array();
            if (array_key_exists($name, $validationErrors)) {
                $messages = $validationErrors[$name];
            } else if (array_key_exists($name, $validationMissing)) {
                // TODO: different messages for each field?
                $messages = Array('dette feltet må fylles ut');
            }
            if (count($messages) > 0) {
                $block .= '<ul class="form-error-list">';
                foreach ($messages as $message) {
                    $block .= '<li>' . $message . '</li>';
                    if (!$allErrors) {
                        break;
                    }
                }
                $block .= '</ul>';
            }
        }
        $block .= '</div>';
        return $block;
    }

}

/**
 * Base class for field validator
 */
abstract class FieldValidator {
    protected $params;
    protected $message;
    
    /**
     * Construct a field validator
     * @param Array $params parameters for the validator
     * @param String $message message to display if validation fails
     */
    public function __construct($params, $message) {
        $this->params = $params;
        $this->message = $message;
    }
    
    /**
     * get errror message for this validator or false if no error
     * @param $value the value to validate
     * @return error message as string or false
     */
    public abstract function getError($name, $value);
    
    /**
     * Get javascript for client side validation
     * @return String javascript block (without <script>-tags)
     */
    public abstract function getJavascript();
}

/**
 * String validator for validating string min and max lengths
 */
class StringValidator extends FieldValidator {
    
    /**
     * Construct a StringValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Validate against minLength and maxLength of the validation parameters
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
    	
        if (array_key_exists('minLength', $this->params) && strlen($value) < $this->params['minLength']) {
            return $this->message;
        }
        if (array_key_exists('maxLength', $this->params) && strlen($value) > $this->params['maxLength']) {
            return $this->message;
        }
        if (array_key_exists('pattern', $this->params) && preg_match($this->params['pattern'], $value) == 0) {
            return $this->message;
        }
        if (array_key_exists('rejectplaceholder', $this->params) && ($this->params['rejectplaceholder'] === $value)) {
            return $this->message;
        }
        return false;
    }
    
    /**
     * Get javascript code for client side validation of string length
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $terms = Array();
        if (array_key_exists('minLength', $this->params)) {
            $terms[] = 'value.length < ' . $this->params['minLength'];
        }
        if (array_key_exists('maxLength', $this->params)) {
            $terms[] = 'value.length > ' . $this->params['maxLength'];
        }
        if (array_key_exists('pattern', $this->params)) {
            $terms[] = '!value.match(' . $this->params['pattern'] . ')';
        }
        if (array_key_exists('rejectplaceholder', $this->params)) {
            $terms[] = 'value == \''.addslashes($this->params['rejectplaceholder']). '\'';
        }
        $js = Array();
        if (count($terms) > 0) {
            $js[] = '    if (' . join(' || ', $terms) . ') {';
            $js[] = '        errors.push("' . $this->message . '");';
            $js[] = '    }';
        }
        return join("\n", $js) . "\n";
    }
}

/**
* Validator for checking email address
*/
class EmailValidator extends FieldValidator {

    /**
     * Construct an EmailValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }

    /**
     * Test field for valid email address
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $value) == 0) {
            return $this->message;
        } else {
            return false;
        }
    }

    /**
     * Get javascript code for client side validation of email
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $js = Array();
        $js[] = '    if (!value.match(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i)) {';
        $js[] = '        errors.push("' . $this->message . '");';
        $js[] = '    }';
        return join("\n", $js) . "\n";
    }
}

/**
 * Checkbox validator for requiring ticking a checkbox
 */
class CheckboxValidator extends FieldValidator {
    
    /**
     * Construct a CheckboxValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Test field for requiredValue in $params 
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (array_key_exists('requiredValue', $this->params) && $value !== $this->params['requiredValue']) {
            return $this->message;
        }
        return false;
    }
    
    /**
     * Get javascript code for client side validation of checkboxes
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $terms = Array();
        if (array_key_exists('requiredValue', $this->params)) {
            $terms[] = 'value !== \''.$this->params['requiredValue'].'\'';
        }
        $js = Array();
        if (count($terms) > 0) {
            $js[] = '    if (' . join(' || ', $terms) . ') {';
            $js[] = '        errors.push("' . $this->message . '");';
            $js[] = '    }';
        }
        return join("\n", $js) . "\n";
    }
}

/**
 * Validator for checking that parameter is in a set
 */
class SetValidator extends FieldValidator {
    
    /**
     * Construct a SetValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }

    /**
     * Test field for value existing in $params
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (array_search($value, $this->params) === false) {
            return $this->message;
        } else {
            return false;
        }
    }
    
    /**
     * Get javascript code for client side validation of set
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $js = Array();
        $js[] = '    var exists = false;';
        $js[] = '    $.each(["' . join('","', $this->params) . '"], function(k, v) {';
        $js[] = '        if (value == v) {';
        $js[] = '            exists = true;';
        $js[] = '        }';
        $js[] = '    });';
        $js[] = '    if (!exists) {';
        $js[] = '        errors.push("' . $this->message . '");';
        $js[] = '    }';
        return join("\n", $js) . "\n";
    }
    
}

/**
 * Validator for checking uploaded file type
 */
class FiletypeValidator extends FieldValidator {
    
    /**
     * Construct a FiletypeValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Test uploaded file for valid file type
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (($pos = strrpos($value, '.')) === false || array_search(strtolower(substr($value, $pos + 1)), $this->params) === false) {
            return $this->message;
        } else {
            return false;
        }
    }
    
    /**
     * Get javascript code for testing valid file type
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $js = Array();
        $js[] = '    if ((pos = value.lastIndexOf(".")) == -1) {';
        $js[] = '        errors.push("' . $this->message . '");';
        $js[] = '    } else {';
        $js[] = '        var ext = value.substr(pos + 1).toLowerCase()';
        $js[] = '        var exists = false;';
        $js[] = '        $.each(["' . join('","', $this->params) . '"], function(k, v) {';
        $js[] = '            if (ext == v) {';
        $js[] = '                exists = true;';
        $js[] = '            }';
        $js[] = '        });';
        $js[] = '        if (!exists) {';
        $js[] = '            errors.push("' . $this->message . '");';
        $js[] = '        }';
        $js[] = '    }';
        return join("\n", $js) . "\n";
    }
        
}

/**
 * Validator for testing uploaded file
 */
class FileuploadedValidator extends FieldValidator {
    /**
     * Construct a FileuploadedValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }

    /**
     * Test that uploaded file is valid
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (!is_uploaded_file($_FILES[$name]['tmp_name'])) {
            return $this->message;
        } else {
            return false;
        }
    }
    
    /**
     * Not possible to test this client side. Return empty javascript.
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        return '';
    }
        
}

/**
 * Validator for testing image type by trying to get its dimensions
 */
class FileIsAcceptableImageValidator extends FieldValidator {
    
    /**
     * Construct a FileIsAcceptableImageValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Test that uploaded file has real image dimensions
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (!file_exists($_FILES[$name]['tmp_name'])) return $this->message;
        $imginfo = getimagesize($_FILES[$name]['tmp_name']);
        if ($imginfo === FALSE) return $this->message;
        if ($imginfo[2] !== IMAGETYPE_JPEG && $imginfo[2] !== IMAGETYPE_PNG && $imginfo[2] !== IMAGETYPE_GIF) return $this->message;
        if ($imginfo['channels'] == 4) {
            // CMYK not supported
            return $this->message;
        } 
        
        return false;
    }
    
    /**
     * Not possible to test this client side. Return empty javascript.
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        return '';
    }
        
}

/**
 * Validator for testing youtube video url
 */
class YoutubeValidator extends FieldValidator {
    
    /**
     * Construct a YoutubeValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Test uploaded field for valid youtube video url
     * It must match one of the following patterns:
     * - http://www.youtube.com/watch?v=Ovg0eYjM64w
     * - http://youtu.be/Ovg0eYjM64w
     * - Ovg0eYjM64w
     * 
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if ($value != '' &&
            preg_match('/^[0-9a-zA-Z_-]{11}$/', $value) == 0 &&
            preg_match('/^http:\/\/www\.youtube\.com\/watch\b.*(\?|&)v=([0-9a-zA-Z_-]{11})($|&)/', $value) == 0 &&
            preg_match('/^http:\/\/youtu\.be\/([0-9a-zA-Z_-]{11}$/', $value) == 0) {
            return $this->message;
        } else {
            return false;
        }
    }
    
    /**
     * Get javascript for youtube video url validation
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $js = Array();
        $js[] = '    var re1 = /^[0-9a-zA-Z_-]{11}$/;';
        $js[] = '    var re2 = /^http:\/\/www\.youtube\.com\/watch\b.*(\?|&)v=([0-9a-zA-Z_-]{11})($|&)/;';
        $js[] = '    var re3 = /^http:\/\/youtu\.be\/([0-9a-zA-Z_-]{11})$/;';
        $js[] = '    if (value != "" && !value.match(re1) && !value.match(re2) && !value.match(re3)) {';
        $js[] = '        errors.push("' . $this->message . '");';
        $js[] = '    }';
        return join("\n", $js) . "\n";
    }
}

/**
 * Validator for testing recaptcha
 */
class ReCaptchaValidator extends FieldValidator {

    /**
     * Construct a ReCaptchaValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }
    
    /**
     * Test that recapcha has validated correctly
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        $privateKey = $this->params['privateKey'];
        $server = $_SERVER['REMOTE_ADDR'];
        $challengeField = $_POST['recaptcha_challenge_field'];
        $responseField = $_POST['recaptcha_response_field'];
        $resp = recaptcha_check_answer($privateKey, $server, $challengeField, $responseField);
        return $resp->is_valid ? false : $this->message;
    }
    
    /**
     * Not possible (?) to do this in javascript
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        return '';
    }
}

/**
* Date validator for validating a date on the format dd/mm/yy(yy)
*/
class DateValidator extends FieldValidator {

    /**
     * Construct a DateValidator
     * @see FieldValidator
     */
    public function __construct($params, $message) {
        parent::__construct($params, $message);
    }

    /**
     * Validate date
     * @see FieldValidator::getError()
     */
    public function getError($name, $value) {
        if (preg_match('/^\d{2}\/\d{2}\/(\d{2}|\d{4})$/', $value) == 0) {
            return $this->message;
        }
        list($d, $m, $y) = explode('/', $value);
        if (strlen($y) == 2) {
            $y = '20' . $y;
        }
        if (!checkdate($m, $d, $y)) {
            return $this->message;
        }
        return false;
    }

    /**
     * Get javascript code for client side validation of date
     * @see FieldValidator::getJavascript()
     */
    public function getJavascript() {
        $terms = Array();
        if (array_key_exists('pattern', $this->params)) {
            $terms[] = '!value.match(' . '/^\d{2}\/\d{2}\/(\d{2}|\d{4])$/' . ')';
        }
        $js = Array();
        if (count($terms) > 0) {
            $js[] = '    if (' . join(' || ', $terms) . ') {';
            $js[] = '        errors.push("' . $this->message . '");';
            $js[] = '    }';
        }
        return join("\n", $js) . "\n";
    }
}

/**
 * Password change validator for validating old, new and new verified password
 */
class PasswordCheckValidator extends FieldValidator {

	/**
	 * Construct a StringValidator
	 * @see FieldValidator
	 */
	public function __construct($params, $message) {
		parent::__construct($params, $message);
	}

	/**
	 * Validate against minLength and maxLength of the validation parameters
	 * @see FieldValidator::getError()
	 */
	public function getError($name, $value) {
		$passwordKey = $this->params['password'];
		$verifyKey = $this->params['verify'];
		$valuePassword = array_key_exists($passwordKey, $_POST) ? $_POST[$passwordKey] : '';
		$valueVerify = array_key_exists($verifyKey, $_POST) ? $_POST[$verifyKey] : '';
		
		$valueLen = strlen($value);
		$passwordLen = strlen($valuePassword);
		$verifyLen = strlen($valueVerify);
		
		if (strlen($value) + strlen($valuePassword) + strlen($valueVerify) > 0) {
			$valFunc = $this->params['pwdValidationFunc'];
			if (!$valFunc($value)) {
				return $this->message;
			}
		}
		return false;
	}

	/**
	 * Get javascript code for client side validation of string length
	 * @see FieldValidator::getJavascript()
	 */
	public function getJavascript() {
		$js = Array();
		$js[] = "var value_password = $('#" . $this->params['password'] . "').val();";
		$js[] = "var value_verify = $('#" . $this->params['verify'] . "').val();";
		$js[] = "if (value_password.length + value_verify.length + value.length) {";
		$js[] = "  if (value.length < 1) {";
		$js[] = "    errors.push('" . $this->message . "');";
		$js[] = "  }";
		$js[] = "}";
		return join("\n", $js) . "\n";
	}
}

/**
 * Password change validator for validating old, new and new verified password
 */
class PasswordChangeValidator extends FieldValidator {

	/**
	 * Construct a StringValidator
	 * @see FieldValidator
	 */
	public function __construct($params, $message) {
		parent::__construct($params, $message);
	}

	/**
	 * Validate against minLength and maxLength of the validation parameters
	 * @see FieldValidator::getError()
	 */
	public function getError($name, $value) {
		$oldKey = $this->params['old'];
		$verifyKey = $this->params['verify'];
		$valueOld = array_key_exists($oldKey, $_POST) ? $_POST[$oldKey] : '';
		$valueVerify = array_key_exists($verifyKey, $_POST) ? $_POST[$verifyKey] : '';

		$valueLen = strlen($value);
		$oldLen = strlen($valueOld);
		$verifyLen = strlen($valueVerify);

		if (strlen($value) + strlen($valueOld) + strlen($valueVerify) > 0) {
			if (strlen($value) < 6) {
				return $this->message; // new password is not minimum length
			}
		}
		return false;
	}

	/**
	 * Get javascript code for client side validation of string length
	 * @see FieldValidator::getJavascript()
	 */
	public function getJavascript() {
		$js = Array();
		$js[] = "var value_old = $('#" . $this->params['old'] . "').val();";
		$js[] = "var value_verify = $('#" . $this->params['verify'] . "').val();";
		$js[] = "if (value_old.length + value_verify.length + value.length) {";
		$js[] = "  if (value.length < 6) {";
		$js[] = "    errors.push('" . $this->message . "');";
		$js[] = "  }";
		$js[] = "}";
		return join("\n", $js) . "\n";
	}
}

/**
 * Password validate validator for checking that password and passwordvalidate is equal
 */
class PasswordVerifyValidator extends FieldValidator {

	/**
	 * Construct a StringValidator
	 * @see FieldValidator
	 */
	public function __construct($params, $message) {
		parent::__construct($params, $message);
	}

	/**
	 * Validate against minLength and maxLength of the validation parameters
	 * @see FieldValidator::getError()
	 */
	public function getError($name, $value) {
		$oldKey = $this->params['old'];
		$passwordKey = $this->params['password'];
		$valueOld = array_key_exists($oldKey, $_POST) ? $_POST[$oldKey] : '';
		$valuePassword = array_key_exists($passwordKey, $_POST) ? $_POST[$passwordKey] : '';

		$valueLen = strlen($value);
		$oldLen = strlen($valueOld);
		$passwordLen = strlen($valuePassword);

		if (strlen($value) + strlen($valueOld) + strlen($valuePassword) > 0) {
			if ($valuePassword != $value) {
				return $this->message; // new password is not minimum length or not equal to verify
			}
		}
		return false;
	}

	/**
	 * Get javascript code for client side validation of string length
	 * @see FieldValidator::getJavascript()
	 */
	public function getJavascript() {
		$js = Array();
		$js[] = "var value_old = $('#" . $this->params['old'] . "').val();";
		$js[] = "var value_password = $('#" . $this->params['password'] . "').val();";
		$js[] = "if (value_old.length + value_password.length + value.length > 0) {";
		$js[] = "  if (value != value_password) {";
		$js[] = "    errors.push('" . $this->message . "');";
		$js[] = "  }";
		$js[] = "}";
		return join("\n", $js) . "\n";
	}
}
