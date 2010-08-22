<?php
class FormTagLib {
    public function tagText($attrs,$view) {
		echo $this->inputElement('text',$attrs);
    }
	public function tagSubmit($attrs,$view) {
		echo $this->inputElement('submit',$attrs);
    }
	public function tagButton($attrs,$view) {
		echo $this->inputElement('button',$attrs);
    }
	public function tagPassword($attrs,$view) {
		echo $this->inputElement('password',$attrs);
    }
	public function tagCheckbox($attrs,$view) {
		echo $this->inputElement('checkbox',$attrs);
    }
	public function tagRadio($attrs,$view) {
		echo $this->inputElement('radio',$attrs);
    }
	private function inputElement($type,$attrs) {
		return sprintf('<input type="%s" name="%s" value="%s" class="form-%s" />',
					$type,$attrs->name,$attrs->value);
	}
	public function tagForm($body,$attrs,$view) {
		echo sprintf('<form method="%s" action="%s" >',
						$attrs->method ? $attrs->method : 'post',
						$attrs->action),
				$body,
				'</form>';
	}
}