<?php
/**
 * @author     Mudrahel.com
 * @category   Application\Parser
 * @copyright  2017
 */
set_time_limit(900);
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
	public function download_picture($url) {
		$ch = curl_init($url);
		$dir = '../wp-content/uploads/';
		$filename = basename($url);
		$path = $dir . $filename;
		$fp = fopen($path, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		if(is_resource($ch)){
			fclose($ch);
		}
		return $path;
	}	
}

$Parser = new Parser;
$categoryData = $Parser->category('http://price.ua/catc6t1.html');
array_splice($categoryData, -2);
foreach ($categoryData as $category) {
	$category = 'http://price.ua/catc52t1.html';
	$Products = $Parser->products($category);
	foreach ($Products as $product) {
		echo 'Start parse ----> '.$product;
	
		// ----------- Picture | echo $Parser->get_picture($product).'<br>';
		// ---------- Category | echo $Parser->get_category($product).'<br>';
		// ----------- Price | echo $Parser->get_price($product).'грн. <br>';
		// ----------- Brand | echo $Parser->get_brand($product).' ';
		// ----------- Model | echo $Parser->get_model($product).'<br>';
		// ----------- Description | echo $Parser->get_description($product);
	
		// array symbols for str_replace
		$repl = array(',', '/', ' ', '--');

		$picture_url = $Parser->get_picture($product);
		$picture_url = $Parser->download_picture($picture_url);
		$picture_url = basename($picture_url);
		$cur_date = date('Y-m-d h:i:s', time());
		
		// ----------------------------------------------------------------------
		// INSERT PRODUCT DATA
		$sql = "INSERT INTO wp_posts (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('1', '{$cur_date}', '{$cur_date}', '<div id=product-description>".DB::DQ($Parser->get_description($product))."</table></div>', '".$Parser->get_brand($product)." ".$Parser->get_model($product)."', '".$Parser->get_brand($product)." ".$Parser->get_model($product)."', 'publish', 'open', 'closed','', '".str_replace(' ','-',$Parser->get_brand($product))."-".str_replace(' ','-',$Parser->get_model($product))."','','', '{$cur_date}', '{$cur_date}', '', '0', 'http://localhost/middle/?post_type=product#038;p=".rand(10,100000)."', '0', 'product', '', '0')";
		
		DB::Execute($sql);
		$ID = DB::InsertID();
		
		// ---------------------------------------------------------------------
		// INSERT PRODUCT IMAGE
		$image = pathinfo($Parser->get_picture($product));
		$sql = "INSERT INTO wp_posts (`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ('1', '{$cur_date}', '{$cur_date}', '', '".$image['filename']."', '', 'inherit', 'open', 'closed', '', '".$image['filename']."', '', '', '{$cur_date}', '{$cur_date}', '', '{$ID}', 'http://localhost/middle/wp-content/uploads/".$picture_url."', '0', 'attachment', 'image/jpeg', '0')";
		
		DB::Execute($sql);
		$ID_picture = DB::InsertID();

		// ---------------------------------------------------------------------
		// INSERT BRAND PRODUCT
		$brand_slug = str_replace($repl,'-',$Parser->get_brand($product));
		$brand_slug = urlencode($brand_slug);
		DB::Execute("INSERT INTO wp_terms (`name`,`slug`,`term_group`) VALUES ('{$Parser->get_brand($product)}', '{$brand_slug}', '0')");
		$brand_term_id = DB::InsertID();
		DB::Execute("INSERT INTO wp_term_taxonomy (`term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES ('{$brand_term_id}', 'product_brand', '', '0', '0')");
		$term_taxonomy_id = DB::InsertID();
		DB::Execute("INSERT INTO wp_term_relationships (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('{$ID}', '{$term_taxonomy_id}', '0')");
		
		// ---------------------------------------------------------------------
		// INSERT CATEGORY PRODUCT
		$category_slug = str_replace($repl,'-',$Parser->get_category($product));
		$category_slug = urlencode($category_slug);
		DB::Execute("INSERT INTO wp_terms (`name`,`slug`,`term_group`) VALUES ('{$Parser->get_category($product)}', '{$category_slug}', '0')");
		$brand_term_id = DB::InsertID();
		DB::Execute("INSERT INTO wp_term_taxonomy (`term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES ('{$brand_term_id}', 'product_cat', '', '0', '0')");
		$term_taxonomy_id = DB::InsertID();
		DB::Execute("INSERT INTO wp_term_relationships (`object_id`, `term_taxonomy_id`, `term_order`) VALUES ('{$ID}', '{$term_taxonomy_id}', '0')");

		// --------------------------------------------------------------------
		// INSERT PRODUCT PRICE
		$sql = "INSERT INTO wp_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('".$ID."', '_regular_price', '".$Parser->get_price($product)."')";
		DB::Execute($sql);
		$sql = "INSERT INTO wp_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('".$ID."', '_price', '".$Parser->get_price($product)."')";
		DB::Execute($sql);
		$sql = "INSERT INTO wp_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('".$ID."', '_stock_status', 'instock')";
		DB::Execute($sql);
		$sql = "INSERT INTO wp_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('".$ID_picture."', '_wp_attached_file', '".$picture_url."')";
		DB::Execute($sql);
		$sql = "INSERT INTO wp_postmeta (`post_id`, `meta_key`, `meta_value`) VALUES ('".$ID."', '_thumbnail_id', '".$ID_picture."')";
		DB::Execute($sql);

		// ---------------------------------------------------------------------
		// LOGS FOR CHECK STATUS PRODUCTS
		echo $log = '<br>Product: '.$product.' ------> <span style="color:green;font-size:16px;font-weight:700;"> Was successfully added to database!</span>';
		echo '<hr>';
	}
}