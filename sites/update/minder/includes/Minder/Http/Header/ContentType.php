<?php

/**
 * Class Minder_Http_Header_ContentType
 *
 * Inspired by Zend Framework (http://framework.zend.com/)
 */
class Minder_Http_Header_ContentType {
    const TYPE_HTML = 'text/html';


    /**
     * @var string
     */
    protected $_value = '';

    /**
     * @var string
     */
    protected $_mediaType = '';

    /**
     * @var array
     */
    protected $_parameters = array();

    function __construct($value, $mediaType, array $parameters) {
        $this->_value = $value;
        $this->_mediaType = $mediaType;
        $this->_parameters = $parameters;
    }

    public static function fromString($headerLine) {
        list($name, $value) = self::_splitHeaderLine($headerLine);

        if (strtolower($name) != 'content-type') {
            throw new Exception('Invalid header line for Content-Type string: "' . $headerLine . '"');
        }

        $parts      = explode(';', $value);
        $mediaType  = array_shift($parts);
        $parameters = array();

        if (count($parts) > 0) {
            foreach ($parts as $parameter) {
                $parameter = trim($parameter);
                if (!preg_match('/^(?P<key>[^\s\=]+)\="?(?P<value>[^\s\"]*)"?$/', $parameter, $matches)) {
                    continue;
                }
                $parameters[$matches['key']] = $matches['value'];
            }
        }

        return new static($value, trim($mediaType), $parameters);
    }

    public function isHtml() {
        return strtolower($this->_mediaType) == self::TYPE_HTML;
    }

    protected static function _splitHeaderLine($headerLine) {
        $parts = explode(':', $headerLine);

        if (count($parts) != 2) {
            throw new Exception('Header must match with the format "name:value"');
        }

        $parts[1] = ltrim($parts[1]);
        return $parts;
    }
}