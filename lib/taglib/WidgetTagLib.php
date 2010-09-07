<?php

class WidgetTagLib extends TagLib {
	const CSS_LIST			= 'pw-list';
	const CSS_BTN			= 'pw-button';
	const CSS_EVT_RMV		= 'pw-evt-remove';
	const CSS_EVT_ADD		= 'pw-evt-add';
	const CSS_EVT_SUBMIT	= 'pw-evt-submit';
	const CSS_EVT_UPLOAD	= 'pw-evt-upload';
	const CSS_EVT_CLOSE		= 'pw-evt-close';
	const CSS_EVT_OPEN		= 'pw-evt-open';
	const CSS_EVT_CANCEL	= 'pw-evt-cancel';
	const CSS_EVT_EDIT		= 'pw-evt-edit';
	const CSS_EVT_CREATE	= 'pw-evt-create';
	const CSS_EVT_MOVE		= 'pw-evt-move';
	const CSS_EVT_DROP		= 'pw-evt-drop';
	const CSS_EVT_DRAG		= 'pw-evt-drag';

	const EVT_REMOVE	= 'remove';
	const EVT_ADD		= 'add';
	const EVT_SUBMIT	= 'submit';
	const EVT_UPLOAD	= 'upload';
	const EVT_CLOSE		= 'close';
	const EVT_OPEN		= 'open';
	const EVT_CANCEL	= 'cancel';
	const EVT_EDIT		= 'edit';
	const EVT_CREATE	= 'create';

	public function __construct() {
		parent::__construct(false);
	}

	protected function tagList($attrs, $body, $view) {
		if (!$attrs->elm)
			$attrs->elm = 'div';
		if (!$attrs->class)
			$attrs->class = '';

		$attrs->class = trim(self::CSS_LIST . ' ' . $attrs->class);
		$elmAttr = ArrayUtil::fromObject($attrs);

		unset($elmAttr['elm']);
		$elm = new HtmlElement($attrs->elm, $elmAttr);
        $elm->addChild(new HtmlText($body));
        return $elm;
	}

	protected function tagButton($attrs,$body, $view) {
		if (!$attrs->elm)
			$attrs->elm = 'a';
		if (!$attrs->class)
			$attrs->class = '';

		$class = $this->evt2css($attrs->event);
		
		$attrs->class = trim(trim(self::CSS_BTN.' '.$class). ' ' . $attrs->class);
		$elmAttr = ArrayUtil::fromObject($attrs);

		unset($elmAttr['elm']);
        unset($elmAttr['event']);
		$elm = new HtmlElement($attrs->elm, $elmAttr);
        $elm->addChild(new HtmlText(trim($body)));
        return $elm;
	}
	private function evt2css($type) {
		switch (strtolower($type)) {
			case self::EVT_REMOVE:
				return self::CSS_EVT_RMV;
			case self::EVT_ADD:
				return self::CSS_EVT_ADD;
			case self::EVT_SUBMIT:
				return self::CSS_EVT_SUBMIT;
			case self::EVT_UPLOAD:
				return self::CSS_EVT_UPLOAD;
			case self::EVT_CLOSE:
				return self::CSS_EVT_CLOSE;
			case self::EVT_OPEN:
				return self::CSS_EVT_OPEN;
			case self::EVT_CANCEL:
				return self::CSS_EVT_CANCEL;
			case self::EVT_EDIT:
				return self::CSS_EVT_EDIT;
			case self::EVT_CREATE:
				return self::CSS_EVT_CREATE;
		}
		return '';
	}
    protected function tagPanel($attrs,$body) {
        return sprintf('<div class="panel %s"><h2>%s</h2>%s</div>',$attrs->class,$attrs->title,$body);
    }
    protected function tagWizard($attrs,$body) {
        return sprintf('<div class="panel wizard %s"><h2>%s<strong>'.T('Step %s of %s',$attrs->step,$attrs->total).'</strong></h2>%s</div>',$attrs->class,$attrs->title,$body);
    }

}