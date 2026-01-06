<?php

class Minder_Page_FormController_EditForm_Ssn extends Minder_Page_FormController_EditForm_Default {
    protected $_mapper = null;

    protected function _getMinder() {
        return Minder::getInstance();
    }

    /**
     * @return Minder_Page_SysScreenMapper_Ssn
     */
    protected function _getMapper()
    {
        if (is_null($this->_mapper))
            $this->_mapper = new Minder_Page_SysScreenMapper_Ssn(Minder_Page::FORM_TYPE_EDIT_FORM);

        return $this->_mapper;
    }


    public function updateAndPrintAll($data) {
        $response = array();

        $response['record'] = $this->update($data);
        $response['messages'][] = 'Record ' . $this->_getSsnId($data['data'])  . ' saved successfully.';

        try {
            $response['messages'] = array_merge($response['messages'], $this->printAll($data));
        } catch (Exception $e) {
            $response['warnings'] = $e->getMessage();
        }

        return $response;
    }

    protected function _getSsnId($data) {
        return $this->_getMapper()->getSsnId($data);
    }

    public function printAll($data) {
        $issns = $this->_getMinder()->getIssns(array('ORIGINAL_SSN = ?' => $this->_getSsnId($data['data'])));
        $printer = $this->_getMinder()->getPrinter();
        $count = 0;

        foreach ($issns as $issnRow) {
            $issnList = $this->_getMinder()->getIssnForPrint($issnRow->id);
            if (!($printer->printIssnLabel($issnList)))
                throw new Exception('Error while print label(s)');

            $count++;
        }

        return array($count . ' label(s) printed successfully');
    }
}