<?php

class Minder_Form_Decorator_GridLayout extends Zend_Form_Decorator_FormElements {
    public function getColumns() {
        $columns = $this->getOption('columns');

        if (empty($columns))
            return 3;

        return $columns;
    }

    /**
     * @param Zend_Form_Element $item
     */
    protected function _getItemWidth($item) {
        $minderOptions = $item->getAttrib('minderOptions');

        if (empty($minderOptions) || !is_array($minderOptions))
            return 1;

        return isset($minderOptions['WIDTH']) ? intval($minderOptions['WIDTH']) : 1;
    }

    public function render($content)
    {
        $form    = $this->getElement();
        if ((!$form instanceof Zend_Form) && (!$form instanceof Zend_Form_DisplayGroup)) {
            return $content;
        }

        $belongsTo      = ($form instanceof Zend_Form) ? $form->getElementsBelongTo() : null;
        $separator      = $this->getSeparator();
        $translator     = $form->getTranslator();
        $view           = $form->getView();

        $columns = $this->getColumns();
        $occupiedColumns = 0;

        $elementContent = '<table class="summary"><tr>';

        /**
         * @var Minder_Form_Element_GridElementInterface|Zend_Form|Zend_Form_DisplayGroup|Zend_Form_Element $item
         */
        foreach ($form as $item) {

            $item->setView($view)
                 ->setTranslator($translator);
            if ($item instanceof Zend_Form_Element) {
                $item->setBelongsTo($belongsTo);
            } elseif (!empty($belongsTo) && ($item instanceof Zend_Form)) {
                if ($item->isArray()) {
                    $name = $this->mergeBelongsTo($belongsTo, $item->getElementsBelongTo());
                    $item->setElementsBelongTo($name, true);
                } else {
                    $item->setElementsBelongTo($belongsTo, true);
                }
            } elseif (!empty($belongsTo) && ($item instanceof Zend_Form_DisplayGroup)) {
                foreach ($item as $element) {
                    $element->setBelongsTo($belongsTo);
                }
            }

            if ($occupiedColumns + $this->_getItemWidth($item) > $columns) {
                while ($occupiedColumns++ < $columns)
                    $elementContent .= '<th>&nbsp;</th><td>&nbsp;</td>';

                $occupiedColumns = 0;
                $elementContent .= '</tr><tr>';
            }

            $elementContent .= '<th>' . $item->getLabel() . '</th>';
            $colspan = $this->_getItemWidth($item) * 2 - 1;
            $elementContent .= '<td colspan="' . $colspan . '">' . $item->render() . '</td>';
            $occupiedColumns += $this->_getItemWidth($item);

            if (($item instanceof Zend_Form_Element_File)
                || (($item instanceof Zend_Form)
                    && (Zend_Form::ENCTYPE_MULTIPART == $item->getEnctype()))
                || (($item instanceof Zend_Form_DisplayGroup)
                    && (Zend_Form::ENCTYPE_MULTIPART == $item->getAttrib('enctype')))
            ) {
                if ($form instanceof Zend_Form) {
                    $form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
                } elseif ($form instanceof Zend_Form_DisplayGroup) {
                    $form->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
                }
            }
        }

        while ($occupiedColumns++ < $columns)
            $elementContent .= '<td>&nbsp;</td><td>&nbsp;</td>';
        $elementContent .= '</tr></table>';

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $elementContent . $separator . $content;
            case self::APPEND:
            default:
                return $content . $separator . $elementContent;
        }
    }

}