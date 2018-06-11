<?php
	
	class SmailyModel
	{
		
		public static function createTableSmaily()
		{
			$smaily = 
            'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'smaily` (
			`id` int(11) NOT NULL AUTO_INCREMENT ,
			`enable` varchar(10) DEFAULT NULL,
			`subdomain` varchar(255) DEFAULT NULL,
			`username` varchar(255) DEFAULT NULL,
			`password` varchar(255) DEFAULT NULL,
			`autoresponder` varchar(255) DEFAULT NULL,
			`syncronize_additional` varchar(255) DEFAULT NULL,
			`rss_feed` varchar(255) DEFAULT NULL,
			`syncronize` varchar(255) DEFAULT NULL,
			PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1';		
			
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($smaily);
			
			
		}	
		public static function smailey_install_data(){
			
			$sql = "INSERT INTO `"._DB_PREFIX_."smaily` (`enable`) VALUES('off')";
			Db::getInstance()->execute($sql);
			
			return Db::getInstance()->Insert_ID();
			
		}
		/* public function validateSubscription($email){
		
		$result = "SELECT * FROM "._DB_PREFIX_."newsletter where email='$email' AND status=1";
		if(!empty($result)){
		return true;
		}
		else{
		return false;
		}
		} */
		/* public function saveNewsLetter()
		{			
			$name=$_POST['name'];
			$email=$_POST['email'];
			$sql = "INSERT INTO `"._DB_PREFIX_."newsletter` (`name`,`email`) VALUES('$name','$email')";
			Db::getInstance()->execute($sql);
			Db::getInstance()->Insert_ID();
		} */
		public function subscribe($email,$data=[],$update = 0){
		$address = [
			'email'=>$_POST['email'],
			'is_unsubscribed' => $update
		];
		
		// if( !empty($data) ){
			// $fields = explode(",",trim($this->getGeneralConfig('fields')));
		
			// foreach($data as $field => $val){
				// if( in_array($field,$fields) || $field == 'name' ){
					// $address[$field] = trim($val); 	
				// }
			// }
		// }
		
		$response = $this->callApi('contact',$address,'POST');
		
		return $response;
	}
		
		// public static function AddNewsletterTable(){
			// $sql = "ALTER TABLE `"._DB_PREFIX_."newsletter` ADD `user_id` int(11) NULL AFTER email, ADD `name` VARCHAR(255) NULL AFTER email,ADD `subscription_type` VARCHAR(255) NULL AFTER email,ADD `customer_group` VARCHAR(255) NULL AFTER email,ADD `customer_id` VARCHAR(255) NULL AFTER email,ADD `prefix` VARCHAR(255) NULL AFTER email,ADD `firstname` VARCHAR(255) NULL AFTER email,ADD `lastname` VARCHAR(255) NULL AFTER email,ADD `gender` VARCHAR(255) NULL AFTER email,ADD `birthday` VARCHAR(255) NULL AFTER email,ADD `store` VARCHAR(255) NULL AFTER email,ADD `website` VARCHAR(255) NULL AFTER email,ADD `autoresponder` VARCHAR(255) NULL AFTER email,ADD `status` VARCHAR(255) NULL AFTER active,ADD `created_at` timestamp AFTER active";
			
			// return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
		// }
		// public static function dropNewsletterTable(){
			
			// $sql = "ALTER TABLE `"._DB_PREFIX_."newsletter` DROP `user_id`, DROP `name`,DROP `subscription_type`,DROP `customer_group`,DROP `customer_id`,DROP `prefix`,DROP `firstname`,DROP `lastname`,DROP `gender`,DROP `birthday`,DROP `store`,DROP `website` ,DROP `autoresponder`,DROP `status`,DROP `created_at`";
			
			// return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
		// }
		public static function dropTables()
		{
			$sql = 'DROP TABLE
			`'._DB_PREFIX_.'smaily`';
			
			return Db::getInstance()->execute($sql);
		}
		public static function updateSettings($formData){
			if(isset($_POST['smailey_submit'])){
				$enable=$formData['enable'];
				if($enable != "on"){
					$enable="off";
				}	
				$subdomain=$formData['subdomain'];
				$username=$formData['username'];
				$password=$formData['password'];
				$autoresponder=$formData['autoresponder'];
				$sync_aditional=serialize($formData['syncronize_additional']);	
				$rss_feed=$formData['rss_feed'];
				
				$sql="UPDATE `"._DB_PREFIX_."smaily` SET `enable` = '$enable', `subdomain` = '$subdomain',`username` = '$username',`password` = '$password',`autoresponder` = '$autoresponder',`syncronize_additional` = '$sync_aditional',`rss_feed` = '$rss_feed'  WHERE `id` = 1 ";			
				Db::getInstance()->execute($sql);
			}
		}
		public static function getResult(){	
			$result="SELECT * FROM `"._DB_PREFIX_."smaily`";
			return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($result);
		}
		public function rss_token(){
			$result = self::getResult();
			return $result['rss_feed'];
		}
		public function is_enable(){
			$result =  self::getResult();
			return ($result['enable'] == 'on')?true:false;
		}
		public function getSubdomain(){
			$configData = SmailyModel::getResult();
			$subdomain = $configData['subdomain'];
			$host = parse_url($subdomain);
			$subdomain = explode('.',$host['host']);
			return $subdomain[0];
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
			$configData = SmailyModel::getResult();
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
				$product_photo = $url._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";
				
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
			die;
		}
		
	}
