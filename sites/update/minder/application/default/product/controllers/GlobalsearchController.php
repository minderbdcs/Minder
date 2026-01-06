<?php

class GlobalsearchController extends Minder_Controller_Action
{
	public function indexajaxAction()
	{
		$scanned = trim($this->getRequest()->getParam('barcode'));
		$jsonObject = new stdClass();
		
		$type = trim($this->getRequest()->getParam('type', null));


		if (substr($scanned, 0, 4)==']C0/') {
			$trn_prefix = substr($scanned, 3, 2);
			$trn = $this->minder->getTrn($trn_prefix);
			
			if ($trn!=null) {
				$id = substr($scanned, 5);
				//var_dump($trn_name); die();
				$trn_name = 'Transaction_'.$trn['TRN_TYPE'].'A';
				
				$transaction               = new $trn_name();
				$transaction->objectId     = $id;
				$ssn = $this->minder->getSsns(array('SSN_ID = ? AND '=>$id));
				
				
				if ($ssn==null) {
					$jsonObject->error = 'Given ID not found.';
									
					die(json_encode($jsonObject));
				}
				
				$transaction->ssnTypeValue = $ssn[0]->items['SSN_TYPE'];
				$result                    = $this->minder->doTransactionResponse($transaction);
				
				if (false == $result) {
					$jsonObject->error = $this->minder->lastError;
				} else {
					$jsonObject->error = $trn['TRN_TYPE'].'A transaction success';	
				}
				
				die(json_encode($jsonObject));	
			}
		}
		
		if($type == null) {
		
			$types = $this->minder->getAllBarcodeScannerParamsByCode(substr($scanned, 0, 3));
			$items = count($types); 
			if ($items>1) {
	
				$name = array();
				foreach ($types as $type) {
					$name[] = $type['DATA_ID'];					
				}
				$jsonObject->menu->name = $name;
				$jsonObject->menu->id = substr($scanned, 3);
				$jsonObject->menu->items = $items;
								
				die(json_encode($jsonObject));
			} elseif($items == 0)  {
				$jsonObject->error = 'Cannot find DATA_ID';
				die(json_encode($jsonObject));
			}
			
		}
		
		try {
			$barcode = new Barcode($scanned, $this->minder, $type);
		} catch (Exception $e) {
			$jsonObject->error = $e->getMessage();
			die(json_encode($jsonObject));
		}
		
		try {
			if (!($this->minder->itemExists($barcode->getId(), $barcode->getType()))) {
				$jsonObject->error = 'Given ID not found.';
			} else {
				$jsonObject->url = $barcode->getRedirectUrl().$barcode->getId();	
			}
		} catch (Exception $e) {
			$jsonObject->error = $e->getMessage();
			
			 die(json_encode($jsonObject));			
		}
		die(json_encode($jsonObject));
	}
}


?>