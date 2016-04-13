<?php

/**
 *
 * @author bs
 *
 */

namespace Hebis\RecordDriver;

/**
 * //TODO: insert Class Description
 * Class ProxyURLBuilder
 * @package Hebis\RecordDriver
 */
class ProxyUrlBuilder
{

    /**
     * @var bool
     */
    private $withProxy = false;

    /**
     * @var string
     */
    private $proxy = "";

    /**
     * @var bool
     */
    private $restricted = true;

    /**
     * @var bool
     */
    private $encoded = true;

    /**
     *
     */
    public function __construct()
    {

        global $configArray;

        // Define Guest-Access
        //ignorier ich erst mal
        // $this->isrestricted = UserAccount::isUserRestricted();
        $this->restricted = false;
        // Proxy falls eingestellt
        if (isset($configArray['hproxy']['host'])) {
            $this->withProxy = true;
            $this->proxy = $configArray['hproxy']['host'];
        }

        // not encoded falls eingeschaltet
        if (isset($configArray['hproxy']['urlencode']) and ($configArray['hproxy']['urlencode'] === "0"))
            $this->encoded = false;

    }

    /**
     *
     */
    public function hasProxy()
    {
        return $this->withProxy;
    }

    /**
     *
     */
    public function isRestricted()
    {
        return $this->restricted;
    }


    /**
     *
     * @param string $url
     * @return string
     */
    public function addProxy($url)
    {
        if ($this->encoded) {

            return $this->proxy . urlencode($url);
        }

        return $this->proxy . $url;
    }

    /**
     *
     * @param string $url
     * @return mixed|string
     */
    public function removeProxy($url)
    {
        if ($this->encoded) {

            return urldecode(str_replace($this->proxy, "", $url));
        }

        return str_replace($this->proxy, "", $url);
    }
}

