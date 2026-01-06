<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Collection\AddOptions;
use MinderNG\Database\Table\SysScreenTable;

class TableManager {
    private $_tableProvider;

    function __construct(SysScreenTable $tableProvider)
    {
        $this->_tableProvider = $tableProvider;
    }


    public function getScreenTables(ScreenCollection $screens, \Minder2_Environment $environment) {
        $tables = $this->_tableProvider->getScreenTables(
            iterator_to_array($screens->pluck('SS_NAME')),
            $environment->getCurrentUser(),
            $environment->getCurrentDevice(),
            $environment->getCompanyLimit()
        );

        $result = new TableCollection();
        $result->init($tables, new AddOptions(false, true));

        return $result;
    }
}