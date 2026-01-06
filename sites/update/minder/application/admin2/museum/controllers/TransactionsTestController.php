<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Mikhail Morozov
 * Date: 18.10.11
 * Time: 13:21
 * To change this template use File | Settings | File Templates.
 */
 
class Admin2_TransactionsTestController extends Minder_Controller_Action {
    public function indexAction()
    {
        $transactionsForm = new Minder_Form_TransactionsTest();
        $this->view->transactionsForm = $transactionsForm;
        $this->view->pageTitle = 'TRANSACTIONS_TEST';
    }

    public function acceptTransactionAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $result = new Minder_JSResponse();

        try {
            $transactionsForm = new Minder_Form_TransactionsTest();

            if (!$transactionsForm->isValid($this->getRequest()->getParams())) {
                $result->errors = $this->_collectErrorMessages($transactionsForm);
                echo json_encode(array('response' => $result));
                return;
            }

            $formValues = $transactionsForm->getValues();
            
            $transaction = new Transaction_Unified(
                $formValues['trn_type'],
                $formValues['trn_code'],
                $formValues['object'],
                $formValues['locn_id'],
                $formValues['wh_id'],
                $formValues['reference'],
                $formValues['qty'],
                $formValues['sub_locn_id']);

            $tran_res = $this->_doTestTransaction($transaction, $formValues['trn_date'], $formValues['input_source']);
            ($tran_res) ? $result->messages = $this->minder->getInstance()->lastError :
                          $result->errors   = $this->minder->getInstance()->lastError;

        } catch (Exception $e) {
            $result->errors[] = $e->getMessage();
        }

        echo json_encode(array('response' => $result));
    }

    protected function _getMenuId()
    {
        return 'ADMIN';
    }

    protected function _collectErrorMessages($form) {
        $result = array();
        /**
         * @var Zend_Form_Element $formElement
         */
        foreach ($form->getElements() as $formElement) {
            if ($formElement->hasErrors()) {
                foreach ($formElement->getMessages() as $error)
                    $result[] = '"' . $formElement->getLabel() . '": ' . $error . '<br>';
            }
        }

        return $result;
    }

    protected  function _doTestTransaction($transaction, $date, $input_source)
    {
        return $this->minder->getInstance()->doTransactionResponse($transaction, 'Y', $input_source, $date, 'MASTER  ');
    }
}
