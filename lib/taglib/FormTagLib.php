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
	public function tagForm($attrs,$body,$view) {
		echo sprintf('<form method="%s" action="%s" >',
						$attrs->method ? $attrs->method : 'post',
						$attrs->action),
				$body,
				'</form>';
	}
	public function tagId($attrs) {
		if ($attrs->id) return $attrs->id;
		else if ($attrs->name) {
			return preg_replace('/[^A-Z0-9_]/i','_',$attrs->name);
		}
		return null;
	}
	private function inputElement($type,$attrs) {
		$attrs->id = $this->tagId($attrs);
		return $this->formElementContainer(
                    sprintf('<input type="%s" name="%s" value="%s" class="form-%s" id="%s" />',
					$type,$attrs->name,htmlentities($attrs->value),$type,$attrs->id),$attrs);
	}
    private function formElementContainer($formElement,$attrs) {
        $output = '<div class="form-item">';
        if ($attrs->label) {
            $output .= sprintf('<label for="%s">%s</label>',$attrs->id,$attrs->label);
        }
        $output .= $formElement;
        $output .= '</div>';
        return $output;
    }
}