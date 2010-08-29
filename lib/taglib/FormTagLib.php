<?php

class FormTagLib extends TagLib {
    public function tagText($attrs,$body,$view) {
		return $this->inputElement('text',$attrs,$view);
    }
	public function tagSubmit($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('submit',$attrs,$view);
    }
	public function tagButton($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('button',$attrs,$view);
    }
	public function tagPassword($attrs,$body,$view) {
		return $this->inputElement('password',$attrs,$view);
    }
	public function tagCheckbox($attrs,$body,$view) {
		return $this->inputElement('checkbox',$attrs,$view);
    }
	public function tagRadio($attrs,$body,$view) {
		return $this->inputElement('radio',$attrs,$view);
    }
    public function tagTextArea($attrs,$body,$view) {
        $body = Request::post($attrs->name,$body);
		return $this->formElementContainer(
                    sprintf('<textarea name="%s" class="form-textarea">%s</textarea>',
					$attrs->name,htmlentities($body)),$attrs);
	}
    public function tagSelect($attrs,$body,$view) {
        
        $keyVal = json_decode(str_replace('\'','"',stripslashes($attrs->options)));
        $options = "";
        foreach($keyVal as $key=>$val) {
            if (is_array($keyVal)) {
                $options .= sprintf('<option>%s</option>',$val);
            } else {
                $options .= sprintf('<option value="%s">%s</option>',$key,$val);
            }
        }
        $selectElm = sprintf('<select name="%s" class="form-select">%s</select>',
					$attrs->name,$options);
		return $this->formElementContainer($selectElm,$attrs);
	}
	public function tagForm($attrs,$body,$view) {
        return sprintf('<form method="%s" action="%s" >',
						$attrs->method ? $attrs->method : 'post',
						$attrs->action).
				$body.
				'</form>';
	}
    protected function tagButtonGroup($attrs,$body,$view) {
		return '<div class="horizontal line buttongroup '.$attrs->class.'">'.chr(10).
                $body.chr(10).
            '</div>';
	}
	public function tagId($attrs) {
		if ($attrs->id) return $attrs->id;
		else if ($attrs->name) {
			return preg_replace('/[^A-Z0-9_]/i','_',$attrs->name);
		}
		return null;
	}
    public function tagCaptcha($attrs) {
        
        $inputElm = sprintf('<img src="%s" alt="Captcha" class="captcha" />
                    <input type="text" name="%s"  class="form-captcha" id="%s" />',
                    Url::makeLink('pimple','captcha')
                    ,$attrs->name,$attrs->id);
        return $this->formElementContainer($inputElm,$attrs);
    }
    public function tagCostum($attrs,$body) {
        return $this->formElementContainer($body,$attrs);
    }

	private function inputElement($type,$attrs,$view) {
        if ($attrs->name)
            $attrs->value = Request::post($attrs->name,$attrs->value);
        $inputElm = sprintf('<input type="%s" name="%s" value="%s" class="form-%s" id="%s" />',
					$type,$attrs->name,htmlentities($attrs->value),$type,$attrs->id);
		$attrs->id = $this->tagId($attrs);
        if ($attrs->container == 'false')
                return $inputElm;
        return $this->formElementContainer($inputElm,$attrs);
	}
    private function formElementContainer($formElement,$attrs) {
        $classes = array();
        $errorMessages = array();
        $errors = array();
        $label = $attrs->label;
        $help = $attrs->help;
        if ($attrs->class) $classes[] = $attrs->class;
        
        $validators = Pimple::instance()->getControllerInstance()->getFieldValidation($attrs->name);
        if (count($validators) > 0) {
            $errors = Validate::getFieldErrors($attrs->name);
            if (!$errors)
                $errors = array();

            if (in_array('required',$validators)) {
                $classes[] = 'v-enabled';

                foreach($validators as $validator) {
                    $errorMessages[] = sprintf('<div style="display:none;" class="error-%s">%s</div>',current(explode('[',$validator)),Validate::getValidator($validator)->getError());
                    $classes[] = "v-$validator";
                }

                $info = T('This field is required');
                if (!$attrs->help)
                     $attrs->help = $info;
                $label .= sprintf('<span class="required" title="%s">*</span>',$info);
            }
        }
        
        if ((count($errors) > 0))
            $classes[] = 'error';
        else if ($attrs->name && Validate::isFieldValid($attrs->name))
            $classes[] = 'valid';

        $output = '<div class="line form-item '.implode(' ',$classes).'">';
        $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$label);
        
        $output .= '<div class="element">'.$formElement.'</div>';
        $output .= sprintf('<div class="instructions">
                                <div class="help">%s</div>
                                <div class="valid">%s</div>
                                <div class="error">%s</div>
                                %s
                            </div>',
                        $attrs->help,
                        T('Field is valid'),
                        current($errors),
                        implode(chr(10),$errorMessages));
        if ($attrs->description) {
            $output .= '<div class="description">'.$attrs->description.'</div>';
        }
        $output .= '</div>';
        return $output;
    }
}