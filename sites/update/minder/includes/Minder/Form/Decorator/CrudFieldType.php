<?php

class Minder_Form_Decorator_CrudFieldType extends Zend_Form_Decorator_Abstract {

    /**
     * Default placement: prepend
     * @var string
     */
    protected $_placement = 'PREPEND';

    /**
     * HTML tag with which to surround label
     * @var string
     */
    protected $_tag;

    public function getFieldType() {
        if (null === ($element = $this->getElement())) {
            return '';
        }

        return $element->getAttrib('fieldType');
    }

    /**
     * Set HTML tag with which to surround label
     *
     * @param  string $tag
     * @return Zend_Form_Decorator_Label
     */
    public function setTag($tag)
    {
        if (empty($tag)) {
            $this->_tag = null;
        } else {
            $this->_tag = (string) $tag;
        }
        return $this;
    }

    /**
     * Get HTML tag, if any, with which to surround label
     *
     * @return string
     */
    public function getTag()
    {
        if (null === $this->_tag) {
            $tag = $this->getOption('tag');
            if (null !== $tag) {
                $this->removeOption('tag');
                $this->setTag($tag);
            }
            return $tag;
        }

        return $this->_tag;
    }

    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $fieldType = $this->getFieldType();
        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $tag       = $this->getTag();

        if (empty($fieldType) && empty($tag)) {
            return $content;
        }

        if (empty($fieldType)) {
            $fieldType = '&nbsp;';
        }

        if (null !== $tag) {
            /** @noinspection PhpIncludeInspection */
            require_once 'Zend/Form/Decorator/HtmlTag.php';
            $decorator = new Zend_Form_Decorator_HtmlTag();
            $decorator->setOptions(array('tag' => $tag));

            $fieldType = $decorator->render($fieldType);
        }

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $fieldType;
            default:
                return $fieldType . $separator . $content;
        }
    }

}