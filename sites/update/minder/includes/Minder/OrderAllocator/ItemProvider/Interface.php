<?php

interface Minder_OrderAllocator_ItemProvider_Interface {
    function selectProdIdToAllocate($productLimit);

    function selectProdIdAndPickLabelNoToAllocate();

    function selectPickOrderToAllocate($orderLimit);
}