<?php
/**
 * Provides currency convertion
 */
class Currency {
    private static $instance;
    /**
     *
     * @return Currency
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     * @var Currency_Provider
     */
    private $provider = null;
    private $data = null;
    private $baseCurrency = null;
    /**
     *
     * @return Currency_Provider 
     */
    public function getProvider() {
        return $this->provider;
    }

    /**
     *
     * @param Currency_Provider $provider
     */
    public function setProvider($provider) {
        $this->provider = $provider;
    }
    public function getBaseCurrency() {
        return $this->baseCurrency;
    }

    public function setBaseCurrency($baseCurrency) {
        $this->baseCurrency = $baseCurrency;
    }
    public function convertFrom($amount,$currencyCode) {
        return $this->convertFromTo($amount,$currencyCode, $this->getBaseCurrency());
    }
    public function convertTo($amount,$currencyCode) {
        return $this->convertFromTo($amount,$this->getBaseCurrency(),$currencyCode);
    }
    public function convertFromTo($amount,$fromCurrency,$toCurrency) {
        if (strtoupper($fromCurrency) == strtoupper($toCurrency)) return $amount;
        $from = $this->getData()->get($fromCurrency);
        $to = $this->getData()->get($toCurrency);
        return $amount * ($from / $to);
    }
    /**
     *
     * @return Currency_Data
     */
    public function getData() {
        if (!$this->data) {
            if ($this->getProvider() == null)
                    throw new Exception(Translate::_('No currency provider added'));
            $this->data = $this->getProvider()->getData($this->getBaseCurrency());
        }
        return $this->data;
    }

}
/**
 * Currency data - returned from a currency provider
 */
class Currency_Data extends System_Collection_List {
    private $baseCurrency;
    public function getBaseCurrency() {
        return $this->baseCurrency;
    }

    public function setBaseCurrency($baseCurrency) {
        if ($this->baseCurrency == $baseCurrency) return;
        $this->baseCurrency = $baseCurrency;
        if (count($this->data) > 0) {
            $this->recalculate($baseCurrency);
        }
        $this->__set($baseCurrency,100);
    }
    public function recalculate($baseCurrency = null) {
        if ($this->baseCurrency != null
            && $baseCurrency != null
            && $this->baseCurrency != $baseCurrency) {
            $oldBase = $this->baseCurrency;
            $oldBaseAmount = $this->__get($oldBase);
            $newBaseAmount = $this->__get($baseCurrency);
            $oldBaseCurrency = $oldBaseAmount / $newBaseAmount;
            $this->__set($oldBase,$oldBaseCurrency);
            foreach($this->data as $iso=>$currency) {
                $this->__set($iso, $oldBaseCurrency * $oldBaseCurrency);
            }
        }
    }
}
/**
 * Abstract class for a curreny provider
 */
abstract class Currency_Provider{
    /**
     *
     * @return Currency_Data
     */
    abstract public function getData();
}
/**
 * Currency provider implementation for the danish national bank
 */
class Currency_Provider_NationalBankDK extends System_Currency_Provider_Abstract {

    /**
     *
     * @return Currency_Data
     */
    public function getData() {
        $cacheId = 'System_Currency_Provider_NationalBankDK';
        $url = 'http://www.nationalbanken.dk/dndk/valuta.nsf/valutakurser.xml';
        $xml = simplexml_load_file($url);
        $data = new System_Currency_Data();
        $data->setBaseCurrency('DKK');
        foreach($xml->Kursdato->Valutakurser as $currency) {
            $data->__set((string)$currency->iso,(float)str_replace(array('.',','),array('','.'),$currency->kurs));
        }
        System_Cache::getInstance()->objectSave($data);
        return $data;
    }
}