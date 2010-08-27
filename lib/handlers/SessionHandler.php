<?php

class SessionHandler {

	public static $_instance;

	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	protected $SID;
	protected $sessionKey = 'PSID';
	protected $sessionSecret = "anythingGoes";
	protected $expires = 3600;
	protected $remember;
	/**
	 *
	 * @var ISession
	 */
	protected $session;
	protected $sessionData;

	public function __construct() {
		$this->remember = (3600 * 24 * 7 * 365);
	}

	public function init() {
		//var_dump($_COOKIE);
		$this->SID = $_COOKIE[$this->sessionKey];
		if (!$this->SID) {
			$this->SID = md5($this->sessionSecret . microtime());
		}
		$this->getSession()->loadFromSID($this->SID);
		setcookie($this->sessionKey, $this->SID,$this->getExpires());
	}
    public function save() {
        $this->getSession()->commit();
    }

	/**
	 *
	 * @param IUser $user 
	 */
	public function setUser(IUser $user) {
		if (!$user->isValid())
			throw new ErrorException(t('Unknown username and/or password'), E_ERROR);
		$this->getSession()->set('__PUSER', $user);
	}

	/**
	 *
	 * @return IUser
	 */
	public function getUser() {
		return $this->getSession()->get('__PUSER');
	}

	public function getSID() {
		return $this->SID;
	}

	public function setSID($SID) {
		$this->SID = $SID;
	}

	public function getSessionKey() {
		return $this->sessionKey;
	}

	public function setSessionKey($sessionKey) {
		$this->sessionKey = $sessionKey;
	}

	public function getSessionSecret() {
		return $this->sessionSecret;
	}

	public function setSessionSecret($sessionSecret) {
		$this->sessionSecret = $sessionSecret;
	}

	public function getExpires() {
		return time()+$this->expires;
	}

	public function setExpires($expires) {
		$this->expires = $expires;
	}

	public function getRemember() {
		return $this->remember;
	}

	public function setRemember($remember) {
		$this->remember = $remember;
	}

	public function getSession() {
		if (!$this->session)
			throw new Exception('Session handler is missing a ISession model');
		return $this->session;
	}

	public function setSession($session) {
		$this->session = $session;
	}

}