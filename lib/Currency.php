<?php
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
     * @var Currency_Provider_Abstract
     */
    private $provider = null;
    private $data = null;
    private $baseCurrency = null;
    /**
     *
     * @return Currency_Provider_Abstract
     */
    public function getProvider() {
        return $this->provider;
    }

    /**
     *
     * @param Currency_Provider_Abstract $provider
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
                    throw new Exception(Translate::_('Ingen valuta kilde angivet!'));
            $this->data = $this->getProvider()->getData($this->getBaseCurrency());
        }
        return $this->data;
    }

}