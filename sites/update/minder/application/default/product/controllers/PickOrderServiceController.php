<?php

class PickOrderServiceController extends Minder_Controller_Action
{
    public function recalculateBillingInformationAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $response    = new Minder_JSResponse();
        $request     = $this->getRequest();
        $pickOrderId = $request->getParam('PICK_ORDER');

        switch (strtolower($pickOrderId)) {
            case 'new':
                $tmpPickOrder = $this->minder->newPickOrder();
                break;
            default:
                if (false === ($tmpPickOrder = $this->minder->getPickOrder($pickOrderId))) {
                    $response->errors[] = 'PICK_ORDER #' . $pickOrderId . ' not found.';
                    echo json_encode($response);
                    return;
                }
        }

        $tmpPickOrder->update($request->getParams());
        $tmpPickOrder->recalculateBillingInformation();

        $response->pickOrder = $tmpPickOrder;

        echo json_encode($response);
    }
}
