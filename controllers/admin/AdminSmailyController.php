<?php

class AdminSmailyController extends ModuleAdminController
{

	public function initContent(){
		parent::initContent();
		
		$this->setTemplate('smaily.tpl');
		
		$helper = new SmailyModel();
	
		$helper->updateSettings($_POST);
		$baseUrl = Tools::getHttpHost(true).__PS_BASE_URI__;
		$result=$helper->getResult();
		
		$autoresponder=$helper->getAutoresponders();
		$this->context->smarty->assign(['result' => $result,'autoresponder' => $autoresponder,'base_url' => $baseUrl]);
	}
	
}

