<?php
class FormTagLib {
    public function tagText($attrs,$body,$view) {
		echo $this->inputElement('text',$attrs);
    }
	public function tagSubmit($attrs,$body,$view) {
		echo $this->inputElement('submit',$attrs);
    }
	public function tagButton($attrs,$body,$view) {
		echo $this->inputElement('button',$attrs);
    }
	public function tagPassword($attrs,$body,$view) {
		echo $this->inputElement('password',$attrs);
    }
	public function tagCheckbox($attrs,$body,$view) {
		echo $this->inputElement('checkbox',$attrs);
    }
	public function tagRadio($attrs,$body,$view) {
		echo $this->inputElement('radio',$attrs);
    }
    public function tagTextArea($attrs,$body,$view) {
		return $this->formElementContainer(
                    sprintf('<textarea name="%s" class="form-textarea">%s</textarea>',
					$type,$attrs->name,htmlentities($attrs->value?$attrs->value:$body)),$attrs);
	}
	private function inputElement($type,$attrs) {
		return $this->formElementContainer(
                    sprintf('<input type="%s" name="%s" value="%s" class="form-%s" />',
					$type,$attrs->name,htmlentities($attrs->value)),$attrs);
	}
    private function formElementContainer($formElement,$attrs) {
        $output = '<div class="form-item">';
        if ($attrs->label) {
            $output .= sprintf('<label>%s</label>',$attrs->label);
        }
        $output .= $formElement;
        $output .= '</div>';
        return $output;
    }
	public function tagForm($body,$attrs,$view) {
		echo sprintf('<form method="%s" action="%s" >',
						$attrs->method ? $attrs->method : 'post',
						$attrs->action),
				$body,
				'</form>';
	}
}