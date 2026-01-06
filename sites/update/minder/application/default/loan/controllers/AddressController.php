<?php
/**
 * UserController
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Minder
 *
 * Action controller
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class AddressController extends Minder_Controller_Action
{
    public function listAction()
    {
        $this->_helper->json($this->minder->getAddresses($this->_getParam('type'), $this->_getParam('person_id')));
    }

    public function showAction()
    {
        $this->_helper->json($this->minder->getAddress($this->_getParam('id')));
    }
}
