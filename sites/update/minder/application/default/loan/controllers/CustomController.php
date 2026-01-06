<?php
/**
 * Minder
 *
 * PHP version 5.2.4
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @version   SVN: <svn_id>
 * @link      http://www.barcoding.com.au/
 *
 */

/**
 * Function ( minder_array_merge() );
 */
include "functions.php";

/**
 * CustomController
 *
 * @category  Minder
 * @package   Minder
 * @author    Sergey Boroday <sergey.boroday@binary-studio.com>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class CustomController extends Zend_Controller_Action
{
    public function init()
    {
        $this->minder = Minder::getInstance();

        $this->session = new Zend_Session_Namespace('blobs');
        $this->initView();
    }

    public function __call($method, $args)
    {
        $method = strtolower($method);
        switch ($method) {
            case 'logojpgaction':
                $this->view->data = $this->minder->getLogo();
                $this->getResponse()->setHeader('Content-type: image/jpeg');
                break;
            default:
                if ($this->minder->userId != null) {
                    if (isset($this->session->blob[substr($method, 0, -6)])) {
                        $this->view->data = $this->session->blob[substr($method, 0, -6)];
                    } else {
                        $this->view->data = '';
                    }
                } else {
                        $this->view->data = 'Please logon to view this data';
                }
            break;
        }

        return $this->render('index');
    }
}
