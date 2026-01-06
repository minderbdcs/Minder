<?php

class Minder_Paginator extends Zend_Paginator {

    public function getState() {
        return new Minder_Paginator_State($this->getItemCountPerPage(), $this->getCurrentPageNumber());
    }

    public function getFirstVisibleItemNumber() {
        return ($this->getCurrentPageNumber() - 1) * $this->getItemCountPerPage() + 1;
    }

    public function getLastVisibleItemNumber() {
        return ($this->getCurrentPageNumber() - 1) * $this->getItemCountPerPage() + $this->getCurrentItemCount();
    }

    /**
     * @param Minder_Paginator_ItemAddress $itemAddress
     * @return boolean
     */
    protected function _isLastItem($itemAddress) {
        $itemAddress = $this->normalizeItemAddress($itemAddress);
        $globalNo    = $this->getAbsoluteItemNumber($itemAddress->itemNo, $itemAddress->pageNo);

        return $globalNo >= $this->getTotalItemCount();
    }

    /**
     * @param Minder_Paginator_ItemAddress $itemAddress
     * @return bool
     */
    protected function _isFirstItem($itemAddress) {
        $itemAddress = $this->normalizeItemAddress($itemAddress);

        return ($itemAddress->itemNo == 1) && ($itemAddress->pageNo == 1);
    }

    /**
     * @param Minder_Paginator_ItemAddress $itemAddress
     * @return Minder_Paginator_ItemAddress | null
     */
    public function getNextItemAddress($itemAddress) {
        $itemAddress = $this->normalizeItemAddress($itemAddress);

        if ($this->_isLastItem($itemAddress))
            return null;

        if ($itemAddress->itemNo == $this->getItemCountPerPage())
            return new Minder_Paginator_ItemAddress(1, $itemAddress->pageNo + 1);

        return new Minder_Paginator_ItemAddress($itemAddress->itemNo + 1, $itemAddress->pageNo);
    }

    public function getPrevioseItemAddress($itemAddress) {
        $itemAddress = $this->normalizeItemAddress($itemAddress);

        if ($this->_isFirstItem($itemAddress))
            return null;

        if ($itemAddress->itemNo == 1)
            return new Minder_Paginator_ItemAddress($this->getItemCountPerPage(), $itemAddress->pageNo - 1);

        return new Minder_Paginator_ItemAddress($itemAddress->itemNo - 1, $itemAddress->pageNo);
    }

    /**
     * @return Minder_Paginator_ItemAddress
     */
    public function getFirstItemAddress() {
        return new Minder_Paginator_ItemAddress(1, 1);
    }

    /**
     * @return Minder_Paginator_ItemAddress|null
     */
    public function getLastItemAddress() {
        $totalItems = $this->getTotalItemCount() - 1;

        if ($totalItems < 0)
            return null;

        $pageNo = floor($totalItems / $this->getItemCountPerPage()) + 1;
        $itemNo = ($totalItems % $this->getItemCountPerPage()) + 1;

        return new Minder_Paginator_ItemAddress($itemNo, $pageNo);
    }

    /**
     * @param Minder_Paginator_ItemAddress $itemAddress
     * @return Minder_Paginator_ItemAddress
     */
    public function normalizeItemAddress($itemAddress) {
        $itemAddress->itemNo = $this->normalizeItemNumber($itemAddress->itemNo);

        if ( is_null($itemAddress->pageNo))
            $itemAddress->pageNo = $this->getCurrentPageNumber();

        $itemAddress->pageNo = $this->normalizePageNumber($itemAddress->pageNo);

        return $itemAddress;
    }
}