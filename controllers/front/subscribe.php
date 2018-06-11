<?php

class SmailySubscribeModuleFrontController extends ModuleFrontController{
	
	public function initContent(){
		parent::initContent();
		
		$helperData=new SmailyModel();
		
		$autoresponder_id = $helperData->getConfig('autoresponder');
		
		$name = (string)@$_REQUEST['name'];
		
		$Context = Context::getContext();
		$id_shop = (int)$Context->shop->id;	
		
		$customer = $Context->customer;
		
		$extra = ['name'=>$name,'subscription_type' => 'Subscriber','customer_group'=>'Guest','store'=>$id_shop ];
		
		if( $customer->isLogged() ){
			$dob = $customer->birthday;
			
			// get custmer data
			$extra['customer_id'] = $customer->id;
			$extra['customer_group'] = 'Customer';
			$extra['prefix'] = '';
			$extra['gender'] = $customer->id_gender == 2 ? 'Female' : 'Male';
			$extra['birthday'] = $dob;
		}
		
		// split name field into firstname & lastname
		if( !empty($name) ){
			$name = explode(' ',trim($name));
			 $extra['firstname'] = ucfirst($name[0]); unset($name[0]);
			 if( !empty($name) )
				 $extra['lastname'] = ucfirst(implode(" ",$name));					 
		}
						
		$response = $helperData->subscribeAutoresponder($autoresponder_id,$_REQUEST['email'],$extra);
		header("Content-Type: application/json");
		echo json_encode($response);
		exit;
	}
}