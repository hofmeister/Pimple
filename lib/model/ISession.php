<?php
interface ISession {
	/**
	 * Get and/or create session given session id
	 */
    public function loadFromSID($SID);
    public function hasSID($SID);
	public function setUser(IUser $user);
	public function set($key,$value);
	public function get($key);
    public function commit();
}