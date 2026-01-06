<?php

spl_autoload_register(function($className)
{
    /** @noinspection PhpIncludeInspection */
    include implode('/', explode('_', $className)) . '.php';
});

include(__DIR__ . '/../vendor/autoload.php');

// Setup the environment and includes path
define('ROOT_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));

set_include_path(get_include_path()
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'library'
    . PATH_SEPARATOR . ROOT_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'PEAR');

$options = new Zend_Console_Getopt(array('in=s' => 'path to source file', 'out-s' => 'path to destination file'));

$options->parse();
$source = $options->getOption('in');

if (empty($source)) {
    return;
}

$target = $options->getOption('out');
$target = empty($target) ? './out.yml' : $target;

echo "Source " . $source . "\nTarget " . $target . "\n";

$config = (array)json_decode(file_get_contents($source), true);

$data = convert($config);

PHPUnit_Extensions_Database_DataSet_YamlDataSet::write(new ArrayDataSet($data), $target);

function convolve($recordIterator) {
    $result = array();

    foreach ($recordIterator as $lazyRecord) {
        $record = $lazyRecord();

        if (!empty($record['__TABLE__'])) {
            $table = $record['__TABLE__'];
            unset($record['__TABLE__']);

            $result[$table] = isset($result[$table]) ? $result[$table] : array();
            $result[$table][] = $record;
        }
    }

    return $result;
}

function setConverter($recordConverter) {
    return function($setName, $config, $lazyWrapperConfig) use($recordConverter) {
        $result = new AppendIterator();
        $sequenceGenerator = sequenceGenerator();

        foreach ($config as $instanceName => $instanceConfig) {
            $result->append($recordConverter($instanceName, normaliseKeys($instanceConfig), $lazyWrapperConfig, $sequenceGenerator()));
        }

        return $result;
    };
}

function recordConverter($nestedConverterList, $createCallback, $idGenerator = null, $sequenceGenerator = null, $addEmptyKey = true, $tableName = null) {
    return function($name, $config, $lazyWrapperConfig, $sequence = null) use($tableName, $sequenceGenerator, $idGenerator, $createCallback, $nestedConverterList, $addEmptyKey) {
        $result = new AppendIterator();

        $config = normaliseKeys($config);

        list($config, $nestedConfigs) = extractNested($config, array_keys($nestedConverterList));

        $id = is_callable($idGenerator) ? $idGenerator() : '';
        $sequence = is_callable($sequenceGenerator) ? $sequenceGenerator() : $sequence;

        $lazyInstance = lazyCall($createCallback, $name, $config, $lazyWrapperConfig, $id, $sequence);

        $result->append(fillNested($lazyInstance, $nestedConfigs, $nestedConverterList));

        if (!empty($tableName)) {
            if ($addEmptyKey || !empty($name)) {
                $result->append(new ArrayIterator(array(lazyCall('addTable', $lazyInstance, $tableName))));
            }
        }

        return $result;
    };
}

function addTable($lazyInstance, $tableName) {
    return array_merge($lazyInstance(), array('__TABLE__' => $tableName));
}

/**
 * @param $callable
 * @param ...
 * @return Closure
 */
function lazyCall($callable) {
    $args = func_get_args();
    $result = null;

    return function() use($args, &$result) {
        $result = is_null($result) ? call_user_func_array(array_shift($args), $args) : $result;
        return $result;
    };
}

function defaults($callable, $data) {
    return function() use ($callable, $data) {
        return array_merge($data, call_user_func_array($callable, func_get_args()));
    };
}

/**
 * @param $id
 * @param int $step
 * @return Closure
 */
function sequenceGenerator($id = 0, $step = 1) {
    return function() use(&$id, $step) {
        return $id += $step;
    };
}

function convert($config) {
    $fieldConverter = recordConverter(array(), 'createField', sequenceGenerator(99999999, -1), null, true, 'SYS_SCREEN_VAR');
    $tabConverter = recordConverter(array('fields' => setConverter($fieldConverter)), 'createTab', sequenceGenerator(99999999, -1), null, false, 'SYS_SCREEN_TAB');
    $buttonConverter = recordConverter(array(), 'createButton', sequenceGenerator(99999999, -1), null, true, 'SYS_SCREEN_BUTTON');

    $formId = sequenceGenerator(99999999, -1);
    $formSequence = sequenceGenerator();
    $nestedConverterList = array('tabs' => setConverter($tabConverter), 'buttons' => setConverter($buttonConverter));

    $varianceConverter = recordConverter(
        array(
            'dataSource' => setConverter(recordConverter(array(), 'createDataSet', sequenceGenerator(99999999, -1), null, true, 'SYS_SCREEN_TABLE')),
            'searchForm' => recordConverter($nestedConverterList, defaults('createForm', array('SSF_TYPE' => 'SE')), $formId, $formSequence, true, 'SYS_SCREEN_FORM'),
            'resultForm' => recordConverter($nestedConverterList, defaults('createForm', array('SSF_TYPE' => 'SR')), $formId, $formSequence, true, 'SYS_SCREEN_FORM'),
            'editForms' => setConverter(recordConverter($nestedConverterList, defaults('createForm', array('SSF_TYPE' => 'ER')), $formId, $formSequence, true, 'SYS_SCREEN_FORM')),
            'transactions' => transactionSetConverter(sequenceGenerator(99999999, -1), 'SYS_SCREEN_TRANSACTION'),
        ),
        'createVariance'
    );
    $screenConverter = recordConverter(array('variance' => setConverter($varianceConverter)), 'createScreen', sequenceGenerator(99999999, -1), null, true, 'SYS_SCREEN');
    $menuConverter = recordConverter(array('screens' => setConverter($screenConverter)), 'createMenu', sequenceGenerator(99999999, -1), null, true, 'SYS_MENU');

    $converter = setConverter($menuConverter);

    return convolve($converter('', $config, array()));
}

/**
 * @param $lazyInstance
 * @param $nestedConfigs
 * @param $nestedConverterList
 * @return Iterator
 */
function fillNested($lazyInstance, $nestedConfigs, $nestedConverterList)
{
    $result = new AppendIterator();

    foreach ($nestedConfigs as $key => $config) {
        $result->append(call_user_func($nestedConverterList[$key], $key, $config, $lazyInstance));
    }

    return $result;
}

/**
 * @param $menuConfig
 * @param $nestedKeys
 * @return array
 */
function extractNested($menuConfig, $nestedKeys)
{
    $nestedConfigs = array();

    foreach ($nestedKeys as $key) {
        if (isset($menuConfig[$key])) {
            $nestedConfigs[$key] = normaliseKeys($menuConfig[$key]);
            unset($menuConfig[$key]);
        }
    }

    return array($menuConfig, $nestedConfigs);
}

/**
 * @param $menuId
 * @param $menuConfig
 * @param $lazyWrapperConfig
 * @param $recordId
 * @return array $name, $config, $lazyWrapperConfig, $id
 * $name, $config, $lazyWrapperConfig, $id
 */
function createMenu($menuId, $menuConfig, $lazyWrapperConfig, $recordId)
{
    return array_merge(array(
        'RECORD_ID' => $recordId,
        'SM_SUBMENU_ID' => $menuId,
        'SM_MENU_TYPE' => empty($menuConfig['SM_MENU_ID']) ? 'TOP' : 'LEFT',
        'SM_MENU_STATUS' => 'OK',
        'SM_MENU_ACTION' => formatActionUrl($menuId, $menuConfig),
    ), $menuConfig);
}

function formatActionUrl($subMenu, $menuConfig) {
    return '/pages/' . (empty($menuConfig['SM_MENU_ID']) ? '' : $menuConfig['SM_MENU_ID']) . '-' . $subMenu;
}

/**
 * @param $sysScreenName
 * @param $screenConfig
 * @param $lazyMenuConfig
 * @param $recordId
 * @return array
 */
function createScreen($sysScreenName, $screenConfig, $lazyMenuConfig, $recordId)
{
    $menuConfig = $lazyMenuConfig();

    return array_merge(array('RECORD_ID' => $recordId, 'SS_NAME' => $sysScreenName, 'SS_MENU_ID' => $menuConfig['SM_SUBMENU_ID']), $screenConfig);
}

function createVariance($name, $config, $lazyScreenConfig, $id) {
    return $lazyScreenConfig();
}

function transactionSetConverter($idGenerator, $tableName) {
    return function($setName, $config, $lazyVarianceConfig) use ($idGenerator, $tableName) {
        $transactions = array();

        $sequence = sequenceGenerator();
        $varianceConfig = $lazyVarianceConfig();

        foreach ($config as $type => $typeConfig) {
            foreach (normaliseKeys($typeConfig) as $action => $actionConfig) {
                foreach (normaliseKeys($actionConfig) as $trnType => $trnTypeConfig) {
                    foreach (normaliseKeys($trnTypeConfig) as $class => $classConfig) {
                        foreach (normaliseKeys($classConfig) as $role => $roleConfig) {
                            foreach (normaliseKeys($roleConfig) as $field => $fieldConfig) {
                                $transactions[] = lazyCall('addTable', lazyCall('createTransactionConfig',
                                    array(
                                        'RECORD_ID' => $idGenerator(),
                                        'SST_SEQUENCE' => $sequence(),
                                        'SST_TRN_TYPE' => $trnType,
                                        'SST_TRN_CLASS' => $class,
                                        'SS_NAME' => $varianceConfig['SS_NAME'],
                                        'SST_SCREEN_TYPE' => $type,
                                        'SST_ACTION' => $action
                                    ),
                                    $role,
                                    $field,
                                    $fieldConfig
                                ), $tableName);
                            }
                        }
                    }
                }
            }
        }

        return new ArrayIterator($transactions);
    };
}

function createTransactionConfig($commonConfig, $role, $field, $fieldConfig) {
    if ($role == 'RESULT') {
        $result = parseColumnAlias($field);
    } else {
        $result = array('SST_TRN_FIELD' => $field);
    }

    $result = array_merge($commonConfig, $result, array('SST_FIELD_ROLE' => $role), parseTransactionField($role, $fieldConfig));

    if (isset($result['SST_COLUMN_EXPRESSION'])) {
        $result['SST_COLUMN_EXPRESSION'] = is_array($result['SST_COLUMN_EXPRESSION'])
            ? json_encode($result['SST_COLUMN_EXPRESSION'])
            : $result['SST_COLUMN_EXPRESSION'];
    }

    if (isset($result['SST_TRN_FIELD_EXPRESSION'])) {
        $result['SST_TRN_FIELD_EXPRESSION'] = is_array($result['SST_TRN_FIELD_EXPRESSION'])
            ? json_encode($result['SST_TRN_FIELD_EXPRESSION'])
            : $result['SST_TRN_FIELD_EXPRESSION'];
    }

    return $result;
}

function parseColumnAlias($alias) {
    list($tableName, $fieldName) = explode('.', $alias);
    return array(
        'SST_TABLE' => $tableName,
        'SST_COLUMN' => $fieldName
    );
}

function parseTransactionField($role, $fieldConfig) {
    $parts = explode(' ', $fieldConfig);
    $field = array_shift($parts);
    $template = array_shift($parts);

    if ($role == 'RESULT') {
        $result = array('SST_TRN_FIELD' => $field);
    } else {
        $result = parseColumnAlias($field);

    }

    return array_merge($result, getTemplate($template));
}

function getTemplate($template) {
    switch ($template) {
        case 'WH_PART':
            return array('SST_COLUMN_EXPRESSION' => array('trim' => array(), "substring" => array(0, 2)));
        case 'LOCN_PART':
            return array('SST_COLUMN_EXPRESSION' => array('trim' => array(), "substring" => array(2)));
        case 'SUB_FIELD0':
            return array('SST_TRN_FIELD_EXPRESSION' => array('trim' => array(), "sub_field" => array(0)));
    }

    return array();
}

/**
 * @param $formName
 * @param $config
 * @param $lazyVarianceConfig
 * @param $recordId
 * @return array
 * $name, $config, $lazyWrapperConfig, $id
 */
function createForm($formName, $config, $lazyVarianceConfig, $recordId)
{
    $varianceConfig = $lazyVarianceConfig();

    return array_merge(
        array('RECORD_ID' => $recordId,  'SSF_NAME' => $formName, 'SS_NAME' => $varianceConfig['SS_NAME'], 'SSF_STATUS' => 'OK'),
        $config
    );
}

/**
 * @param $buttonName
 * @param $buttonConfig
 * @param $lazyFormConfig
 * @param $recordId
 * @return array
 */
function createButton($buttonName, $buttonConfig, $lazyFormConfig, $recordId)
{
    $formConfig = $lazyFormConfig();

    return array_merge(
        array(
            'RECORD_ID' => $recordId,
            'SS_NAME' => $formConfig['SS_NAME'],
            'SSF_NAME' => $formConfig['SSF_NAME'],
            'SSB_BUTTON_TYPE' => $formConfig['SSF_TYPE'],
            'SSB_TAB_STATUS' => 'OK',
            'SSB_BUTTON_NAME' => $buttonName
        ),
        $buttonConfig
    );
}

/**
 * @param $tabName
 * @param $tabConfig
 * @param $lazyFormConfig
 * @param $recordId
 * @return array
 * @internal param $sysScreenName
 * @internal param $formType
 * @internal param $formName
 */
function createTab($tabName, $tabConfig, $lazyFormConfig, $recordId)
{
    $formConfig = $lazyFormConfig();
    $tab = array_merge(
        array(
            'RECORD_ID' => $recordId,
            'SS_NAME' => $formConfig['SS_NAME'],
            'SST_TAB_NAME' => $tabName,
            'SSF_NAME' => $formConfig['SSF_NAME'],
            'SST_FIELD_TYPE' => $formConfig['SSF_TYPE'],
            'SST_TAB_STATUS' => "OK"
        ),
        $tabConfig
    );
    return $tab;
}

/**
 * @param $fieldId
 * @param $fieldConfig
 * @param $lazyTabConfig
 * @param $recordId
 * @return array
 */
function createField($fieldId, $fieldConfig, $lazyTabConfig, $recordId)
{
    $tabConfig = $lazyTabConfig();

    if (isset($fieldConfig['SSV_INPUT_METHOD'])) {
        $fieldConfig['SSV_INPUT_METHOD'] = is_array($fieldConfig['SSV_INPUT_METHOD']) ? json_encode($fieldConfig['SSV_INPUT_METHOD']) : $fieldConfig['SSV_INPUT_METHOD'];
    }

    if (isset($fieldConfig['SSV_INPUT_METHOD_NEW'])) {
        $fieldConfig['SSV_INPUT_METHOD_NEW'] = is_array($fieldConfig['SSV_INPUT_METHOD_NEW']) ? json_encode($fieldConfig['SSV_INPUT_METHOD_NEW']) : $fieldConfig['SSV_INPUT_METHOD_NEW'];
    }

    $key = array_merge(
        array(
            'RECORD_ID' => $recordId,
            'SS_NAME' => $tabConfig['SS_NAME'],
            'SSV_TAB' => $tabConfig['SST_TAB_NAME'],
            'SSF_NAME' => $tabConfig['SSF_NAME'],
            'SSV_FIELD_TYPE' => $tabConfig['SST_FIELD_TYPE'],
            'SSV_FIELD_STATUS' => 'OK'
        ),
        $fieldConfig
    );
    return $key;
}

/**
 * @param $tableName
 * @param $tableConfig
 * @param $lazyVarianceConfig
 * @param $recordId
 * @param $sequence
 * @return array
 */
function createDataSet($tableName, $tableConfig, $lazyVarianceConfig, $recordId, $sequence)
{
    $varianceConfig = $lazyVarianceConfig();

    return array_merge(
        array('RECORD_ID' => $recordId, 'SST_TABLE' => $tableName, 'SS_NAME' => $varianceConfig['SS_NAME'], 'SST_SEQUENCE' => $sequence, 'SST_TABLE_STATUS' => "OK"),
        $tableConfig
    );
}

function normaliseKeys($source) {
    $result = array();

    foreach($source as $key => $value) {
        $parts = explode(' ', $key, 2);
        $realKey = array_shift($parts);
        $realKey = ($realKey == '*') ? '' : $realKey;

        $result[$realKey] = isset($result[$realKey]) ? $result[$realKey] : array();

        if (count($parts) < 1) {
            $result[$realKey] = $value;
        } else {
            $reminder = array_shift($parts);
            $result[$realKey][$reminder] = $value;
        }
    }

    return $result;
}