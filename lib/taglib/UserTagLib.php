<?php
class UserTagLib extends TagLib {

	protected function tagFullname() {
		$user = SessionHandler::user();
		if ($user) {
			return $user->getFullName();
		}
	}
}
Pimple::instance()->registerTagLib('u',new UserTagLib());