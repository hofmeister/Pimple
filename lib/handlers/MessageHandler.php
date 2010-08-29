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

	private $messages = array();

	public function addMessage($text, $field = null) {
		$this->messages[] = new MessageHandler_Message($text, false, $field);
	}

	public function addError($text, $field = null) {
		$this->messages[] = new MessageHandler_Message($text, true, $field);
	}
    public function getMessages() {
        return $this->messages;
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