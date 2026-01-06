<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenVar;

class FieldManager {
    private $_sysScreenVarProvider;

    function __construct(SysScreenVar $sysScreenVarProvider)
    {
        $this->_sysScreenVarProvider = $sysScreenVarProvider;
    }

    public function getScreenFields(ScreenCollection $screens, \Minder2_Environment $environment) {
        $fields = $this->_sysScreenVarProvider->getScreenFields(
            iterator_to_array($screens->pluck('SS_NAME')),
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );

        $result = new FieldCollection();
        $result->init($fields, new AddOptions(false, true));

        return $result;
    }

}