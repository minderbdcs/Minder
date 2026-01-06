<?php

class Minder_ReportManager_Response {
    /**
     * @var string
     */
    protected $_content;

    /**
     * @var Minder_Http_Header_ContentType
     */
    protected $_contentType;

    protected $_bodyMessage;

    function __construct($content, Minder_Http_Header_ContentType $contentType)
    {
        $this->_content = $content;
        $this->_contentType = $contentType;
    }

    public function getBodyMessage() {
        if (empty($this->_bodyMessage)) {
            $this->_bodyMessage = $this->_extractBodyMessage();
        }

        return $this->_bodyMessage;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @return Minder_Http_Header_ContentType
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    private function _extractBodyMessage() {
        if (preg_match('/<h3.*>(?P<message>.*)<\/h3>/mU', $this->_content, $matches)) {
            return $matches['message'];
        }

        return 'Unknown error.';
    }

}