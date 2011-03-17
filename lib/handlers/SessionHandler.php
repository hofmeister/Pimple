<?php

class SessionHandler {
    const COOKIE_DOMAIN = 'session_cookie_domain';
    const COOKIE_URL = 'session_cookie_url';
    const SES_USER_KEY = 'PIMPLE_USER';

	private static $_instance;

    /**
     *
     * @return SessionHandler
     */
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
    public static function user() {
        return self::instance()->getUser();
    }
    public static function isLoggedIn() {
        return self::user() != null;
    }
    public static function get($name) {
        return self::instance()->getSession()->get($name);
    }
    public static function set($name,$value) {
        self::instance()->getSession()->set($name,$value);
    }

	protected $SID;
	protected $sessionKey = 'PSID';
	protected $sessionSecret = "anythingGoes";
	protected $expires = 0;
	protected $remember;
	/**
	 *
	 * @var ISession
	 */
	protected $session;
	protected $sessionData;

	public function __construct() {
        $thus->expires = 3600*7*24;
		$this->remember = (3600 * 24 * 365);
	}

	public function init() {
		if (!$this->session) return;
		//var_dump($_COOKIE);
        $this->SID = $_COOKIE[$this->sessionKey];
		if (!$this->SID) {
			$this->SID = md5($this->sessionSecret . microtime());
		}
        $this->getSession()->loadFromSID($this->SID);
		setcookie($this->sessionKey, $this->SID,$this->getExpires(),Settings::get(self::COOKIE_URL,'/'),Settings::get(self::COOKIE_DOMAIN,null));
	}
    public function save() {
		if ($this->session)
			$this->getSession()->commit();
    }

	/**
	 *
	 * @param IUser $user 
	 */
	public function setUser(IUser $user) {
		if (!$user->isValid())
			throw new ErrorException(t('Unknown username and/or password'), E_ERROR);
		$this->getSession()->set(self::SES_USER_KEY, $user);
	}
	public function clearUser() {
		$this->getSession()->set(self::SES_USER_KEY,null);
	}

	/**
	 *
	 * @return IUser
	 */
	public function getUser() {
        if (!$this->session) return null;
		return $this->getSession()->get(self::SES_USER_KEY);
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
    public function hasSession() {
        return ($this->session != null);
    }

	public function setSession($session) {
		$this->session = $session;
	}


}