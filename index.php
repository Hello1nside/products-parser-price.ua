<?php
/**
 * @author     Mudrahel.com
 * @category   Application\Parser
 * @copyright  2017
 */
set_time_limit(30);
error_reporting(E_ALL);
ini_set('display_errors', true);
require_once('db.php');
class Parser {
	public function curl_get_contents($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36');
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
	}
	public function category($url) {
		$data = $this->curl_get_contents($url);
		preg_match_all('/<a\stitle=\"([^\"]*)\"[^>]*href=\"([^\"]*)\">(.*)<\/a>/siU', $data, $data);
		return $data[2];
	}
	public function products($category) {
		$data = $this->curl_get_contents($category);
		//preg_match_all('~<div\sclass=\"product-item  view-list \"\s[^>]*>(.*)</div>~m', $data, $products);
		preg_match_all('~<a\sclass=\"model-name clearer-block ga_card_mdl_title\"\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)</a>~m', $data, $products);
		return $products[1];
	}
	public function get_picture($url) {
		$data = $this->curl_get_contents($url);
		preg_match_all('~<img\sid=\"model-big-photo-img\"\ssrc=\"([^\"]*)\"[^>]*>~m', $data, $picture);
		$pic = implode(' ', $picture[1]);
		return $pic;
	}
	public function get_category($url) {
		$data = $this->curl_get_contents($url);
		preg_match_all('~<span\sclass=\"breadcrumbs-last\"><span\s[^>]*><a\s[^>]*><span\sitemprop=\"title\">(.*)</span></a></span></span>~m', $data, $category);
		$categ = implode(' ',$category[1]);
		return $categ;
	}
	public function get_price($url) {
		$data = $this->curl_get_contents($url);
		preg_match_all('~<div\sclass=\"price-diapazon\"><span>(.*)</span><\/div>~m', $data, $prices);
		$price = implode(' ', $prices[1]);
		$price = substr($price, 0, 11);
		return $price;
	}
	public function get_brand($url) {
		$data = $this->curl_get_contents($url);
		preg_match('~<span itemprop=\"brand\">(.*?)</span>~m', $data, $brand);
		return $brand[1];
	}
	public function get_model($url) {
		$data = $this->curl_get_contents($url);
		preg_match('~<span itemprop=\"model\">(.*?)</span>~m', $data, $model);
		return $model[1];
	}
	public function get_description($url) {
		$data = $this->curl_get_contents($url);
		preg_match('~<a\s+rel=\"nofollow\" onclick=\"(.*?)\" href=\"([^\"]*)\"[^>]*~m', $data, $descr);
		$url_description = substr($descr[1], 11, -1);
		$dat = $this->curl_get_contents($url_description);
		preg_match('/<div class="model-description-section">(.+?)<\/div>/s', $dat, $description);
		
		if(empty($description)) {
			preg_match('/<table cellspacing="0" cellpadding="0" class="description">(.*)<\/table>/s', $dat, $description);
			return $description[1];
		} else {
			return $description[0];
		}
	}
}
$Parser = new Parser;
/*
$categoryData = $Parser->category('http://price.ua/catc6t1.html');
array_splice($categoryData, -2);
foreach ($categoryData as $category) {
*/
	$category = 'http://price.ua/catc52t1.html';
	$Products = $Parser->products($category);
	foreach ($Products as $product) {
		//echo $product;
		//$ProductContent = $Parser->products_content($product);
		//var_dump($ProductContent);
		// ----------- Picture
		echo $Parser->get_picture($product).'<br>';
		// ---------- Category
		echo $Parser->get_category($product).'<br>';
		// ----------- Price
		echo $Parser->get_price($product).'грн. <br>';
		// ----------- Brand
		echo $Parser->get_brand($product).' ';
		// ----------- Model
		echo $Parser->get_model($product).'<br>';
		// ----------- Description
		var_dump($Parser->get_description($product));
		echo '<hr>';
		
		$cur_date = date('Y-m-d h:i:s', time());

		$sql = "INSERT INTO wp_posts (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('1', '{$cur_date}', '{$cur_date}', '".$Parser->get_description($product)."', '".$Parser->get_brand($product)." ".$Parser->get_model($product)."', '".$Parser->get_brand($product)." ".$Parser->get_model($product)."', 'publish', 'open', 'closed','', '".str_replace(' ','-',$Parser->get_brand($product))."-".str_replace(' ','-',$Parser->get_model($product))."','','', '{$cur_date}', '{$cur_date}', '', '0', 'http://localhost/0-testwordpress/?post_type=product#038;p=".rand(10,100000)."', '0', 'product', '', '0')";
		

		DB::Execute($sql);
	}
//}