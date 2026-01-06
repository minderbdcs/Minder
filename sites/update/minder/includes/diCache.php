<?php

return array (
    'MinderNG\\PageMicrocode\\JsonRpc\\Api' =>
        array (
            'factory' => function($pageCache, $microcodeCache, $dice) {
                return new MinderNG\PageMicrocode\JsonRpc\Api($pageCache, $microcodeCache, $dice);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\PageCache',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            1 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\MicrocodeCache',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            2 =>
                                array (
                                    'className' => 'MinderNG\\Di\\DiContainer',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\PageCache' =>
        array (
            'factory' => function($pageManager, $cache) {
                return new MinderNG\PageMicrocode\PageCache($pageManager, $cache);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\PageManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            1 =>
                                array (
                                    'className' => 'Zend_Cache_Core',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\PageManager' =>
        array (
            'factory' => function($sysMenuProvider) {
                return new MinderNG\PageMicrocode\Component\PageManager($sysMenuProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysMenu',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysMenu' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysMenu($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                    'setDefaultCompanyId' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\MicrocodeCache' =>
        array (
            'factory' => function($microcodeManager, $cache) {
                return new MinderNG\PageMicrocode\MicrocodeCache($microcodeManager, $cache);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\MicrocodeManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            1 =>
                                array (
                                    'className' => 'Zend_Cache_Core',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\MicrocodeManager' =>
        array (
            'factory' => function($componentManager, $dice) {
                return new MinderNG\PageMicrocode\MicrocodeManager($componentManager, $dice);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\ComponentManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            1 =>
                                array (
                                    'className' => 'MinderNG\\Di\\DiContainer',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\ComponentManager' =>
        array (
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\PageManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            1 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\ScreenManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            2 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\FormManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            3 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\TabManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            4 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\FieldManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            5 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\TableManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            6 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\DataSetManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            7 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\DataSourceFieldManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            8 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\TransactionManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            9 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\TransactionFieldManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            10 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\DataIdentifierManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            11 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\CompanyManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            12 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\WarehouseManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            13 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\DeviceManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            14 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\ButtonManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            15 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\FilterManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            16 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\ValidatorManager',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            17 =>
                                array (
                                    'className' => 'MinderNG\\PageMicrocode\\Component\\Validator\\Page',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                            18 =>
                                array (
                                    'className' => 'Minder',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\ScreenManager' =>
        array (
            'factory' => function($sysScreenProvider) {
                return new MinderNG\PageMicrocode\Component\ScreenManager($sysScreenProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreen',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreen' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreen($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                    'setDefaultCompanyId' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\FormManager' =>
        array (
            'factory' => function($formProvider) {
                return new MinderNG\PageMicrocode\Component\FormManager($formProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreenForm',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreenForm' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreenForm($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                    'setDefaultCompanyId' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\TabManager' =>
        array (
            'factory' => function($tabProvider) {
                return new MinderNG\PageMicrocode\Component\TabManager($tabProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreenTab',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreenTab' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreenTab($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\FieldManager' =>
        array (
            'factory' => function($sysScreenVarProvider) {
                return new MinderNG\PageMicrocode\Component\FieldManager($sysScreenVarProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreenVar',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreenVar' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreenVar($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\TableManager' =>
        array (
            'factory' => function($tableProvider) {
                return new MinderNG\PageMicrocode\Component\TableManager($tableProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreenTable',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreenTable' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreenTable($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\TransactionFieldManager' =>
        array (
            'factory' => function($transactionProvider) {
                return new MinderNG\PageMicrocode\Component\TransactionFieldManager($transactionProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\SysScreenTransaction',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\SysScreenTransaction' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\SysScreenTransaction($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                ),
        ),
    'MinderNG\\PageMicrocode\\Component\\DataIdentifierManager' =>
        array (
            'factory' => function($paramProvider) {
                return new MinderNG\PageMicrocode\Component\DataIdentifierManager($paramProvider);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Database\\Table\\Param',
                                    'defaultValueAvailable' => false,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
    'MinderNG\\Database\\Table\\Param' =>
        array (
            'factory' => function($config) {
                return new MinderNG\Database\Table\Param($config);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => false,
                                    'defaultValueAvailable' => true,
                                    'defaultValue' =>
                                        array (
                                        ),
                                ),
                        ),
                ),
        ),
    'MinderNG\\Di\\DiContainer' =>
        array (
            'factory' => function($manager) {
                return new MinderNG\Di\DiContainer($manager);
            },
            'methods' =>
                array (
                    '__construct' =>
                        array (
                            0 =>
                                array (
                                    'className' => 'MinderNG\\Di\\ReflectionManager',
                                    'defaultValueAvailable' => true,
                                    'defaultValue' => NULL,
                                ),
                        ),
                ),
        ),
);