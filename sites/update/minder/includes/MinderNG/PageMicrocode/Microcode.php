<?php

namespace MinderNG\PageMicrocode;

use MinderNG\Di\DiContainer;
use MinderNG\Events;

class Microcode implements Events\PublisherAggregateInterface {
    private $_publisher;
    /**
     * @var Component\Components
     */
    private $_pageComponents;

    /**
     * @var DiContainer
     */
    private $_dice;

    function __construct(Component\Components $pageComponents)
    {
        $this->_pageComponents = $pageComponents;
    }


    /**
     * @return Events\PublisherInterface
     */
    public function getPublisher()
    {
        if (empty($this->_publisher)) {
            $this->_publisher = new Events\Publisher();
        }

        return $this->_publisher;
    }

    /**
     * @return Component\Components
     */
    public function getPageComponents()
    {
        return $this->_pageComponents;
    }

    /**
     * serialize() checks if your class has a function with the magic name __sleep.
     * If so, that function is executed prior to any serialization.
     * It can clean up the object and is supposed to return an array with the names of all variables of that object that should be serialized.
     * If the method doesn't return anything then NULL is serialized and E_NOTICE is issued.
     * The intended use of __sleep is to commit pending data or perform similar cleanup tasks.
     * Also, the function is useful if you have very large objects which do not need to be saved completely.
     *
     * @return array|NULL
     * @link http://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.sleep
     */
    function __sleep()
    {
        return array('_publisher', '_pageComponents');
    }


    /**
     * @return DiContainer
     */
    public function getDice()
    {
        return $this->_dice;
    }

    /**
     * @param DiContainer $dice
     */
    public function setDice($dice)
    {
        $this->_dice = $dice;
    }
}