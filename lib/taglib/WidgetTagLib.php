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

    protected $tabPanels = array();

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
        $add = '';
        if ($attrs->id) {
            $add .= sprintf(' id="%s"',$attrs->id);
        }
        return sprintf('<div class="panel %s"%s><h2>%s</h2>%s</div>',$attrs->class,$add,$attrs->title,$body);
    }
    protected function tagWizard($attrs,$body) {
        $current = Pimple::instance()->getAction();
        $w = Wizard::get($attrs->id);
        $cs = $w->getStep($current);
        $title = '<ul class="horizontal divided">';
        $before = true;
        foreach($w->getSteps() as $s) {
            if ($s->getId() == $current) {
                $title .= sprintf('<li class="active">%s</li>',$s->getTitle());
                $before = false;
            } else {
                $add = '';
                if ($before) {
                    $add = ' class="done"';
                }
                if ($cs->canJumpTo($s->getId()))
                    $title .= sprintf('<li%s><a href="%s">%s</a></li>',$add,Url::makeLink(Pimple::instance()->getController(),$s->getId()),$s->getTitle());
                else
                    $title .= sprintf('<li%s>%s</li>',$add,$s->getTitle());
            }
            
        }
        $title .= '</ul>';
        
        return sprintf('<div class="panel wizard %s"><h2>%s<strong>'.T('Step %s of %s',$cs->getStep(),$w->getNumSteps()).'</strong></h2>%s</div>',$attrs->class,$title,$body);
    }
    
    protected function tagTabPanel($attrs,$body) {
        $id = $this->uid();
        $title = $attrs->title ? $attrs->title : t('Unnamed tab');
        unset($attrs->title);
        if (count($this->tabPanels) == 0) {
            $attrs->class .= ' active';
        }
        $this->tabPanels[$id] = $title;
        return sprintf('<div class="pw-tabpanel %s" id="%s">%s</div>',$attrs->class,$id,$body);
    }
    protected function tagTabPage($attrs,$body) {
        $tabs = '<ul class="line horizontal pw-tabs">';
        $first = true;
        foreach($this->tabPanels as $id=>$title) {
            $class = ($first) ? 'active' : '';
            $first = false;
            $tabs .= sprintf('<li><a class="%s" href="#%s">%s</a></li>',$class,$id,$title);
        }
        $tabs .= '</ul>';
        $this->tabPanels = array();
        return sprintf('<div class="pw-tabpage %s">%s%s</div>',$attrs->class,$tabs,$body);
    }

}
class Wizard {
    private static $_registry = array();
    /**
     *
     * @param string $id
     * @return Wizard
     */
    public static function get($id) {
        if (!self::$_registry[$id]) {
            self::$_registry[$id] = new self($id);
        }
        return self::$_registry[$id];
    }
    private $id;
    private $steps = array();
    function __construct($id) {
        $this->id = $id;
    }
    /**
     *
     * @param WizardStep $step
     * @return Wizard
     */
    public function addStep($step) {
        $this->steps[$step->getId()] = $step;
        $step->setStep(count($this->steps));
        return $this;
    }
    public function getNumSteps() {
        return count($this->steps);
    }
    public function getStep($id) {
        return $this->steps[$id];
    }
    public function getSteps() {
        return $this->steps;
    }
}
class WizardStep {
    private $id;
    private $title;
    private $step = 0;
    private $jumpTo = array();
    function __construct($id, $title,$jumpTo = array()) {
        $this->id = $id;
        $this->title = $title;
        $this->jumpTo = $jumpTo;
    }
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }
    public function setStep($step) {
        $this->step = $step;
    }
    public function getStep() {
        return $this->step;
    }
    public function canJumpTo($step) {
        return in_array($step,$this->jumpTo);
    }
    public function setJumpTo() {
        $this->jumpTo = func_get_args();
    }
}