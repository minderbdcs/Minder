<?php

class Minder_LabelPrinter_Factory {

    /**
     * @param string $labelType
     * @throws Exception
     * @return Minder_LabelPrinter_Interface
     */
    static function getLabelPrinter($labelType) {
        switch ($labelType) {
            case 'ISSN':
                return new Minder_LabelPrinter_Issn();
            case 'GRN_ORDER':
                return new Minder_LabelPrinter_GrnOrder();
            case 'LOCATION':
                return new Minder_LabelPrinter_Location();
	    case 'BORROWER':
		return new Minder_LabelPrinter_Borrower();
            case 'PICK_LABEL':
                return new Minder_LabelPrinter_PickLabel();
            case 'LOGON':
                return new Minder_LabelPrinter_Logon();
            case 'PRODUCT_LABEL':
            case 'PRODUCT_INNER':
            case 'PRODUCT_OUTER':
                return new Minder_LabelPrinter_Product($labelType);
            case 'SSCC':
                return new Minder_LabelPrinter_Sscc();
            case 'PACK_ID':
                return new Minder_LabelPrinter_CrudPackId();
            case 'COST_CENTRE':
                return new Minder_LabelPrinter_CostCentre($labelType);
            default:
                throw new Exception('Unsupported label type ' . $labelType);
        }
    }
}
