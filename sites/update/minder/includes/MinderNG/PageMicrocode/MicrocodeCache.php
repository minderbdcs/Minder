<?php

namespace MinderNG\PageMicrocode;

use MinderNG\Di\DiContainer;
use MinderNG\PageMicrocode\Component\Page;

class MicrocodeCache {
    const CLASS_NAME = 'MinderNG\\PageMicrocode\\MicrocodeCache';
    const EXTRA_LIFETIME = 3600;

    /**
     * @var MicrocodeManager
     */
    private $_microcodeManager;

    /**
     * @var \Zend_Cache_Core
     */
    private $_cache;

    function __construct(MicrocodeManager $microcodeManager, \Zend_Cache_Core $cache)
    {
        $this->_microcodeManager = $microcodeManager;
        $this->_cache = $cache;
    }

    public function getPageMicrocode(Page $page, \Minder2_Environment $environment, DiContainer $dice) {
        $id = $this->_buildId($page, $environment);

        if (false === ($pageMicrocode = $this->_cache->load($id))) {
            $pageMicrocode = $this->_microcodeManager->getPageMicrocode($page, $environment);
        }

        $pageMicrocode->setDice($dice);
        $pageMicrocode->getPageComponents()->getMessages()->removeObsolete();

        return $pageMicrocode;
    }

    public function savePageMicrocode(Microcode $microcode, \Minder2_Environment $environment) {
        $page = $microcode->getPageComponents()->page;
        $id = $this->_buildId($page, $environment);
        $tags = $this->_buildTags($page, $environment);

        return $this->_cache->save($microcode, $id, $tags);
    }

    public function cleanCache(Page $page, \Minder2_Environment $environment) {
        $tags = $this->_buildTags($page, $environment);
        return $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
    }

    public function cleanSessionCache() {
        return $this->_cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(\Zend_Session::getId()));
    }

    public function touch(Page $page, \Minder2_Environment $environment) {
        return $this->_cache->touch($this->_buildId($page, $environment), static::EXTRA_LIFETIME);
    }

    private function _buildId(Page $page, \Minder2_Environment $environment) {
        return implode('_', $this->_buildTags($page, $environment));
    }

    private function _buildTags(Page $page, \Minder2_Environment $environment){
        return array(
            \Zend_Session::getId(),
            $environment->getCurrentUser()->USER_ID,
            $page->SM_MENU_ID,
            $page->SM_SUBMENU_ID,
        );
    }
}