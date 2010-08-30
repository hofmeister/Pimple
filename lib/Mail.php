<?php
class Mail {
    const SMTP_HOST = 'smtp_host';
    const SMTP_PORT = 'smtp_port';
    const SMTP_SSL  = 'smtp_ssl';
    const SMTP_TLS  = 'smtp_tls';
    const SMTP_USER = 'smtp_user';
    const SMTP_PASS = 'smtp_pass';
    const SMTP_POP3HOST = 'smtp_pop3host';
    const DEBUG = 'mail_debug';
    const FROM_NAME = 'mail_from_name';
    const FROM_MAIL = 'mail_from_mail';
    const REPLY_MAIL = 'mail_reply_mail';
    const RETURN_MAIL = 'mail_return_mail';

    public static function preview($view,$data) {
        $containerViewFile = Dir::normalize(BASEDIR).'view/mail.php';
        $mailViewFile = Dir::normalize(BASEDIR)."view/mail/$view.php";
        $mailContainer = new View($containerViewFile);
        $mailView = new View($mailViewFile);
        return $mailContainer->render(array('body'=>$mailView->render($data),'subject'=>$subject,'name'=>$name,'email'=>$email));
    }
    public static function send($email,$name,$subject,$view,$data) {
        require_once Pimple::instance()->getRessource('lib/mimemail/email_message.php');
        require_once Pimple::instance()->getRessource('lib/mimemail/smtp_message.php');
        require_once Pimple::instance()->getRessource('lib/mimemail/smtp.php');
        $html = self::preview($view,$data);
        

        $email_message=new smtp_message_class();

        /* This computer address */
        $email_message->localhost=$_SERVER['HTTP_HOST'];

        /* SMTP server address, probably your ISP address,
         * or smtp.gmail.com for Gmail
         * or smtp.live.com for Hotmail */
        $email_message->smtp_host=Settings::get(self::SMTP_HOST,'localhost');

        /* SMTP server port, usually 25 but can be 465 for Gmail */
        $email_message->smtp_port=Settings::get(self::SMTP_PORT,25);

        /* Use SSL to connect to the SMTP server. Gmail requires SSL */
        $email_message->smtp_ssl=Settings::get(self::SMTP_SSL,0);

        /* Use TLS after connecting to the SMTP server. Hotmail requires TLS */
        $email_message->smtp_start_tls=Settings::get(self::SMTP_TLS,0);

        /* authentication user name */
        $email_message->smtp_user=Settings::get(self::SMTP_USER,'');

        /* authentication password */
        $email_message->smtp_password=Settings::get(self::SMTP_PASS,'');

        /* if you need POP3 authetntication before SMTP delivery,
         * specify the host name here. The smtp_user and smtp_password above
         * should set to the POP3 user and password*/
        $email_message->smtp_pop3_auth_host=Settings::get(self::SMTP_POP3HOST,'');

        /* authentication realm or Windows domain when using NTLM authentication */
        $email_message->smtp_realm="";

        /* authentication workstation name when using NTLM authentication */
        $email_message->smtp_workstation="";

        /* force the use of a specific authentication mechanism */
        $email_message->smtp_authentication_mechanism="PLAIN";
        
        /* Output dialog with SMTP server */
        $email_message->smtp_debug=Settings::get(self::DEBUG,0);

        /* if smtp_debug is 1,
         * set this to 1 to make the debug output appear in HTML */
        $email_message->smtp_html_debug=1;
        $email_message->default_charset = 'UTF-8';

        /* If you use the SetBulkMail function to send messages to many users,
         * change this value if your SMTP server does not accept sending
         * so many messages within the same SMTP connection */
        $email_message->maximum_bulk_deliveries=100;
        $from_mail = Settings::get(self::FROM_MAIL,'');
        $from_name = Settings::get(self::FROM_NAME,'');
        $reply_mail = Settings::get(self::REPLY_MAIL,$from_mail);
        $return_mail = Settings::get(self::RETURN_MAIL,$from_mail);

        $email_message->SetEncodedEmailHeader("To",$email,$name);
        $email_message->SetEncodedEmailHeader("From",$from_mail,$from_name);
        $email_message->SetEncodedEmailHeader("Reply-To",$reply_mail,$from_name);
        $email_message->SetHeader("Return-Path",$return_mail);
        $email_message->SetEncodedEmailHeader("Errors-To",$return_mail,$from_name);
        $email_message->SetEncodedHeader("Subject",$subject);

        $email_message->AddQuotedPrintableHTMLPart($html,"UTF-8");

        $text_message= T("This is an HTML message. Please use an HTML capable mail program to read this message.");
        $email_message->AddQuotedPrintableTextPart($email_message->WrapText($text_message),"UTF-8");
        //var_dump($email_message);
        $error = $email_message->Send();

        for($recipient=0,Reset($email_message->invalid_recipients);$recipient<count($email_message->invalid_recipients);next($email_message->invalid_recipients),$recipient++)
            MessageHandler::instance ()->addError("Invalid recipient: ".key($email_message->invalid_recipients)." Error: ".$email_message->invalid_recipients[key($email_message->invalid_recipients)]);
        if($error)
            throw new ErrorException ($error);

    }
}