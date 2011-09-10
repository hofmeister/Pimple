<?php
/**
 * Form tags
 * @namespace f
 */
class FormTagLib extends TagLib {
    private $formData,$formMethod;
    
    /**
     * Render a text input field
     * @uses FormTagLib::inputElement
     */
    protected function tagText($attrs,$view) {
		return $this->inputElement('text',$attrs,$view);
    }
    /**
     * Render a date input field
     * @param string class CSS class
     * @param string format Date format (for the date() method). Defaults to Pimple default
     * @param integer|string value unix timestamp or date string
     * @uses FormTagLib::inputElement
     */
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
    
    /**
     * Render a submit button 
     * @uses FormTagLib::inputElement
     */
	protected function tagSubmit($attrs,$view) {
        $attrs->container = 'false';
        $attrs->class .= ' button';
		return $this->inputElement('submit',$attrs,$view);
    }
    /**
     * Render a button 
     * @uses FormTagLib::inputElement
     */
	protected function tagButton($attrs,$view) {
        $attrs->container = 'false';
        $attrs->class .= ' button';
		return $this->inputElement('button',$attrs,$view);
    }
    /**
     * Render a password input field
     * @uses FormTagLib::inputElement
     */
	protected function tagPassword($attrs,$view) {
		return $this->inputElement('password',$attrs,$view);
    }
    /**
     * Render a password input field
     * @uses FormTagLib::inputElement
     */
	protected function tagFile($attrs,$view) {
		return $this->inputElement('file',$attrs,$view);
    }
    /**
     * Render a checkbox
     * @param string currentValue | if currentValue and value match - checbox is checked
     * @param string|boolean checked | Values checked,yes and true will cause the checkbox to be checked
     * @param boolean simple | Forced to true
     * @uses FormTagLib::inputElement
     */
	protected function tagCheckbox($attrs,$view) {
        $label = trim($attrs->label);
        if (!$label)
            $label = '&nbsp;';

        $attrs->simple = 'true';
        $attrs->id = $this->tagId($attrs);
        if ($attrs->id)
            $attrs->after = sprintf('<label for="%s">%s</label>',$attrs->id,$label).$attrs->after;
        else
            $attrs->after = sprintf('<label>%s</label>',$label).$attrs->after;

        unset($attrs->label);
        if (!isset($attrs->checked) && $attrs->currentValue == $attrs->value) {
            $attrs->checked = 'checked';
        } else if (isset($attrs->checked)) {
			$attrs->checked = (in_array(strtolower($attrs->checked),array('true','yes','checked'))) ? 'checked' : null;
		}
		if (!$attrs->checked)
			unset($attrs->checked);
		return $this->inputElement('checkbox',$attrs,$view);
    }
    /**
     * Render a radio button
     * @param string currentValue | if currentValue and value match - radio button is checked
     * @param string|boolean checked | Values checked,yes and true will cause the checkbox to be checked
     * @param boolean simple | Forced to true
     * @uses FormTagLib::inputElement
     */
	protected function tagRadio($attrs,$view) {
        
        $label = trim($attrs->label);
        if (!$label)
            $label = '&nbsp;';
        
        $attrs->simple = 'true';
        $attrs->id = $this->tagId($attrs);
        if ($attrs->id)
            $attrs->after = sprintf('<label for="%s">%s</label>',$attrs->id,$label).$attrs->after;
        else
            $attrs->after = sprintf('<label>%s</label>',$label).$attrs->after;

        unset($attrs->label);
        if (isset($attrs->checked)) {
			$attrs->checked = (in_array(strtolower($attrs->checked),array('true','yes','checked'))) ? 'checked' : null;
		} elseif ($attrs->currentValue == $attrs->value) {
            $attrs->checked = 'checked';
        } 
        if (!$attrs->checked)
			unset($attrs->checked);
        
		return $this->inputElement('radio',$attrs,$view);
    }
    /**
     * Render a html area (not implemented - just renders a text area at the moment)
     * @todo implement text area
     * @uses FormTagLib::tagTextArea
     */
    protected function tagHtmlArea($attrs,$view) {
        return $this->tagTextArea($attrs,  $view);
    }
    /**
     * Render a textarea
     * @uses FormTagLib::formElementContainer
     * @container
     */
    protected function tagTextArea($attrs,$view) {
        $value = $this->body();
        
        $elmAttr = $this->getElementAttr($attrs);
        $value = $this->getFieldValue($attrs,$value);
        
        $elm = new HtmlElement('textarea',$elmAttr);
        $elm->addChild(new HtmlText(htmlentities($value,ENT_QUOTES,'UTF-8')));
		return $this->formElementContainer($elm,$attrs);
	}
    /**
     * Render a token input (Facebook-like label input field)
     * @uses FormTagLib::tagTextarea
     */
    protected function tagTokenInput($attrs,$view) {
        $attrs->behaviour = 'tokeninput';
        if ($attrs->options)
            $attrs->options = new stdClass();
        $attrs->options->url = $attrs->url;
        unset($attrs->url);
        $this->setFieldValue($attrs,'');//@todo implement setting token field value
        return $this->tagTextarea($attrs,$view);
    }
    /**
     * Render select box
     * @param object|array|json | options a map or array of options
     * @param string propKey | name of map property to be used as key (defaults to "key")
     * @param string propValue | name of map property to be used as value (defaults to "value")
     * @uses FormTagLib::formElementContainer
     */
    protected function tagSelect($attrs,$view) {

        $attrs->value = $this->getFieldValue($attrs,$attrs->value);

		$keyVal = $this->toObject($attrs->options);
        
        $options = "";
        if ($attrs->emptyText) {
            if (isset($attrs->emptyValue)) {
                $options .= sprintf('<option value="%s">%s</option>',$attrs->emptyValue,$attrs->emptyText);
            } else {
                $options .= sprintf('<option>%s</option>',$attrs->emptyText);
            }
        }
        $isMap = (is_object($keyVal) || ArrayUtil::isMap($keyVal));
        
        if (is_array($keyVal) || is_object($keyVal)) {
            foreach($keyVal as $key=>$val) {
                if (is_object($val)) {
                    $propKey = $attrs->propKey ? $attrs->propKey : 'key';
                    $propVal = $attrs->propValue ? $attrs->propValue : 'value';
                    $key = $val->$propKey;
                    $val = $val->$propVal;
                } else {
                    $key = ($isMap) ? $key : $val;
                }
                if ($key == $attrs->value) {
                    $checked = 'selected="true"';
                } else {
                    $checked = '';
                }
                $options .= ($isMap || $key != $val) ? sprintf('<option %s value="%s">%s</option>',$checked,$key,$val) : sprintf('<option %s>%s</option>',$checked,$val);
            }
        }
        $addon = '';
        if ($attrs->id) {
            $addon .= sprintf(' id="%s"',$attrs->id);
        }
        $selectElm = sprintf('<select name="%s" class="form-select %s" %s>%s</select>',
					$attrs->name,$attrs->class,$addon,$options);
		return $this->formElementContainer($selectElm,$attrs);
	}
    /**
     * Render form tags
     * @param boolean binary | if set - renders a multipart form - else a url encoded form.
     * @param string controller | if set - sets the controller of the target url for this form 
     * @param string action | if set - sets the action of the target url for this form
     * @param string parms | if set - sets the parameters of the target url for this form
     * @param array|object | A map of data for this form - will be propegated to fields within this form.
     * @param string method | sets the HTTP method to used (post or get) - defaults to post
     * @param string url | sets the url of the form - only used if controller is not set
     * @param string id | sets the id on the form element
     * @param string class | sets the css class for the form element
     */
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
        $elm = new HtmlElement('form');
        $elm->setAttribute('method', $attrs->method);
        $elm->setAttribute('enctype', $attrs->enctype);
        $elm->setAttribute('action', $attrs->url);

        if ($attrs->id)
            $elm->setAttribute('id', $attrs->id);

        if ($attrs->class)
            $elm->setAttribute('class', $attrs->class);

        $elm->addChild(new HtmlText($this->body()));
        return $elm->toHtml();
	}
    /**
     * Renders a button group - used to place buttons correctly when building forms
     * @param string class | Addition css class for the button group container
     * @container
     */
    protected function tagButtonGroup($attrs,$view) {
		return '<div class="horizontal line buttongroup '.$attrs->class.'">'.chr(10).
                $this->body().chr(10).
            '</div>';
	}
    /**
     * Returns id based on field name
     * @param string name | Generate id from this name (required unless id is specified)
     * @param string id | overrides and returns simply this id
     */
	protected function tagId($attrs) {
		if ($attrs->id) return $attrs->id;
		else if ($attrs->name && substr($attrs->name,-2) != '[]') {
			return preg_replace('/[^A-Z0-9_]/i','_',$attrs->name);
		}
		return null;
	}
    /**
     * Render a captcha input field
     * @param string name | name of field
     * @param string id | id of field
     * @uses FormTagLib::formElementContainer
     */
    protected function tagCaptcha($attrs) {

        $inputElm = sprintf('<input type="text" name="%s"  class="form-captcha" id="%s" />'
                    ,$attrs->name,$attrs->id);
        $img = sprintf('<img src="%s" alt="Captcha" class="captcha" />',Url::makeLink('pimple','captcha'));
        return $img.$this->formElementContainer($inputElm,$attrs);
    }
    /**
     * Renders a custom form element container - used to create custom input fields
     * 
     * @container
     * @uses FormTagLib::formElementContainer
     */
    protected function tagCostum($attrs) {
        return $this->formElementContainer($this->body(),$attrs);
    }
    /**
     * Renders a hidden field
     * @param boolean container | forced to false
     * @param boolean composit | forced to false
     * @uses FormTagLib::formElementContainer
     */
    protected function tagHidden($attrs,$view) {
        $attrs->container = 'false';
        $attrs->composit = 'false';
        return $this->inputElement('hidden',$attrs, $view);
    }

    /**
     * Base method for almost all input elements
     * 
     * @param mixed value | Value of the field
     * @param string type  | input type
     * @param string class | css class
     * @param string id | input element id
     * @param boolean container | render element container
     * @param boolean checker | render checkout to enable/disable field
     * @param string before | output this before input element (within element container)
     * @param string after | output this after input element (within element container)
     * @uses FormTagLib::formElementContainer
     */
    private function inputElement($type,$attrs,$view) {

        $attrs->value = $this->getFieldValue($attrs,$attrs->value);
        
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
        $attrs->class = trim("form-".$attrs->type.' '.$attrs->class);

        $elmAttr = $this->getElementAttr($attrs);

        $inputElm .= new HtmlElement('input',$elmAttr,false);

        $inputElm .= $this->body().$attrs->after;
        if ($checker) {
            $inputElm .= '<div class="clear"></div></div>';
        }
		
        if ($container == 'false')
                return $inputElm;
        return $this->formElementContainer($inputElm,$attrs);
	}
    
    /**
     * Render form element container
     * 
     * @param string id | Id of input element
     * @param string label | the label of the field
     * @param string help  | help text for this field
     * @param string instructions | intructions on how to fill out field
     * @param string description | Description of the field / value
     * @param string name | name of the input element
     * @param string cClass | CSS class to apply to form element container
     * @param string cStyle | CSS styles to apply to form element container
     * @param string eClass | CSS class to apply to div element 
     * @param string eStyle | CSS styles to apply to div element
     * @param boolean readonly | make field readonly (defaults to false)
     * @param boolean disabled | make field disabled (defaults to false)
     * @param boolean simple | Makes the field behave more as a std html input field (defaults to false)
     * @param boolean composit | Render the field with label, input and instructions (defaults to true)
     * @param boolean small | render field without label and instructions (defaults to false)
     * @param boolean nolabel | render field without label (defaults to false)
     * @param boolean noinstructions | render field without instructions (defaults to false)
     
     */
    private function formElementContainer($formElement,$attrs) {
        $classes = array();
        $elmClasses = explode(' ',$attrs->eClass);
        $errorMessages = array();
        $errors = array();
        $classes[] = 'line';
        if ($attrs->simple) {
            $attrs->composit = 'false';
            $classes[] = 'simple';
        }

        if ($attrs->small || $attrs->composit == 'false') { //Remove small attr...
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
        if (!$label && (!$attrs->simple && $attrs->composit != 'false'))
            $label = '&nbsp;';

        if ($label) {
            if (!$attrs->nolabel || $attrs->small || ($attrs->composit == 'false')) {
                if ($attrs->id)
                    $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$label);
                else
                    $output .= sprintf('<label>%s</label>',$label);
            }
        }
        unset($attrs->nolabel);
        unset($attrs->simple);
        unset($attrs->composit);
        unset($attrs->small);
        unset($attrs->currentValue);

        $output .= '<div class="element '.implode(' ',$elmClasses).'" style="'.$attrs->eStyle.'">'.$formElement.'</div>';
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

    private function handleBehaviour(&$attrs) {
        $behaviours = explode(' ',$attrs->behaviour);
        foreach($behaviours as $i=>$behaviour) {
            if ($behaviour)
                $behaviours[$i] = 'pb-'.$behaviour;
        }
        $attrs->class = trim($attrs->class.' '.implode(' ',$behaviours));
        unset($attrs->behaviour);
    }
    private function handleOptions(&$attrs,&$elmAttr) {
        if ($attrs->options) {
            $elmAttr['p:options'] = (is_string($attrs->options)) ? $attrs->options : str_replace('"','\'',json_encode($attrs->options));
            unset($elmAttr['options']);
            unset($attrs->options);
        }
    }
    private function clearNonHtmlAttributes(&$attrs) {
        $nonos = array(
            'checker','composit','help','label','options','behaviour',
            'before','after','checker','behaviour','help','label','cClass','cclass',
            'nolabel','noinstructions','simple','composit','small','currentValue'
        );
        foreach($nonos as $nono) {
            if (is_array($attrs)) {
                unset($attrs[$nono]);
            } else {
                unset($attrs->$nono);
            }
        }
    }
    protected function getElementAttr($attrs) {
        $this->handleBehaviour($attrs);
        $elmAttr = ArrayUtil::fromObject($attrs);
        $this->handleOptions($attrs,$elmAttr);
        $this->clearNonHtmlAttributes($elmAttr);
        return $elmAttr;
    }
    private function getFieldValue($attrs,$value) {
        if ($attrs->name) {
            if (!$value && $this->formData) {
                $name = $attrs->name;
                if (is_array($this->formData))
                    $value = $this->formData[$name];
                else
                    $value = $this->formData->$name;
            }
            if (strtolower($this->formMethod) == 'get')
                $value = Request::get($attrs->name,$value);
            else
                $value = Request::post($attrs->name,$value);
        }
        return $value;
    }
    private function setFieldValue($attrs,$value) {
        if ($attrs->name) {
            $name = $attrs->name;
            if (!$value && $this->formData) {
                if (is_array($this->formData))
                    $this->formData[$name] = $value;
                else
                    $this->formData->$name = $value;
            }
            if (strtolower($this->formMethod) == 'get')
                Request::get()->$name = $value;
            else
                Request::post()->$name = $value;
        }
    }
}