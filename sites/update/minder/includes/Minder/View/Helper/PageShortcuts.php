<?php

class Minder_View_Helper_PageShortcuts extends Zend_View_Helper_Abstract {

    protected function _buildShortcuts($shortcuts, $tooltips) {
        if (empty($shortcuts))
            return '';

        $xhtml = '';
        foreach ($shortcuts as $name => $url) {
            $title = (isset($tooltips[$name])) ? $tooltips[$name] : '';

            if (is_array($url)) {
                $xhtml .= '<a class="menuitem submenuheader" href="#" title="' . $this->view->escape($title) . '">' . $this->view->escape($name) . '</a>' . PHP_EOL;
                $xhtml .= '<div class="submenu"><ul>' . PHP_EOL;

                foreach ($url as $subName => $subUrl) {
                    $title = (isset($tooltips[$subName])) ? $tooltips[$subName] : '';
                    $xhtml .= '<li><a href="' . $this->view->escape($subUrl) . '" title="' . $this->view->escape($title) . '">' . $this->view->escape($subName) . '</a></li>' . PHP_EOL;
                }

                $xhtml .= '</ul></div>' . PHP_EOL;
            } else {
                $xhtml .= '<a class="menuitem" href="' . $this->view->escape($url) . '" title="' . $this->view->escape($title) . '">' . $this->view->escape($name) . '</a>' . PHP_EOL;
            }
        }

        return $xhtml;
    }

    public function pageShortcuts($shortcuts = array(), $tooltips = array(), $plusButtonUrl = '', $minusButtonUrl = '') {
        if (empty($shortcuts) || !is_array($shortcuts))
            return '';

        $tooltips = (is_array($tooltips)) ? $tooltips : array();
        $baseUrl = $this->view->baseUrl();

        return '
            <h2>Shortcuts</h2>
            <div class="glossymenu">' . PHP_EOL
            . $this->_buildShortcuts($shortcuts, $tooltips) . PHP_EOL
            . '</div>
            <script >
                ddaccordion.init({
                    headerclass: "submenuheader",
                    contentclass: "submenu",
                    collapseprev: true,
                    defaultexpanded: [],
                    animatedefault: false,
                    persiststate: true,
                    toggleclass: ["", ""],
                    togglehtml: ["suffix", "<img src=\'' . $baseUrl . $plusButtonUrl . '\' class=\'statusicon\' />", "<img src=\'' . $baseUrl . $minusButtonUrl . '\' class=\'statusicon\' />"],
                    animatespeed: "normal",
                    oninit:function(headers, expandedindices){
                        //do nothing
                    },
                    onopenclose:function(header, index, state, isclicked){
                        //do nothing
                    }
                    });
            </script>

            ';
    }
}