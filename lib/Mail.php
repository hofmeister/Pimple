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
    const TEST_MAIL	= 'mail_test_mail';
	
	private static $init = false;
	public static function init() {
		if (!self::$init) {
			Zend::_use('Zend_Mail');
            if (Settings::get(self::SMTP_HOST,false)) {
				Zend::_use('Zend_Mail_Transport_Smtp');
                
                $config = array(
                    'port'=>Settings::get(self::SMTP_PORT,25)
                );
                if(Settings::get(self::SMTP_USER,false)) {
                	$config['auth'] = 'login';
                    $config['username'] = Settings::get(self::SMTP_USER,'');
                  	$config['password'] = Settings::get(self::SMTP_PASS,'');
                }
                if (Settings::get(self::SMTP_SSL,false)) {
                    $config['ssl'] = Settings::get(self::SMTP_SSL);
                }
                $transport = new Zend_Mail_Transport_Smtp(Settings::get(self::SMTP_HOST),$config);
                Zend_Mail::setDefaultTransport($transport);
            }
			self::$init = true;
		}
	}

    public static function preview($view,$data,$containerViewFile = 'mail',$textonly = false) {
        $mailViewFile = "mail/$view";
        if (!$containerViewFile)
            $containerViewFile = 'mail';
        $mailContainer = new View($containerViewFile);
        $mailView = new View($mailViewFile);
        if (!is_array($data)) {
            $data = ArrayUtil::fromObject($data);
        }
        $data['textonly'] = $textonly;
        $container = $data;
        $container['body'] = $mailView->render($data);
        
        return $mailContainer->render($container);
    }
    public static function send($email,$name,$subject,$view,$data,$containerViewFile = 'mail') {
		self::init();
        if (is_object($data)) {
            $data->email = $email;
            $data->subject = $subject;
        } else if (is_array($data)) {
            $data['email'] = $email;
            $data['subject'] = $subject;
        } else {
            $data = array();
            $data['email'] = $email;
            $data['subject'] = $subject;
        }
        if (Settings::get(Settings::DEBUG,false)
                && Settings::get(self::TEST_MAIL,'') != '') {
            $email = Settings::get(self::TEST_MAIL,'');
        }

        $html = self::preview($view,$data,$containerViewFile,false);
        $text = trim(self::preview($view,$data,$containerViewFile,true));
        if (String::isHtml($text) || $text == '')
            $text = T("This is an HTML message. Please use a HTML capable mail program to read this message.");

		$mail = new Zend_Mail(Settings::get(Settings::ENCODING));
		$fromName = Settings::get(self::FROM_NAME);
		$fromMail = Settings::get(self::FROM_MAIL);
		$mail->setFrom($fromMail,$fromName);
		$mail->setReplyTo(Settings::get(self::REPLY_MAIL,$fromMail),$fromName);
		$mail->setReturnPath(Settings::get(self::RETURN_MAIL,$fromMail),$fromName);
		$mail->setSubject($subject);
        $mail->setBodyHtml($html);
		$mail->addTo($email, $name);
		$mail->setBodyText($text);
        $mail->send();
    }
}