<?php

class ManifestBuilder_AustPost_FormatterXml_PcmsManifest extends ManifestBuilder_AustPost_FormatterXml_PartFormatter {

    public function addConsignment(ManifestBuilder_AustPost_Model_Consignment $consignment) {
        $node = $this->getDocument()->createElement('PCMSConsignment');
        $document = $this->getDocument();

        $node->appendChild($document->createElementWithTextNode('ConsignmentNumber', $consignment->getConnoteNo()));
        $node->appendChild($document->createElementWithTextNode('ChargeCode', $consignment->getServiceChargeCode()));
        if ($consignment->hasReturnName()) $node->appendChild($document->createElementWithTextNode('ReferenceNo1', $consignment->getReference1()));
        if ($consignment->hasReturnName()) $node->appendChild($document->createElementWithTextNode('ReferenceNo2', $consignment->getReference2()));
        $node->appendChild($document->createElementWithTextNode('DeliveryName', $consignment->getDeliveryName()));
        //if (!empty($consignment->FIRST_NAME) and 
        //    $consignment->hasDeliveryEmailAddress()) $node->appendChild($document->createElementWithTextNode('EmailNotification', 'Y'));
        if ($consignment->hasReturnName() and 
            $consignment->hasDeliveryEmailAddress()) $node->appendChild($document->createElementWithTextNode('EmailNotification', 'Y'));

        $node->appendChild($document->createElementWithTextNode('DeliveryAddressLine1', $consignment->getDeliveryAddressLine1()));
        if ($consignment->hasDeliveryAddressLine2()) $node->appendChild($document->createElementWithTextNode('DeliveryAddressLine2', $consignment->getDeliveryAddressLine2()));
        if ($consignment->hasDeliveryAddressLine3()) $node->appendChild($document->createElementWithTextNode('DeliveryAddressLine3', $consignment->getDeliveryAddressLine3()));
        if ($consignment->hasDeliveryAddressLine4()) $node->appendChild($document->createElementWithTextNode('DeliveryAddressLine4', $consignment->getDeliveryAddressLine4()));

        if ( $consignment->hasDeliveryPhoneNumber()) $node->appendChild($document->createElementWithTextNode('DeliveryPhoneNumber', $consignment->getDeliveryPhoneNumber()));
        if ( $consignment->hasDeliveryEmailAddress()) $node->appendChild($document->createElementWithTextNode('DeliveryEmailAddress', $consignment->getDeliveryEmailAddress()));
        $node->appendChild($document->createElementWithTextNode('DeliverySuburb', $consignment->getDeliverySuburb()));
        $node->appendChild($document->createElementWithTextNode('DeliveryStateCode', $consignment->getDeliveryStateCode()));
        $node->appendChild($document->createElementWithTextNode('DeliveryPostcode', $consignment->getDeliveryPostCode()));
        $node->appendChild($document->createElementWithTextNode('DeliveryCountryCode', $consignment->getDeliveryCountryCode()));
        $node->appendChild($document->createElementWithTextNode('IsInternationalDelivery', $consignment->getIsInternationalDelivery()));

        //if (!empty($consignment->FIRST_NAME)) $node->appendChild($document->createElementWithTextNode('ReturnName', $consignment->FIRST_NAME));
        if ($consignment->hasReturnName()) $node->appendChild($document->createElementWithTextNode('ReturnName', $consignment->getReturnName()));
        $node->appendChild($document->createElementWithTextNode('ReturnAddressLine1', $consignment->getReturnAddressLine1()));
        if ($consignment->hasReturnAddressLine2()) $node->appendChild($document->createElementWithTextNode('ReturnAddressLine2', $consignment->getReturnAddressLine2()));
        if ($consignment->hasReturnAddressLine3()) $node->appendChild($document->createElementWithTextNode('ReturnAddressLine3', $consignment->getReturnAddressLine3()));
        if ($consignment->hasReturnAddressLine4()) $node->appendChild($document->createElementWithTextNode('ReturnAddressLine4', $consignment->getReturnAddressLine4()));

        $node->appendChild($document->createElementWithTextNode('ReturnSuburb', $consignment->CITY));
        $node->appendChild($document->createElementWithTextNode('ReturnStateCode', $consignment->STATE));
        $node->appendChild($document->createElementWithTextNode('ReturnPostcode', $consignment->POST_CODE));
        $node->appendChild($document->createElementWithTextNode('ReturnCountryCode', $consignment->COUNTRY));

        $node->appendChild($document->createElementWithTextNode('CreatedDateTime', $consignment->createDateTime));
        $node->appendChild($document->createElementWithTextNode('PostChargeToAccount', $consignment->ACCOUNT));
        $node->appendChild($document->createElementWithTextNode('IsSignatureRequired', $consignment->getIsSignatureRequired()));
        $node->appendChild($document->createElementWithTextNode('CTCAmount', '0'));
        $node->appendChild($document->createElementWithTextNode('DeliverPartConsignment', $consignment->getDeliverPartConsignment()));
        $node->appendChild($document->createElementWithTextNode('ContainsDangerousGoods', $consignment->containsDangerousGoods));

        $this->getNode()->appendChild($node);

        return new ManifestBuilder_AustPost_FormatterXml_Consignment($node, $this->getDocument());
    }
}
