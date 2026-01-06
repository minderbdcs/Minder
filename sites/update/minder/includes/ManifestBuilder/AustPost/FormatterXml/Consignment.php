<?php

class ManifestBuilder_AustPost_FormatterXml_Consignment extends ManifestBuilder_AustPost_FormatterXml_PartFormatter {
    public function addArticle(ManifestBuilder_AustPost_Model_Article $article) {
        $node = $this->getDocument()->createElement('PCMSDomesticArticle');

        $document = $this->getDocument();
        $node->appendChild($document->createElementWithTextNode('ArticleNumber', $article->articleNo));
        $node->appendChild($document->createElementWithTextNode('BarcodeArticleNumber', $article->getBarcodeArticleNumber()));

        $node->appendChild($document->createElementWithTextNode('Length', $article->getLength()));
        $node->appendChild($document->createElementWithTextNode('Width', $article->getWidth()));
        $node->appendChild($document->createElementWithTextNode('Height', $article->getHeight()));

        $node->appendChild($document->createElementWithTextNode('ActualWeight', $article->getActualWeight()));

        $node->appendChild($document->createElementWithTextNode('IsTransitCoverRequired', $article->getIsTransitCoverRequired()));
        $node->appendChild($document->createElementWithTextNode('TransitCoverAmount', $article->getTransitCoverAmount()));

        $this->getNode()->appendChild($node);

        return new ManifestBuilder_AustPost_FormatterXml_Article($node, $this->getDocument());
    }
}