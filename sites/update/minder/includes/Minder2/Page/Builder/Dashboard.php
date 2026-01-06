<?php

class Minder2_Page_Builder_Dashboard extends Minder2_Page_Builder {

    const ORDER_STATISTICS = 'ORDER_STATISTICS';

    public function build($menuId)
    {
        $page = parent::build($menuId);

        $orderStatisticsScreen = $this->getScreenBuilder(self::ORDER_STATISTICS)->build(self::ORDER_STATISTICS);
        $page->addScreen($orderStatisticsScreen);

        return $page;
    }

}