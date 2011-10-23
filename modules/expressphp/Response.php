<?php
require_once(dirname(__FILE__).'/ResponseStatusCodes.php');

/**
 * Modified by outbounder, based on:
 * 
 * Flight: An extensible micro-framework.
 *
 * @copyright   Copyright (c) 2011, Mike Cao <mike@mikecao.com>
 * @license     http://www.opensource.org/licenses/mit-license.php
 */
class Response {

    protected $headers = array();
    protected $status = 200;
    protected $body = "";

    /**
     * Sets the HTTP status of the response.
     *
     * @param int $code HTTP status code.
     */
    public function status($code) {

        if (array_key_exists($code, ResponseStatusCodes::$codes)) {
            if (strpos(php_sapi_name(), 'cgi') !== false)
                $this->header('Status',$code.' '.ResponseStatusCodes::$codes[$code]);
            else
                $this->header(($_SERVER['SERVER_PROTOCOL'] ? $_SERVER['SERVER_PROTOCOL']: 'HTTP/1.1'), ResponseStatusCodes::$codes[$code]);
        }
        else
            throw new Exception('Invalid status code '.$code.' not found in '.ResponseStatusCodes::$codes);

        return $this;
    }

    /**
     * Adds a header to the response.
     *
     * @param string|array $key Header name or array of names and values
     * @param string $value Header value
     */
    public function header($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->headers[$k] = $v;
            }
        }
        else {
            $this->headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Writes content to the response body.
     *
     * @param string $str Response content
     */
    public function write($str) {
        $this->body .= $str;

        return $this;
    }

    /**
     * Clears the response.
     */
    public function clear() {
        $this->headers = array();
        $this->status = 200;
        $this->body = '';

        return $this;
    }

    /**
     * Sets caching headers for the response.
     *
     * @param int|string $expires Expiration time
     */
    public function cache($expires) {
        if ($expires === false) {
            $this->header('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
            $this->header('Cache-Control', array(
                'no-store, no-cache, must-revalidate',
                'post-check=0, pre-check=0',
                'max-age=0'
            ));
            $this->header('Pragma','no-cache');
        }
        else {
            $expires = is_int($expires) ? $expires : strtotime($expires);
            $this->header('Expires', gmdate('D, d M Y H:i:s', $expires) . ' GMT');
            $this->header('Cache-Control','max-age='.($expires - time()));
        }

        return $this;
    }

    /**
     * Sends the response and exits the program.
     * if $body is NULL, then $response->body will be used instead. defaults to "".
     * if $status is NULL, then $response->status will be used instead, defaults to 200.
     * All headers are send using this method if they have not been send already.
     */
    public function send($body = NULL, $status = NULL) {

        if (!headers_sent()) {
            if($status != NULL)
                $this->status($status);

            foreach ($this->headers as $field => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        header($field.': '.$v);
                    }
                }
                else {
                    header($field.': '.$value);
                }
            }
        }

        if($body != NULL)
            exit($body);
        else
            exit($this->body);
    }
}
?>