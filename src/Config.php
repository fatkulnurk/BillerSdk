<?php
namespace Fatkulnurk\BillerSdk;

class Config
{
    private static $instance = null;
    private string $baseUrl = 'http://bayarcepat.test/api';
    private string $token;
    private string $merchantId;

    private function __construct()
    {
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return Config
     */
    public function setToken(string $token): Config
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return Config
     */
    public function setMerchantId(string $merchantId): Config
    {
        $this->merchantId = $merchantId;
        return $this;
    }

}