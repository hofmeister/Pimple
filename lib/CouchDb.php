<?php
/**
 * Just a wrapper around PHP-On-Couch
 * @link https://github.com/dready92/PHP-on-Couch
 */


class CouchDB {
    const URL = "COUCHDB:URL";
    const NAME = "COUCHDB:NAME";
    private static $_client;
    /**
     *
     * @return couchClient
     */
    public static function client() {
        if (self::$_client == null) {
            $basepath = Pimple::instance()->getRessource('lib/phponcouch/');
            require_once "$basepath/couch.php";
            require_once "$basepath/couchAdmin.php";
            require_once "$basepath/couchClient.php";
            require_once "$basepath/couchDocument.php";
            require_once "$basepath/couchReplicator.php";
            self::$_client = new couchClient(Settings::get(self::URL),Settings::get(self::NAME));
        }
        return self::$_client;
    }
    public static function createDocument($values = array()) {
        $doc = new couchDocument(self::client());
        foreach ($values as $key => $value) {
            $doc->set($key, $value);
        }
        return $doc;
    }
}