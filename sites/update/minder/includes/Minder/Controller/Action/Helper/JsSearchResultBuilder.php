<?php

class Minder_Controller_Action_Helper_JsSearchResultBuilder extends Zend_Controller_Action_Helper_Abstract {
    /**
     * @var Minder_SysScreen_Builder
     */
    protected $_screenBuilder;

    protected function _sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }

    public function build($ssName, $namespace, $extra = array(), $required = true) {
        return $this->buildScreenDescription($ssName, $namespace, $extra, $required);
    }

    public function buildEmptyScreenDescription($ssName, $namespace, $extra = array()) {
        $screenDescription = $extra;

        $screenDescription['sysScreenName'] = $ssName;
        $screenDescription['namespace']     = $namespace;

        if (!isset($screenDescription['sysScreenCaption'])) {
            $screenDescription['sysScreenCaption'] = $this->_getScreenBuilder()->getSysScreenTitle($ssName);
        }

        return $screenDescription;
    }

    public function buildScreenDescription($ssName, $namespace, $extra = array(), $required = true) {
        $screenDescription = $extra;

        $screenDescription['sysScreenName'] = $ssName;
        $screenDescription['namespace']     = $namespace;

        if (!isset($screenDescription['sysScreenCaption'])) {
            $screenDescription['sysScreenCaption'] = $this->_getScreenBuilder()->getSysScreenTitle($ssName);
        }

        $screenBuilder = $this->_getScreenBuilder();

        list($screenDescription['fields'], $screenDescription['tabs']) = $screenBuilder->buildSysScreenSearchResult($ssName, $required);

        $screenDescription['tabs'] = array_values($screenDescription['tabs']);
        usort($screenDescription['tabs'], array($this, '_sortCallback'));
        $screenDescription['fields'] = array_values($screenDescription['fields']);

        list($screenDescription['buttons']) = array_values($screenBuilder->buildScreenButtons($ssName));
        usort($screenDescription['buttons'], array($this, '_sortCallback'));

        return $screenDescription;
    }

    public function buildScreenSearchResult($screenName, $namespace, $extra = array()) {
        return array(
            'name'              => $screenName,
            'namespace'         => $namespace,
            'searchResults'     => $this->buildScreenDescription($screenName, $namespace, $extra),
        );
    }

    public function buildEmptyResult($screenName, $namespace, $extra = array()) {
        return array(
            'name'              => $screenName,
            'namespace'         => $namespace,
            'searchResults'     => $this->buildEmptyScreenDescription($screenName, $namespace, $extra),
        );
    }

    /**
     * @return Minder_SysScreen_Builder
     */
    protected function _getScreenBuilder()
    {
        if (empty($this->_screenBuilder)) {
            $this->_screenBuilder = new Minder_SysScreen_Builder();
        }
        return $this->_screenBuilder;
    }
}