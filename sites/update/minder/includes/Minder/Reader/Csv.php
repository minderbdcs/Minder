<?php

class Minder_Reader_Csv implements Iterator {
    const DELIMETER               = ',';
    const QUOTE_CHARACTER         = '"';

    protected $filePath     = null;
    protected $_filePointer = null;
    protected $_currentLineNo = null;
    protected $_currentLine   = false;

    public function __construct($filePath) {
        $this->setFilePath($filePath);
    }

    /**
     * @param Minder_Reader_Csv $filePath
     * @return \Minder_Reader_Csv
     */
    public function setFilePath($filePath) {
        $this->filePath = $filePath;
        return $this;
    }

    public function open() {
        $realPath = realpath($this->filePath);

        if (!file_exists($realPath))
            throw new Minder_Reader_Exception('File "' . $this->filePath . '" not exists.');

        if (!is_readable($realPath))
            throw new Minder_Reader_Exception('File "' . $this->filePath . '" is not readable.');

        $this->_filePointer = fopen($realPath, 'r');
        if ($this->_filePointer === false)
            throw new Minder_Reader_Exception('Error opening file "' . $this->filePath . '" for reading.');

        $this->_currentLineNo = -1;
    }

    public function close() {
        if (!is_null($this->_filePointer))
            fclose($this->_filePointer);
        $this->_filePointer = null;
        $this->_currentLine = false;
    }

    protected function nextLine() {
        $this->_currentLine = fgetcsv($this->_filePointer, 0, self::DELIMETER, self::QUOTE_CHARACTER);
        $this->_currentLineNo++;
        return $this->_currentLine;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        if (empty($this->_filePointer))
            $this->open();

        fseek($this->_filePointer, 0);
        $this->nextLine();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return !($this->_currentLine === false);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->_currentLine;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return scalar scalar on success, integer
     * 0 on failure.
     */
    public function key()
    {
        return $this->_currentLineNo;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->nextLine();
    }


}