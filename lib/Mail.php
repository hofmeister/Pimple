<?php
class Mail {
    public static function send($email,$subject,$body) {
        return mail($email,$subject,'<html><head></head><body>'.
                    htmlspecialchars_decode(htmlentities($body.MAIL_SIGNATURE,null,'UTF-8'))
                    .'</body></html>'
                    ,"From: MailRobotten <info@xn--minprivatkonomi-eub.dk>\r\n".
                    "Content-Type: text/html; charset=utf-8");
    }
}