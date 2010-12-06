<?php
class Mail {
    const SMTP_HOST		= 'mail_smtp_host';
    const SMTP_PORT		= 'mail_smtp_port';
    const SMTP_SSL		= 'mail_smtp_ssl';
    const SMTP_USER		= 'mail_smtp_user';
    const SMTP_PASS		= 'mail_smtp_pass';
    const FROM_NAME		= 'mail_from_name';
    const FROM_MAIL		= 'mail_from_mail';
    const REPLY_MAIL	= 'mail_reply_mail';
    const RETURN_MAIL	= 'mail_return_mail';
	
	private static $init = false;
	public static function init() {
		if (!self::$init) {
            require_once Pimple::instance()->getRessource('lib/Zend/Mail.php');
            if (Settings::get(self::SMTP_HOST,false)) {
                require_once Pimple::instance()->getRessource('lib/Zend/Mail/Transport/Smtp.php');
                $config = array(
                    'auth'=>'login',
                    'username'=>Settings::get(self::SMTP_USER,''),
                    'password'=>Settings::get(self::SMTP_PASS,''),
                    'port'=>Settings::get(self::SMTP_PORT,25)
                );

                if (Settings::get(self::SMTP_SSL,false))
                    $config['ssl'] = Settings::get(self::SMTP_SSL);

                $transport = new Zend_Mail_Transport_Smtp(Settings::get(self::SMTP_HOST),$config);
                Zend_Mail::setDefaultTransport($transport);
            }
			self::$init = true;
		}
	}

    public static function preview($view,$data) {
        $containerViewFile = 'mail';
        $mailViewFile = "mail/$view";
        $mailContainer = new View($containerViewFile);
        $mailView = new View($mailViewFile);
        return $mailContainer->render(array('body'=>$mailView->render($data),'subject'=>$subject,'name'=>$name,'email'=>$email));
    }
    public static function send($email,$name,$subject,$view,$data) {
		self::init();
        $html = self::preview($view,$data);

		$mail = new Zend_Mail(Settings::get(Settings::ENCODING));
		$fromName = Settings::get(self::FROM_NAME);
		$fromMail = Settings::get(self::FROM_MAIL);
		$mail->setFrom($fromMail,$fromName);
		$mail->setReplyTo(Settings::get(self::REPLY_MAIL,$fromMail),$fromName);
		$mail->setReturnPath(Settings::get(self::RETURN_MAIL,$fromMail),$fromName);
		$mail->setSubject($subject);
        $mail->setBodyHtml($html);
		$mail->addTo($email, $name);

		$txt= T("This is an HTML message. Please use an HTML capable mail program to read this message.");
		$mail->setBodyText($txt);
        $mail->send();
    }
}