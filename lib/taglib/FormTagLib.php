<?php

class FormTagLib extends TagLib {
    private $formData;
    protected function tagText($attrs,$body,$view) {
		return $this->inputElement('text',$attrs,$view);
    }
    protected function tagTextList($attrs,$body,$view) {
        $out = '<div class="form-text-list">';
        foreach($attrs->value as $elm) {
            $field = $attrs->field;
            $attr = clone $attrs;
            $attr->value = $elm->$field;
			$attr->after = '<a href="javascript:;" class="button remove-normal js-btn-remove" >x</a>';
            $out .= $this->text($attr,null,$view);
        }

        $attr = clone $attrs;
        $attr->checker = false;
        $attr->value = '';
        $attr->class .= ' form-text-list-new';

        if ($attrs->checker) {
            $attr->class .= ' no-checker';
        }
        $attr->after = '<a href="javascript:;" class="button add-normal js-btn-add" >+</a>';
        $out .= $this->text($attr, null,$view);

        $out .= '</div>';
        return $out;
    }
	protected function tagSubmit($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('submit',$attrs,$view);
    }
	protected function tagButton($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('button',$attrs,$view);
    }
	protected function tagPassword($attrs,$body,$view) {
		return $this->inputElement('password',$attrs,$view);
    }
	protected function tagCheckbox($attrs,$body,$view) {
		return $this->inputElement('checkbox',$attrs,$view);
    }
	protected function tagRadio($attrs,$body,$view) {
		return $this->inputElement('radio',$attrs,$view);
    }
    protected function tagTextArea($attrs,$body,$view) {
        $body = Request::post($attrs->name,$body);
		return $this->formElementContainer(
                    sprintf('<textarea name="%s" class="form-textarea %s">%s</textarea>',
					$attrs->name,$attrs->class,htmlentities($body,ENT_QUOTES,'UTF-8')),$attrs);
	}
    protected function tagSelect($attrs,$body,$view) {
        
        $keyVal = json_decode(str_replace('\'','"',stripslashes($attrs->options)));
        $options = "";
        foreach($keyVal as $key=>$val) {
            if (is_array($keyVal)) {
                $options .= sprintf('<option>%s</option>',$val);
            } else {
                $options .= sprintf('<option value="%s">%s</option>',$key,$val);
            }
        }
        $selectElm = sprintf('<select name="%s" class="form-select %s">%s</select>',
					$attrs->name,$attrs->class,$options);
		return $this->formElementContainer($selectElm,$attrs);
	}
	protected function tagForm($attrs,$body,$view) {
        $this->formData = $view->data;
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
	protected function tagId($attrs) {
		if ($attrs->id) return $attrs->id;
		else if ($attrs->name) {
			return preg_replace('/[^A-Z0-9_]/i','_',$attrs->name);
		}
		return null;
	}
    protected function tagCaptcha($attrs) {
        
        $inputElm = sprintf('<img src="%s" alt="Captcha" class="captcha" />
                    <input type="text" name="%s"  class="form-captcha" id="%s" />',
                    Url::makeLink('pimple','captcha')
                    ,$attrs->name,$attrs->id);
        return $this->formElementContainer($inputElm,$attrs);
    }
    protected function tagCostum($attrs,$body) {
        return $this->formElementContainer($body,$attrs);
    }

	private function inputElement($type,$attrs,$view) {

        if ($attrs->name) {
            if (!$attrs->value && $this->formData) {
                $name = $attrs->name;
                if (is_array($this->formData))
                    $attrs->value = $this->formData[$name];
                else
                    $attrs->value = $this->formData->$name;
            }
            $attrs->value = Request::post($attrs->name,$attrs->value);
        }
        $inputElm = '';
        if ($attrs->checker) {
            $inputElm .= '<div class="form-checker js-checker"><input type="checkbox" class="form-checkbox" checked="true" />';
        }
        $inputElm .= $attrs->before;
        $inputElm .= sprintf('<input type="%s" name="%s" value="%s" class="form-%s %s" id="%s" />',
					$type,$attrs->name,htmlentities($attrs->value,ENT_QUOTES,'UTF-8'),$type,$attrs->class,$attrs->id);
        $inputElm .= $attrs->after;
        if ($attrs->checker) {
            $inputElm .= '<div class="clear"></div></div>';
        }
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
                if ($label)
                    $label .= sprintf('<span class="required" title="%s">*</span>',$info);
            }
        }
        $hasInstructions = ($attrs->help || count($validators) > 0 || count($errors) > 0);
        if (!$hasInstructions && !$attrs->instructions) {
            $classes[] = 'no-instructions';
        }
        if ((count($errors) > 0))
            $classes[] = 'error';
        else if ($attrs->name && Validate::isFieldValid($attrs->name))
            $classes[] = 'valid';

        $output = '<div class="line form-item '.implode(' ',$classes).'">';
        $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$label);
        
        $output .= '<div class="element">'.$formElement.'</div>';
        if ($hasInstructions) {
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
        } else if ($attrs->instructions) {
            $output .= sprintf('<div class="instructions">
                                    %s
                                </div>',$attrs->instructions);
        }
        if ($attrs->description) {
            $output .= '<div class="description">'.$attrs->description.'</div>';
        }
        $output .= '</div>';
        return $output;
    }
}