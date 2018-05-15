<?php

/**
 * Description of WPEAE_HTTP_Proxy
 *
 * @author Geometrix
 */
class WPEAE_HTTP_Proxy {

    private $proxy_host = '';
    private $proxy_port = '';
    private $proxy_user = '';
    private $proxy_pass = '';

    public function __construct($init_proxy = "") {
        $proxy = "";
        if ($init_proxy) {
            $proxy = "" . $init_proxy;
        }
        if (get_option('wpeae_use_proxy', false)) {
            $proxy = wpeae_proxy_get();
        }

        if ($proxy) {
            //username:password@proxy.example.com:8080
            $proxy = explode("@", $proxy);
            if (count($proxy) == 1) {
                $proxy_url = explode(":", $proxy[0]);
                if (count($proxy_url) == 1) {
                    $this->proxy_host = $proxy_url[0];
                    $this->proxy_port = '80';
                } else if (count($proxy_url) == 2) {
                    $this->proxy_host = $proxy_url[0];
                    $this->proxy_port = $proxy_url[1];
                }
            } else if (count($proxy) == 2) {
                $proxy_auth = explode(":", $proxy[0]);
                if (count($proxy_auth) == 1) {
                    $this->proxy_user = $proxy_auth[0];
                    $this->proxy_pass = '';
                } else if (count($proxy_auth) == 2) {
                    $this->proxy_user = $proxy_auth[0];
                    $this->proxy_pass = $proxy_auth[1];
                }

                $proxy_url = explode(":", $proxy[1]);
                if (count($proxy_url) == 1) {
                    $this->proxy_host = $proxy_url[0];
                    $this->proxy_port = '80';
                } else if (count($proxy_url) == 2) {
                    $this->proxy_host = $proxy_url[0];
                    $this->proxy_port = $proxy_url[1];
                }
            }
        }
    }

    /**
     * Whether proxy connection should be used.
     *
     * @since 2.8.0
     *
     * @use WP_PROXY_HOST
     * @use WP_PROXY_PORT
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->proxy_host && $this->proxy_port;
    }

    /**
     * Whether authentication should be used.
     *
     * @since 2.8.0
     *
     * @use WP_PROXY_USERNAME
     * @use WP_PROXY_PASSWORD
     *
     * @return bool
     */
    public function use_authentication() {
        return $this->proxy_user && $this->proxy_pass;
    }

    /**
     * Retrieve the host for the proxy server.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function host() {
        if ($this->proxy_host)
            return $this->proxy_host;

        return '';
    }

    /**
     * Retrieve the port for the proxy server.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function port() {
        if ($this->proxy_port)
            return $this->proxy_port;

        return '';
    }

    /**
     * Retrieve the username for proxy authentication.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function username() {
        if ($this->proxy_user)
            return $this->proxy_user;

        return '';
    }

    /**
     * Retrieve the password for proxy authentication.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function password() {
        if ($this->proxy_pass)
            return $this->proxy_pass;

        return '';
    }

    /**
     * Retrieve authentication string for proxy authentication.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function authentication() {
        return $this->username() . ':' . $this->password();
    }

    /**
     * Retrieve header string for proxy authentication.
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function authentication_header() {
        return 'Proxy-Authorization: Basic ' . base64_encode($this->authentication());
    }

    /**
     * Whether URL should be sent through the proxy server.
     *
     * We want to keep localhost and the site URL from being sent through the proxy server, because
     * some proxies can not handle this. We also have the constant available for defining other
     * hosts that won't be sent through the proxy.
     *
     * @since 2.8.0
     *
     * @staticvar array|null $bypass_hosts
     * @staticvar array      $wildcard_regex
     *
     * @param string $uri URI to check.
     * @return bool True, to send through the proxy and false if, the proxy should not be used.
     */
    public function send_through_proxy($uri) {
        //always return true!
        return true;

        /*
         * parse_url() only handles http, https type URLs, and will emit E_WARNING on failure.
         * This will be displayed on sites, which is not reasonable.
         */
        $check = @parse_url($uri);

        // Malformed URL, can not process, but this could mean ssl, so let through anyway.
        if ($check === false)
            return true;

        $home = parse_url(get_option('siteurl'));

        /**
         * Filter whether to preempt sending the request through the proxy server.
         *
         * Returning false will bypass the proxy; returning true will send
         * the request through the proxy. Returning null bypasses the filter.
         *
         * @since 3.5.0
         *
         * @param null   $override Whether to override the request result. Default null.
         * @param string $uri      URL to check.
         * @param array  $check    Associative array result of parsing the URI.
         * @param array  $home     Associative array result of parsing the site URL.
         */
        $result = apply_filters('pre_http_send_through_proxy', null, $uri, $check, $home);
        if (!is_null($result))
            return $result;

        if ('localhost' == $check['host'] || ( isset($home['host']) && $home['host'] == $check['host'] ))
            return false;

        if (!defined('WP_PROXY_BYPASS_HOSTS'))
            return true;

        static $bypass_hosts = null;
        static $wildcard_regex = array();
        if (null === $bypass_hosts) {
            $bypass_hosts = preg_split('|,\s*|', WP_PROXY_BYPASS_HOSTS);

            if (false !== strpos(WP_PROXY_BYPASS_HOSTS, '*')) {
                $wildcard_regex = array();
                foreach ($bypass_hosts as $host)
                    $wildcard_regex[] = str_replace('\*', '.+', preg_quote($host, '/'));
                $wildcard_regex = '/^(' . implode('|', $wildcard_regex) . ')$/i';
            }
        }

        if (!empty($wildcard_regex))
            return !preg_match($wildcard_regex, $check['host']);
        else
            return !in_array($check['host'], $bypass_hosts);
    }

}
