<?php
/**
 * User tags
 * @namespace u
 */
class UserTagLib extends TagLib {

    /**
     * Outputs the current users full name (if any)
     */
	protected function tagFullname() {
		$user = SessionHandler::user();
		if ($user) {
			return $user->getFullName();
		}
	}
}
Pimple::instance()->registerTagLib('u',new UserTagLib());