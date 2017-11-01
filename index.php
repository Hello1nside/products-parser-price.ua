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

	public function product($category) {
		$data = $this->curl_get_contents($category);
		//preg_match_all('/<a\s[^>]*class=\"\model-name clearer-block"\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>/siU', $data, $data);
		preg_match_all('#<a\s[^>]*href=\"([^\"]*)\"[^>]*>(.*)<\/a>#siU', $data, $data);
		return $data[1];
	}

}

$Parser = new Parser;

$categoryData = $Parser->category('http://price.ua/catc6t1.html');

array_splice($categoryData, -2);

foreach ($categoryData as $category) {
	echo '<pre>';
	//echo $category;
	echo '</pre>';


}

	$productData = $Parser->product('http://price.ua/catc52t1.html');
	
	var_dump($productData);