<?php
  
class Minder_View_Helper_JsSearchResult extends Zend_View_Helper_FormElement
{
    public function jsSearchResult($ssName, $namespace, $attribs = array()) {
        return (isset($attribs['filename'])) ?
            $this->renderNoDbScreenDescription($ssName, $namespace, $attribs) :
            $this->render($ssName, $namespace, $attribs);
    }
    
    protected function sortCallback($a, $b) {
        return $a[$a['ORDER_BY_FIELD_NAME']] - $b[$b['ORDER_BY_FIELD_NAME']];
    }
    
    public function render($ssName, $namespace, $attribs = array()) {
        $screenDescription = $attribs;
        
        $screenDescription['sysScreenName'] = $ssName;
        $screenDescription['namespace']     = $namespace;
        
        $screenBuilder = new Minder_SysScreen_Builder();

        list($screenDescription['fields'], $screenDescription['tabs']) = $screenBuilder->buildSysScreenSearchResult($ssName);

        $screenDescription['tabs'] = array_values($screenDescription['tabs']);
        usort($screenDescription['tabs'], array($this, 'sortCallback'));
        $screenDescription['fields'] = array_values($screenDescription['fields']);

        list($screenDescription['buttons']) = array_values($screenBuilder->buildScreenButtons($ssName));
        usort($screenDescription['buttons'], array($this, 'sortCallback'));

        return $screenDescription;
    }

    public function renderNoDbScreenDescription($ssName, $namespace, $attribs = array()) {
        $screenDescription = $attribs;

        $screenDescription['sysScreenName'] = $ssName;
        $screenDescription['namespace']     = $namespace;

        $screenBuilder = new Minder_SysScreen_FileContentsBuilder();

        list($screenDescription['fields'], $screenDescription['tabs']) =
            $screenBuilder->buildSysScreenSearchResult($ssName, false, $attribs['filename']);

        $screenDescription['tabs'] = array_values($screenDescription['tabs']);
        usort($screenDescription['tabs'], array($this, 'sortCallback'));
        $screenDescription['fields'] = array_values($screenDescription['fields']);

        list($screenDescription['buttons']) = array_values($screenBuilder->buildScreenButtons($ssName));
        usort($screenDescription['buttons'], array($this, 'sortCallback'));

        return $screenDescription;
    }
}