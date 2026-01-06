<?php
  
class Minder_View_Helper_SysScreenFormDL extends Zend_View_Helper_FormElement
{
    public function sysScreenFormDL($attribs = array(), $fieldDesc) {
        
        if (empty($fieldDesc['SSV_TABLE']))
            $ddName  = $fieldDesc['SSV_ALIAS'];
        else 
            $ddName  = $fieldDesc['SSV_TABLE'] . '-' . $fieldDesc['SSV_ALIAS'];

        $ddValue = '';
        $inName  = '';
        $inValue = '';
        
        if (isset($attribs['search_element'])) {
            $ddName  = 'SEARCH-' . $ddName;
            if (isset($fieldDesc['SEARCH_VALUE']))
                $ddValue = $fieldDesc['SEARCH_VALUE'];
        }
        
        $options = array();
        
        foreach ($fieldDesc['ELEMENTS'] as $optDesc) {
            
            if (empty($optDesc['SSV_TABLE']))
                $tmpName    = $optDesc['SSV_ALIAS'];
            else 
                $tmpName    = $optDesc['SSV_TABLE'] . '-' . $optDesc['SSV_ALIAS'];
            $tmpCaption = $optDesc['SSV_TITLE'];
            
            if (empty($optDesc['SSV_TABLE']))
                $tmpOptId = $optDesc['SSV_ALIAS'];
            else 
                $tmpOptId = $optDesc['SSV_TABLE'] . '-' . $optDesc['SSV_ALIAS'];
                
            if (isset($attribs['search_element'])) {
                $tmpName  = 'SEARCH-' . $tmpName;
                $tmpOptId = 'SEARCH-' . $tmpOptId;
            }
            
            if ($tmpOptId == $ddValue) {
                $inName  = $tmpOptId;
                if (isset($optDesc['SEARCH_VALUE']))
                    $inValue = $optDesc['SEARCH_VALUE'];
            }
            
            $options[$tmpName] = $tmpCaption;
        }
        
        if (isset($attribs['search_element']) && !empty($ddValue)) {
            $inName  = 'SEARCH-' . $inName;
        }
        
        $ddId = $ddName;
        if (isset($attribs['id'])) {
            $ddId = $attribs['id'];
        }
        
        $tmpInputMethod = explode('|', $fieldDesc['SSV_INPUT_METHOD']);
        $inId = 'SEARCH-DL_' . $tmpInputMethod[1];

        $options = array_merge(array('' => ''), $options);
        
        $xhtml  = $this->view->formSelect($ddName, $ddValue, $attribs, $options);
        
        $tmpAttribs       = $attribs;
        $tmpAttribs['id'] = $inId;
        
        $xhtml .= '</td><th>----></th><td>' . $this->view->formText($inName, $inValue, $tmpAttribs);
        $xhtml .= '
            <script type="text/javascript">
                $("#' . $ddId . '").change(function() {
                    $("#' . $inId . '").attr("name", $(this).val()).val("");
                });
            </script>
        ';
        
        return $xhtml;
    }
    
}