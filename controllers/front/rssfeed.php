<?php

class SmailyRssFeedModuleFrontController extends ModuleFrontController{
	
	public function initContent(){
		parent::initContent();
		
		$helperFront=new SmailyModel();
	
		$rss_token=trim($helperFront->rss_token());
		if( $rss_token == @$_REQUEST['token'] && !empty($_REQUEST['token']) ){
			$helperFront->generateRssFeed();
		}else{ 
			echo '<h1>Access Denied</h1>';
			die;
		}
	}
}	