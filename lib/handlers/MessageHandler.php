<?php

class MessageHandler {

	private static $_instance;

    /**
     *
     * @return MessageHandler
     */
	public static function instance() {
		if (!self::$_instance)
			self::$_instance = new self();
		return self::$_instance;
	}

	private $messages;
    private $flash = array();
    public function  __construct() {
        $this->messages = SessionHandler::get('PIMPLE_FLASH');
        //var_dump($this->messages);
        if (!is_array($this->messages)) {
            $this->messages = array();
        } else {
            foreach($this->messages as $msg) {
                if ($msg->isError() && $msg->getField()) {
                    Validate::addFieldError($msg->getField(),$msg->getText());
                }
            }
        }
    }
    public function flash($text) {
        $this->flash[] = new MessageHandler_Message($text);
    }

	public function addMessage($text, $field = null) {
        $msg = new MessageHandler_Message($text, false, $field);
        $this->messages[] = $msg;
	}

	public function addError($text, $field = null) {
		$msg = new MessageHandler_Message($text, true, $field);

        if ($field)
            Validate::addFieldError($field,$text);

        $this->messages[] = $msg;
	}
    public function getMessages() {
        return $this->messages;
    }
    public function clear() {
        $this->messages = array();
    }
    public function save() {
        if (count($this->messages) > 0) {
            $this->flash = array_merge($this->flash,$this->messages);
            $this->clear();
        }
        SessionHandler::set('PIMPLE_FLASH',$this->flash);
    }

}

class MessageHandler_Message {

	private $text, $error, $field;

	function __construct($text, $error = false, $field = null) {
		$this->text = $text;
		$this->error = $error;
		$this->field = $field;
	}

	public function getText() {
		return $this->text;
	}

	public function isError() {
		return $this->error;
	}
    public function getField() {
        return $this->field;
    }
}