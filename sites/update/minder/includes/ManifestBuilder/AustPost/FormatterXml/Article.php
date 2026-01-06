<?php

class ManifestBuilder_AustPost_FormatterXml_Article extends ManifestBuilder_AustPost_FormatterXml_PartFormatter {
    public function addItem(ManifestBuilder_AustPost_Model_Item $item ) {
        if (!$item->isValid()) {
            return;
        }

        $node = $this->getDocument()->createElement('ContentsItem');
        //$node->appendChild($this->getDocument()->createElementWithTextNode('GoodsDescription', $item->SHORT_DESC));
        $node->appendChild($this->getDocument()->createElementWithTextNode('GoodsDescription', $item->getGoodsDescription()));
        $node->appendChild($this->getDocument()->createElementWithTextNode('Quantity', $item->QTY_PICKED));

        $this->getNode()->appendChild($node);
    }
}
