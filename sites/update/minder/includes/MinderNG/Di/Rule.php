<?php
/* @description     fork of Dice - A minimal Dependency Injection Container for PHP originally written by Tom Butler
 * @see             http://r.je/dice.html
 * @version         1.1.1+1
 */
namespace MinderNG\Di;

class Rule {
    public $shared = false;
    public $constructParams = array();
    public $substitutions = array();
    public $newInstances = array();
    public $instanceOf;
    public $call = array();
    public $inherit = true;
    public $shareInstances = array();
}
