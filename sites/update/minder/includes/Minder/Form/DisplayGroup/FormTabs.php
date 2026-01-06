<?php

class Minder_Form_DisplayGroup_FormTabs extends Minder_Form_DisplayGroup {
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('FormTabs')
                 ->addDecorator('FormTabsContainer');
        }
    }

}