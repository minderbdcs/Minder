<?php
/**
 * Minder
 *
 * PHP version 5.2.3
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
 * Order
 *
 * All access to the Minder database is through the Minder class.
 *
 * @category  Minder
 * @package   Minder
 * @author    Rich Buggy <rich@zoombug.com.au>
 * @copyright 2007 Barcoding & Data Collection Systems
 * @license   http://www.barcoding.com.au/licence.html B&DCS Licence
 * @link      http://www.barcoding.com.au/
 */
class ProductProfile extends Model
{
    public $prodId;
    public $shortDesc;
    public $ssnDescription;
    public $salePrice;
    public $gst;
    public $availableQty;
    public $uom;

    public function __construct() {
        $this->prodId = '';
        $this->shortDesc = '';
        $this->ssnDescription = '';
        $this->salePrice = '';
        $this->gst = '';
        $this->availableQty = '';
        $this->uom = '';
    }
}
