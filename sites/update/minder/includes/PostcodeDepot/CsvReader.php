<?php

class PostcodeDepot_CsvReader extends Minder_Reader_Csv {

    /**
     * @throws PostcodeDepot_CsvReader_Exception
     * @return array
     */
    protected function _readFields() {
        if (false === ($fields = $this->nextLine()))
            throw new PostcodeDepot_CsvReader_Exception('Bad file format: Fields Line expected but EOF found.');

        return $fields;
    }

    /**
     * @throws PostcodeDepot_CsvReader_Exception
     * @param PostcodeDepot_Adapter_Array $adapter
     * @return PostcodeDepot_Collection
     */
    public function readFile($adapter) {
        try {
            $this->open();
            $adapter->setFieldsOrder($this->_readFields());
            $result = new PostcodeDepot_Collection();

            while ($fileRow = $this->nextLine())
                $result->addElement($adapter->convert($fileRow));

        } catch (Exception $e) {
            $this->close();
            throw new PostcodeDepot_CsvReader_Exception($e->getMessage());
        }
        $this->close();

        return $result;
    }
}