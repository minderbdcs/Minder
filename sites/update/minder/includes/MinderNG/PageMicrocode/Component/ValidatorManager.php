<?php

namespace MinderNG\PageMicrocode\Component;

use MinderNG\Validator;

class ValidatorManager {
    /**
     * @var Validator\Factory
     */
    private $_factory;

    /** @var Validator\Field[] */
    private $_validatorList;

    /** @var Validator\Field[] */
    private $_validatorListNew;

    /**
     * ValidatorManager constructor.
     * @param Validator\Factory $factory
     */
    public function __construct(Validator\Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * @param Field $field
     * @return Validator\Field
     */
    public function getFieldValidator(Field $field) {
        if (empty($this->_validatorList[$field->getId()])) {
            $this->_validatorList[$field->getId()] = $this->_createFieldValidator($field->SSV_ALIAS, $field->SSV_TITLE, $field->METHOD->validator);
        }

        return $this->_validatorList[$field->getId()];
    }

    /**
     * @param Field $field
     * @return Validator\Field
     */
    public function getFieldValidatorNew(Field $field) {
        if (empty($this->_validatorListNew[$field->getId()])) {
            $this->_validatorListNew[$field->getId()] = $this->_createFieldValidator($field->SSV_ALIAS, $field->SSV_TITLE, $field->METHOD->validator);
        }

        return $this->_validatorListNew[$field->getId()];
    }

    /**
     * @param $alias
     * @param $title
     * @param array $validator
     * @return Validator\Field
     */
    private function _createFieldValidator($alias, $title, array $validator) {
        return new Validator\Field($alias, $title, $this->_factory->set($validator));
    }
}