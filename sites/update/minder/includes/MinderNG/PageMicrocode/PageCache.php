<?php

namespace MinderNG\PageMicrocode;

use MinderNG\PageMicrocode\Component\PageManager;

class PageCache {
    /**
     * @var PageManager
     */
    private $_pagetManager;

    /**
     * @var \Zend_Cache_Core
     */
    private $_cache;

    function __construct(PageManager $pageManager, \Zend_Cache_Core $cache)
    {
        $this->_pagetManager = $pageManager;
        $this->_cache = $cache;
    }

    public function getCurrentUserAndDevicePages(\Minder2_Environment $environment) {
        $key = $environment->getCurrentUser()->USER_ID;
        $pages = $this->_cache->load($key);

        if (false === $pages) {
            $pages = $this->_pagetManager->getCurrentUserAndDevicePages($environment);
        }

        $this->_cache->save($pages, $key);

        return $pages;
    }
}