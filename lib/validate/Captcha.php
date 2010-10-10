<?php
require_once 'Abstract.php';

class CaptchaValidate extends AbstractValidate {
    public function validate($value,$data) {
        return strtolower($value) == strtolower(SessionHandler::get('CAPTCHA'));
    }
    public function getError() {
        return T('Security code is incorrect (%s)',SessionHandler::get('CAPTCHA'));
    }
}