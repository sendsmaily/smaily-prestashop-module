<?php

class SmailyCronjobModuleFrontController extends ModuleFrontController{
	
	public function initContent(){
		parent::initContent();
		
		$helperData=new SmailyModel();
		
		if ( $helperData->isEnabled() ){
			
		
			$customers = $this->getList();
		
			$response = $helperData->cronSubscribeAll($customers);
			echo '<pre>';
			print_r($response );
			
			
		
		}
	
		exit;
	}
	
	private function getList($limit=500){
		
		$list = [];
		$exists_ids = [];

		// get subscribers
		$customers = Db::getInstance()->executeS("Select * from "._DB_PREFIX_."customer LIMIT 0,$limit");
		
		foreach($customers as $c){
				
			$customer_id = (int)$c['id_customer'];
			
				// create list
				$list[] = [
					'email'=>$c['email'],
					'name' => ucfirst($c['firstname']).' '.ucfirst($c['lastname']),
					'subscription_type' => 'Customer',
					'customer_group' => 'Customer',
					'customer_id' => $customer_id,
					'prefix' =>'',
					'firstname' => ucfirst($c['firstname']),
					'lastname' =>  ucfirst($c['lastname']),
					'gender' =>  $c['id_gender']== 2 ? "Female" : "Male",
					'birthday' => !empty($c['birthday']) ? $c['birthday']." 00:00" : '',
					'website' => '',
					'store' => $c['id_shop']
				];
			
		}
		
        return $list;
    }
	
}