<?php

class MessageHandler {

	private static $_instance;

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

}

class MessageHandler_Message {

	private $text, $isError, $field;

	function __construct($text, $isError = false, $field = null) {
		$this->text = $text;
		$this->isError = $isError;
		$this->field = $field;
	}

	public function getText() {
		return $this->text;
	}

	public function getIsError() {
		return $this->isError;
	}

}