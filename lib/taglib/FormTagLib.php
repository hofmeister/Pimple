<?php

class FormTagLib extends TagLib {
    private $formData,$formMethod;
    protected function tagText($attrs,$view) {
		return $this->inputElement('text',$attrs,$view);
    }
    protected function tagDate($attrs,$view) {
        if (!$attrs->class) $attrs->class = '';
        if (!$attrs->format)
            $attrs->format = Settings::get(Date::DATE_FORMAT,'Y-m-d');

        $attrs->class = trim($attrs->class.' js-datepicker');
        if ($attrs->value && !preg_match('/[^0-9]/',$attrs->value)) {
            $attrs->value = date($attrs->format,$attrs->value);
        }
		return $this->inputElement('text',$attrs,$view);
    }
	protected function tagSubmit($attrs,$view) {
        $attrs->container = 'false';
        $attrs->class .= ' button';
		return $this->inputElement('submit',$attrs,$view);
    }
	protected function tagButton($attrs,$view) {
        $attrs->container = 'false';
        $attrs->class .= ' button';
		return $this->inputElement('button',$attrs,$view);
    }
	protected function tagPassword($attrs,$view) {
		return $this->inputElement('password',$attrs,$view);
    }
	protected function tagFile($attrs,$view) {
		return $this->inputElement('file',$attrs,$view);
    }
	protected function tagCheckbox($attrs,$view) {
		return $this->inputElement('checkbox',$attrs,$view);
    }
	protected function tagRadio($attrs,$view) {
		return $this->inputElement('radio',$attrs,$view);
    }
    protected function tagHtmlArea($attrs,$view) {
        return $this->tagTextArea($attrs,  $view);
    }
    protected function tagTextArea($attrs,$view) {
        if ($attrs->name)
            $this->body(Request::post($attrs->name,$this->body()));
        unset($attrs->checker);
        unset($attrs->behaviour);
        unset($attrs->help);
        unset($attrs->label);
        $elm = new HtmlElement('textarea',$attrs,true);
        $elm->addChild(new HtmlText(htmlentities($this->body(),ENT_QUOTES,'UTF-8')));
		return $this->formElementContainer($elm,$attrs);
	}
    protected function tagSelect($attrs,$view) {

        if ($attrs->name) {
            if (!$attrs->value && $this->formData) {
                
                $name = $attrs->name;
                if (is_array($this->formData))
                    $attrs->value = $this->formData[$name];
                else
                    $attrs->value = $this->formData->$name;
            }
            if ($this->formMethod == 'get') {
                $attrs->value = Request::get($attrs->name,$attrs->value);
            } else {
                $attrs->value = Request::post($attrs->name,$attrs->value);
            }
        }
        
        $keyVal = json_decode(str_replace('\'','"',stripslashes($attrs->options)));
        $options = "";
        foreach($keyVal as $key=>$val) {
            $key = (is_array($keyVal)) ? $val : $key;
            if ($key == $attrs->value) {
                $checked = 'selected="true"';
            } else {
                $checked = '';
            }
            if (is_array($keyVal)) {
                $options .= sprintf('<option %s>%s</option>',$checked,$val);
            } else {
                $options .= sprintf('<option %s value="%s">%s</option>',$checked,$key,$val);
            }
        }
        $selectElm = sprintf('<select name="%s" class="form-select %s">%s</select>',
					$attrs->name,$attrs->class,$options);
		return $this->formElementContainer($selectElm,$attrs);
	}
	protected function tagForm($attrs,$view) {
        if ($attrs->binary) {
            $attrs->enctype = 'multipart/form-data';
        } else {
            $attrs->enctype = 'application/x-www-form-urlencoded ';
        }
		if (!$attrs->controller && !$attrs->action && !$attrs->parms) {
            if (!$attrs->controller) {
				$attrs->controller = Pimple::instance()->getController();
				if (!$attrs->action) {
					$attrs->action = Pimple::instance()->getAction();
				}
			}
			$attrs->url = Url::makeLink($attrs->controller,$attrs->action,$_SERVER['QUERY_STRING']);
		} else if ($attrs->action && $attrs->controller) {
                $attrs->url = Url::makeLink($attrs->controller,$attrs->action,$attrs->parms);
		}
        unset($attrs->binary);
        if ($attrs->data)
            $this->formData = $attrs->data;
        else
            $this->formData = $view->data;
        $attrs->method = strtolower($attrs->method ? $attrs->method : 'post');
        $this->formMethod = $attrs->method;
        
        
        return sprintf('<form method="%s" action="%s" enctype="%s">',
						$attrs->method,
						$attrs->url,
                        $attrs->enctype).
				$this->body().
				'</form>';
	}
    protected function tagButtonGroup($attrs,$view) {
		return '<div class="horizontal line buttongroup '.$attrs->class.'">'.chr(10).
                $this->body().chr(10).
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
    protected function tagCostum($attrs) {
        return $this->formElementContainer($this->body(),$attrs);
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
            if ($this->formMethod == 'get') {
                $attrs->value = Request::get($attrs->name,$attrs->value);
            } else {
                $attrs->value = Request::post($attrs->name,$attrs->value);
            }
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
        unset($attrs->container);
        
        if ($checker) {
            $inputElm .= '<div class="form-checker pw-checker"><input type="checkbox" class="form-checkbox" checked="true" />';
        }
        

        $inputElm .= $attrs->before;
        $attrs->id = $this->tagId($attrs);
        if (!$attrs->id) unset($attrs->id);
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
        unset($elmAttr->cClass);
        unset($elmAttr->cclass);
        unset($elmAttr->nolabel);
        unset($elmAttr->noinstructions);

        $elmAttr = ArrayUtil::fromObject($elmAttr);
        if ($attrs->options) {
            $elmAttr['p:options'] = (is_string($attrs->options)) ? $attrs->options : json_encode($attrs->options);
            unset($elmAttr['options']);
        }

        $inputElm .= new HtmlElement('input',$elmAttr,false);

        $inputElm .= $this->body().$attrs->after;
        if ($checker) {
            $inputElm .= '<div class="clear"></div></div>';
        }
		
        if ($container == 'false')
                return $inputElm;
        return $this->formElementContainer($inputElm,$attrs);
	}
    private function formElementContainer($formElement,$attrs) {
        $classes = array();
        $elmClasses = array();
        $errorMessages = array();
        $errors = array();
        $classes[] = 'line';
        if ($attrs->small) {
            $attrs->nolabel = true;
            $attrs->noinstructions = true;
        } else {
            $classes[] = 'composit';
        }
        
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
        if ($attrs->cClass) {
            $classes[] = $attrs->cClass;
            
        }
        unset($attrs->cClass);
        $hasInstructions = ($attrs->help || $hasValidators || count($errors) > 0) && !$attrs->noinstructions;
        unset($attrs->noinstructions);
        if (!$hasInstructions && !$attrs->instructions) {
            $classes[] = 'no-instructions';
        }
        if ($attrs->nolabel) {
            $classes[] = 'no-label';
        }
        if ((count($errors) > 0))
            $classes[] = 'error';
        else if ($attrs->name && Validate::isFieldValid($attrs->name))
            $classes[] = 'valid';

        $output = '<div class="form-item '.implode(' ',$classes).'"';
        if ($attrs->cStyle) {
            $output .= sprintf(' style="%s" ',$attrs->cStyle);
        }
        $output .= '>';
        $label = trim($label);
        if (!$label)
            $label = '&nbsp;';
        if (!$attrs->nolabel || $attrs->small) {
            if ($attrs->id)
                $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$label);
            else
                $output .= sprintf('<label>%s</label>',$label);
        }
        unset($attrs->nolabel);

        $output .= '<div class="element '.implode(' ',$elmClasses).'">'.$formElement.'</div>';
        $renderedInstructions = false;
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
            $renderedInstructions = true;
        } else if ($attrs->instructions) {
            $output .= sprintf('<div class="instructions">
                                    %s
                                </div>',$attrs->instructions);
            $renderedInstructions = true;
        }
        if (!$renderedInstructions && (count($errors) > 0)) {
            $output .= '<div class="description error">'.current($errors).'</div>';
        } else {
            if ($attrs->description) {
                $output .= '<div class="description">'.$attrs->description.'</div>';
            }
        }
        $output .= '</div>';
        return $output;
    }
}