<?php
/**
 * Interface that user class must implement
 * @See ISession
 * @See SessionHandler
 */
interface IUser {
    public function getUserId();
	public function getUsername();
	public function isValid();
}