<?php
/**
 * Access handler for handling ACL
 * Notice: Not implemented
 * @TODO: Implement proper access handling
 */
class AccessHandler {

	private static $instance;
	private static $roleNames;
	const R_SYSADMIN = 4;
	const R_ADMIN = 3;
	const R_SUPERUSER = 2;
	const R_USER = 1;
	const R_GUEST = 0;

	public static function instance() {
		if (!self::$instance)
			self::$instance = new self();
		return self::$instance;
	}

	public static function getRoleName($role) {
		if (!self::$roleNames)
			self::$roleNames = array(
				t('Guest'),
				t('User'),
				t('Superuser'),
				t('Admin'),
				t('System admin')
			);
		return self::$roleNames[$role];
	}

	private $tagRoles = array();

	protected function __construct() {

	}

	public function hasAccess($tag) {
		$entry = $this->tagRoles[strtoupper($tag)];
		if (!$entry) return true;
		$user = SessionHandler::instance()->getUser();
		return $entry->isValid($user ? $user->getRole() : self::R_GUEST);
	}

	public function setTagAccess($tag, $role, $exact = false) {
		$this->tagRoles[strtoupper($tag)] = new Access_Entry($role,$exact);
	}

}

class Access_Entry {

	private $role;
	private $exact;

	function __construct($role, $exact) {
		$this->role = $role;
		$this->exact = $exact;
	}

	public function getRole() {
		return $this->role;
	}

	public function setRole($role) {
		$this->role = $role;
	}

	public function getExact() {
		return $this->exact;
	}

	public function setExact($exact) {
		$this->exact = $exact;
	}
	public function isValid($role) {
		if ($this->exact && $role == $this->role)
				return true;
		if ($role >= $this->role)
				return true;
		return false;
	}

}