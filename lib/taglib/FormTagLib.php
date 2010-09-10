<?php

class FormTagLib extends TagLib {
    private $formData;
    protected function tagText($attrs,$body,$view) {
		return $this->inputElement('text',$attrs,$body,$view);
    }
	protected function tagSubmit($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('submit',$attrs,$body,$view);
    }
	protected function tagButton($attrs,$body,$view) {
        $attrs->container = 'false';
		return $this->inputElement('button',$attrs,$body,$view);
    }
	protected function tagPassword($attrs,$body,$view) {
		return $this->inputElement('password',$attrs,$body,$view);
    }
	protected function tagCheckbox($attrs,$body,$view) {
		return $this->inputElement('checkbox',$attrs,$body,$view);
    }
	protected function tagRadio($attrs,$body,$view) {
		return $this->inputElement('radio',$attrs,$body,$view);
    }
    protected function tagTextArea($attrs,$body,$view) {
        $body = Request::post($attrs->name,$body);
        unset($attrs->checker);
        unset($attrs->behaviour);
        unset($attrs->help);
        unset($attrs->label);
        $elm = new HtmlElement('textarea',$attrs,true);
        $elm->addChild(new HtmlText(htmlentities($body,ENT_QUOTES,'UTF-8')));
		return $this->formElementContainer($elm,$attrs);
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
		else if ($attrs->name && substr($attrs->name,-2) != '[]') {
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

	private function inputElement($type,$attrs,$body,$view) {

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

        $behaviours = explode(' ',$attrs->behaviour);
        foreach($behaviours as $i=>$behaviour) {
            if ($behaviour)
                $behaviours[$i] = 'pb-'.$behaviour;
        }
        $behaviour = implode(' ',$behaviours);
        $inputElm = '';
        $checker = $attrs->checker;
        $container = $attrs->container;
        if ($checker) {
            $inputElm .= '<div class="form-checker pw-checker"><input type="checkbox" class="form-checkbox" checked="true" />';
        }
        

        $inputElm .= $attrs->before;
        $attrs->id = $this->tagId($attrs);
        $attrs->type = $type;
        $attrs->value = htmlentities($attrs->value,ENT_QUOTES,'UTF-8');
        $attrs->class = trim("form-".$attrs->type.' '.trim($attrs->class.' '.$behaviour));

        $elmAttr = clone $attrs;
        unset($elmAttr->before);
        unset($elmAttr->after);
        unset($elmAttr->checker);
        unset($elmAttr->behaviour);
        unset($elmAttr->help);
        unset($elmAttr->label);

        $inputElm .= new HtmlElement('input',$elmAttr,false);

        $inputElm .= $body.$attrs->after;
        if ($checker) {
            $inputElm .= '<div class="clear"></div></div>';
        }
		
        if ($container == 'false')
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
        $hasValidators = ($attrs->readonly != 'true' && !$attrs->disabled && count($validators) > 0);
        if ($hasValidators) {
            if (String::EndsWith($attrs->name,'[]')) {
                $i = Util::count($attrs->name);
                $fieldName = str_replace('[]',"[$i]",$attrs->name);
            } else {
                $fieldName = $attrs->name;
            }
            $errors = Validate::getFieldErrors($fieldName);

            if (!$errors)
                $errors = array();


                $classes[] = 'v-enabled';

                foreach($validators as $validator) {
                    $errorMessages[] = sprintf('<div style="display:none;" class="error-%s">%s</div>',current(explode('[',$validator)),Validate::getValidator($validator)->getError());
                    $classes[] = "v-$validator";
                }

            if (in_array('required',$validators)) {
                $info = T('This field is required');
                if (!$attrs->help)
                     $attrs->help = $info;
                if ($label)
                    $label .= sprintf('<span class="required" title="%s">*</span>',$info);
            }
        }
        $hasInstructions = ($attrs->help || $hasValidators || count($errors) > 0);
        if (!$hasInstructions && !$attrs->instructions) {
            $classes[] = 'no-instructions';
        }
        if ((count($errors) > 0))
            $classes[] = 'error';
        else if ($attrs->name && Validate::isFieldValid($attrs->name))
            $classes[] = 'valid';

        $output = '<div class="line form-item '.implode(' ',$classes).'"';
        if ($attrs->cStyle) {
            $output .= sprintf(' style="%s" ',$attrs->cStyle);
        }
        $output .= '>';
        $label = trim($label);
        if (!$label)
            $label = '&nbsp;';
        if ($attrs->id)
            $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$label);
        else
            $output .= sprintf('<label>%s</label>',$attrs->id,$label);

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