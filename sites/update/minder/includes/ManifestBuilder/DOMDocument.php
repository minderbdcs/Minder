<?php

class ManifestBuilder_DOMDocument extends DOMDocument {
    public function createElementWithTextNode($name, $text) {
        $element = $this->createElement($name);
        //$element->appendChild($this->createTextNode(iconv('UTF-8', 'UTF-8//IGNORE', $text)));
        $element->appendChild($this->createTextNode(iconv('UTF-8', 'UTF-8//TRANSLIT//IGNORE', $text)));
        return $element;
    }

    public function appendChildNodes(array $nodes, DOMNode $parentNode = null) {
        $root = is_null($parentNode) ? $this : $parentNode;

        foreach ($nodes as $node) {
            $root->appendChild($node);
        }
    }

    public function createNodesFromArray(array $data) {
        $result = array();

        foreach ($data as $nodeName => $text) {
            $result[] = $this->createElementWithTextNode($nodeName, $text);
        }

        return $result;
    }
}
