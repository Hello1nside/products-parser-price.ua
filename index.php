<?php
/**
 * @author     Mudrahel.com
 * @category   Application\Parser
 * @copyright  2017
 */

error_reporting(E_ALL);
ini_set('display_errors', true);

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
		
		return file_get_contents($url);
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
		echo $Parser->get_picture($product);
		// ---------- Category
		echo $Parser->get_category($product);
		// ----------- Price
		echo $Parser->get_price($product);
		
		// ----------- Brand
		//preg_match_all('~<span\sitemprop=\"brand\">(.*)</span><span\sitemprop=\"model\">(.*)</span>~m', $data, $brand);
		//var_dump($brand[1]);

		// ----------- Description
		//preg_match_all('~<table\s[^>]*\sclass=\"description\">(.*)</table>~m', $data, $description);
		//var_dump($description);

		?><hr><?php
	}
//}





/*
$productData = $Parser->product_data('http://price.ua/catc52t1.html');
//var_dump($productData);
	$i = 1;
foreach ($productData as $product) {
	echo '<pre>';
	//echo $i.' = '.$product;
	echo '</pre>';
	$i++;
}

$productPrice = $Parser->product_prices('http://price.ua/catc52t1.html');
	
	
foreach ($productPrice as $price) {
	echo '<pre>';
	//echo $b.' = '.$price;
	echo '</pre>';
	$b++;	
}
*/