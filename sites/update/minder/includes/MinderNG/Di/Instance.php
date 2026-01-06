<?php
/* @description     fork of Dice - A minimal Dependency Injection Container for PHP originally written by Tom Butler
 * @see             http://r.je/dice.html
 * @version         1.1.1+1
 */
namespace MinderNG\Di;

class Instance {
    public $name;
    public function __construct($instance) {
        $this->name = $instance;
    }
}
