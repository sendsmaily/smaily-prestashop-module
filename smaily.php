<?php   
if (!defined('_PS_VERSION_')) {
  exit;
}

class Smaily extends Module
{
	public function __construct()
	{
	$this->name = 'smaily';
	$this->tab = 'front_office_features';
	$this->version = '1.0.0';
	$this->author = '';
	$this->need_instance = 0;
	$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_); 
	$this->bootstrap = true;

	parent::__construct();

	$this->displayName = $this->l('Smaily email marketing and automation');
	$this->description = $this->l('Smaily RSS Feed Module');
	$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('MYMODULE_NAME')) {
		  $this->warning = $this->l('No name provided');
		}
	}
	public function installTab($parent_class, $class_name, $name) 
	{
		  $tab = new Tab();
		  $tab->name[$this->context->language->id] = $name; 
		  $tab->class_name = $class_name;
		  $tab->id_parent = (int) Tab::getIdFromClassName($parent_class);
		  $tab->module = $this->name;
		  return $tab->add();
	}
	public function install()
	{
		$this->createTableSmaily();
		
		  return (parent::install() &&
				$this->registerHook('backOfficeHeader') &&
				$this->registerHook('displayTopColumn') &&
				$this->registerHook('header') &&
				$this->registerHook('footer') &&
				$this->installTab('AdminAdmin', 'AdminSmaily', 'Smaily email marketing and automation') &&
				Configuration::updateValue('MODULENAME', "smaily")
		  );
		  
	}
	public function uninstall() {
		$this->dropTables();
		$tab = new Tab((int)Tab::getIdFromClassName('AdminSmaily'));
		$tab->delete();
	
		if (!parent::uninstall())
			return false;
		return true;
	}
	public function hookDisplayLeftColumn($params)
	{
		return $this->display(__FILE__, 'blocknewsletter.tpl');
	}

	public function hookFooter($params)
	{
		return $this->hookDisplayLeftColumn($params);
	}
	public function createTableSmaily()
	{
		$smaily = 
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'smaily` (
		`id` int(11) NOT NULL AUTO_INCREMENT ,
		`enable` varchar(10) DEFAULT NULL,
		`subdomain` varchar(255) DEFAULT NULL,
		`username` varchar(255) DEFAULT NULL,
		`password` varchar(255) DEFAULT NULL,
		`autoresponder` varchar(255) DEFAULT NULL,
		`syncronize_additional` text DEFAULT NULL,
		`rss_feed` varchar(255) DEFAULT NULL,
		`syncronize` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1';		
		
		Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($smaily);
		
		$sql = "INSERT INTO `"._DB_PREFIX_."smaily` (`enable`) VALUES('off')";
		Db::getInstance()->execute($sql);
			
		return Db::getInstance()->Insert_ID();	
	}
	
	public function dropTables()
{
		$sql = 'DROP TABLE
		`'._DB_PREFIX_.'smaily`';
		
		return Db::getInstance()->execute($sql);
	}
	
		
}

class SmailyModel
{
	public function subscribe($email,$data=[],$update = 0){
		$address = [
			'email'=>$_POST['email'],
			'is_unsubscribed' => $update
		];		
		if( !empty($data) ){
			 $fields = explode(",",trim($this->getConfig('syncronize_additional')));
		
			 foreach($data as $field => $val){
				 if( in_array($field,$fields) || $field == 'name' ){
					 $address[$field] = trim($val); 	
				 }
			 }
		}
		
		$response = $this->callApi('contact',$address,'POST');
		
		return $response;
	}
	public function subscribeAutoresponder($aid,$email,$data=[]){
		
		$address = [
			'email'=>$email,
		];
		
		if( !empty($data) ){
			$fields = explode(",",trim($this->getConfig('syncronize_additional')));
			foreach($data as $field => $val){
				if( in_array($field,$fields) || $field == 'name' ){
					$address[$field] = trim($val); 	
				}
			}
		}
		
		$post  = [
			'autoresponder' => $aid,
			'addresses' => [$address],
		];
	
		$response = $this->callApi('autoresponder',$post,'POST');
		
		return $response;
	}
		
		
	
	public function updateSettings($formData){
		if(isset($_POST['smailey_submit'])){
				$enable=$formData['enable'];
				if($enable != "on"){
					$enable="off";
				}	
				$subdomain=$formData['subdomain'];
				$username=$formData['username'];
				$password=$formData['password'];
				$autoresponder=$formData['autoresponder'];
				$sync_aditional=implode(',',$formData['syncronize_additional']);	
				$rss_feed=$formData['rss_feed'];
				
				$sql="UPDATE `"._DB_PREFIX_."smaily` SET `enable` = '$enable', `subdomain` = '$subdomain',`username` = '$username',`password` = '$password',`autoresponder` = '$autoresponder',`syncronize_additional` = '$sync_aditional',`rss_feed` = '$rss_feed'  WHERE `id` = 1 ";			
				Db::getInstance()->execute($sql);
		}
	}
	public function getResult(){	
		$result="SELECT * FROM `"._DB_PREFIX_."smaily`";
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($result);
	}
	public function getConfig($field){
		$result = $this->getResult();
		return @$result[$field];
	}
	public function rss_token(){
		$result = $this->getResult();
		return $result['rss_feed'];
	}
	public function isEnabled(){
		$result =   $this->getResult();
		return ($result['enable'] == 'on')?true:false;
	}
	
	public function getSubdomain(){
		$configData =  $this->getResult();
		$subdomain = $configData['subdomain'];
		$host = parse_url($subdomain);
		$subdomain = explode('.',$host['host']);
		return $subdomain[0];
	}
	
	public function cronSubscribeAll($list){

		$data = [];
		$fields = explode(",",trim($this->getConfig('syncronize_additional')));
		
		foreach($list as $row){
		
			$_data = [
				'email'=>$row['email'],
				'is_unsubscribed' => 0
			];
		
			foreach($row as $field => $val){
				if( in_array($field,$fields) ){
					$_data[$field] = trim($val); 	
				}
			}
		
			$data[] = $_data;
		}

		$response = $this->callApi('contact',$data,'POST');
		
		return $response;
	}
	public function getAutoresponders(){
		
		$_list = $this->callApi('autoresponder',['page'=>1,'limit'=>100,'status'=>['ACTIVE']]);
		$list = [];
		foreach($_list as $r){
			if( !empty($r['id']) && !empty($r['name']) )
			$list[$r['id']] = trim($r['name']);
		}
		return $list;
	}
	public function callApi($endpoint,$data,$method='GET'){
	
		$configData =  $this->getResult();
		$subdomain = $this->getSubdomain();
		$username = trim($configData['username']);
		$password = trim($configData['password']);
		$apiUrl = "https://".$subdomain.".sendsmaily.net/api/".trim($endpoint,'/').".php";
		$data = http_build_query($data);
		if( $method == 'GET' )
		$apiUrl = $apiUrl.'?'.$data;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if( $method == 'POST' ){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = json_decode(@curl_exec($ch),true);
		$error = false;
		if( curl_errno($ch) )
			$result = ["code"=>0,"message"=>curl_error($ch)];
		curl_close($ch);
		return $result;
	}
	
	public function getLatestProducts(){		
		return $products = Product::getProducts(array(0,1,2), 0, 1000, 'id_product', 'asc');
	}	
	
	public function generateRssFeed(){
		
		$products = $this->getLatestProducts();			
		$baseUrl = Tools::getHttpHost(true).__PS_BASE_URI__;
		$url = $baseUrl;
		$currencysymbol = "$";
		$items= [];
		
		$rss ='<?xml version="1.0" encoding="utf-8"?><rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0"><channel><title>Store</title><link>'.$baseUrl.'</link><description>Product Feed</description><lastBuildDate>'.date("D, d M Y H:i:s").'</lastBuildDate>';
		
		foreach($products as $product){
			$link = new Link();
			$product_url = $link->getProductLink((int)$product['id_product']);
			$image = Product::getCover((int)$product['id_product']);
			$image = new Image($image['id_image']);
			$product_photo = $url.$image->getExistingImgPath().".jpg";
			
			$price = $product['price'];
			$splcPrice = $product['wholesale_price'];
			$description = $product['description'];
			$date_add = $product['date_add'];
			$name = $product['name'];
			$discount = 0;
			if( $splcPrice  == 0 )
			$splcPrice = $price;
			
			if( $splcPrice < $price && $price > 0 )
			$discount = ceil(($price-$splcPrice)/$price*100);
			
			$price ='$'.number_format($price,2,'.',',');
			$splcPrice = '$'.number_format($splcPrice,2,'.',',');
			
			$productUrl ='';
			
			$createTime = strtotime($prod->post_date);
			$price_fields ='';
			if( $discount > 0 ){
				$price_fields = '<smly:old_price>'.$price.'</smly:old_price><smly:discount>-'.$discount.'%</smly:discount>';	
			}
			
			$rss .= '<item>
			<title>'.$name.'</title>
			<link>'.$productUrl.'</link>
			<guid isPermaLink="True">'.$url.'</guid>
			<pubDate>'.date("D, d M Y H:i:s",$date_add).'</pubDate>
			<description>'.htmlentities($product['description_short']).'</description>
			<enclosure url="'.$product_photo.'" />
			<smly:price>'.$splcPrice.'</smly:price>'.$price_fields.'
			</item>';
		}
		$rss .='</channel></rss>';
		header('Content-Type: application/xml');
		echo $rss;
		
	}
}
