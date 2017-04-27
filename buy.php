<!DOCTYPE html>
<html>
<head>
	<title>Buy Products</title>
	<style>
		* {
			font-family: Arial, Helvetica, sans-serif;
		}
		fieldset {
			padding: 12px 25px;
			padding-bottom: 20px;
		}
		select {
			font-size: 14px;
		}
		td {
			/*border-bottom: 1px solid #ccc;*/
			padding: 5px 20px;
		}
		tr:nth-child(even) {
			background-color: #f5f5f5;
		}
		tr:hover {
			background-color: #f9f9f9;
		}
		.btn {
			padding: 5px;
			color: white;
			border: 0;
			border-radius: 5px;
			font-size: 16px;
			cursor: pointer;
		}
		.buy {
			width: 100px;
			background-color: green;
		}
		.delete {
			background-color: red;
		}
		.empty {
			background-color: gold;
		}
		.search {
			margin-left: 5px;
			background-color: darkblue;
		}
		.offers {
			background-color: blue;
			text-decoration: none;
		}
		.product_name {
			/*font-weight: bold;*/
		}
		.description {
			font-size: 14px;
		}
		legend {
			font-size: 18px;
			margin-left: 10px;
			padding-left: 10px;
			padding-right: 10px;
		}
		#shopping {
			font-size: 18px;
		}
	</style>
</head>
<body>
<p id="shopping">Shopping Basket</p>
<table cellspacing="0">
<tbody>
<?php

	session_start();

	/* Empty cart */
	if (isset($_GET['clear'])){
		session_unset();
	}

	$_SESSION['total'] = 0;

	/* Buy items */
	if (isset($_GET['buy'])) {

		if (empty($_SESSION['count'])) {
			$_SESSION['count'] = 1;
		} else {
			$_SESSION['count']++;
		}

		$index = $_SESSION['count'] - 1;

		$exists = false;

		for ($i = 0; $i < $_SESSION['count']; $i++) {
			if(isset($_SESSION["product_id"][$i])){
				if ($_GET['buy'] == $_SESSION["product_id"][$i]){
					$exists = true;
					$_SESSION['count']--;
					break;
				}
			}
		}

		/* Check if item is already in cart & add if not */
		if(!$exists){
			$_SESSION["product_id"][$index] = $_GET['buy'];
			$buy_url = "http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&productId=";
			$buy_url .= $_GET['buy'];
			$buy_query_str = file_get_contents($buy_url);
			$buy_query = new SimpleXMLElement($buy_query_str);
			$_SESSION["name"][$index] = (string) $buy_query->categories->category->items->product->name;
			$_SESSION["minPrice"][$index] = floatval((string) $buy_query->categories->category->items->product->minPrice);
			$_SESSION["image"][$index] = (string) $buy_query->categories->category->items->product->images->image[0]->sourceURL;
			$_SESSION["productOffersURL"][$index] = (string) $buy_query->categories->category->items->product->productOffersURL;
		}

	}

	/* Delete from cart */
	if(isset($_GET['delete'])) {
		$exists = false;
		$index_to_delete;
		for ($i = 0; $i < $_SESSION['count']; $i++) {
			if(isset($_SESSION["product_id"][$i])){
				if ($_GET['delete'] == $_SESSION["product_id"][$i]){
					$exists = true;
					unset($_SESSION["product_id"][$i]);
					unset($_SESSION["name"][$i]);
					unset($_SESSION["image"][$i]);
					unset($_SESSION["minPrice"][$i]);
					unset($_SESSION["productOffersURL"][$i]);
					break;
				}
			}
		}
		if($exists){

		}
	}

	/* Show items in cart */
	if(isset($_SESSION['count'])){
		for ($i = 0; $i < $_SESSION['count']; $i++) {
			if(isset($_SESSION["product_id"][$i])){
				$_SESSION["total"] += $_SESSION["minPrice"][$i];
				echo '<tr>';
				echo '<td><img src="' , $_SESSION["image"][$i] , '" /></td>';
				echo '<td><p>' , $_SESSION["name"][$i] , '</p></td>';
				echo '<td>$' , $_SESSION["minPrice"][$i] , '</td>';
				echo '<td><a class="btn offers" href="' , $_SESSION["productOffersURL"][$i] , '" target="_blank()">View Offers</a></td>';
				echo '<td><form action="buy.php" method="GET"><input name="delete" value="' , $_SESSION["product_id"][$i] , '" type="hidden" /><input class="btn delete" value="Delete (X)" type="submit" /></form></td>';
				echo '</tr>';
			}
		}
	}

?>
</tbody>
</table>

<p><strong>Total:</strong> $<?php echo $_SESSION["total"]; ?></p>

<p></p>

<form action="buy.php" method="GET">
<input name="clear" value="1" type="hidden" />
<input class="btn empty" value="Empty Basket" type="submit" />
</form>

<p></p>

<?php

	$userAgent = $_SERVER['HTTP_USER_AGENT'];
	$ipAddress = $_SERVER['REMOTE_ADDR'];
	error_reporting(E_ALL);
	ini_set('display_errors','On');

	echo '<form action="buy.php" method="GET">';
	echo '<fieldset><legend>Find products:</legend>';
	echo '<label>Category: <select name="category"><option value="72" selected="selected">Computers</option>';

	/* Generate search categories */
	$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72');
	$xml = new SimpleXMLElement($xmlstr);

	foreach ($xml->category->categories->category as $category) {
		echo '<option value="' , $category['id'] , '">' , $category->name , '</option>';
		if ((string)$category->contentType == "categories"){
			echo '<optgroup label="', $category->name , '">';
			$url = "http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=";
			$url .= $category['id'];
			$subcategorystr = file_get_contents($url);
			$subcategory = new SimpleXMLElement($subcategorystr);
			foreach ($subcategory->category->categories->category as $category) {
		   		echo '<option value="' , $category['id'] , '">' , $category->name , '</option>';
		   	}
		   	echo '</optgroup>';
		}
	}
	echo '</select></label><label> Search keywords: <input name="search" type="text" /><label><input class="btn search" value="Search" type="submit" /></label></label></fieldset>';
	echo '</form>';

	if (isset($_GET['search'])){
		$search_url = "http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&categoryId=";
		$search_url .= $_GET["category"];
		$search_url .= "&keyword=";
		$search_url .= urlencode ( $_GET["search"] );
		$search_url .= "&numItems=20";
		$search_result_str = file_get_contents($search_url);
		$search_result = new SimpleXMLElement($search_result_str);

		echo '<table cellspacing="0"><tbody>';
		foreach ($search_result->categories->category->items->product as $product) {
			echo '<tr>';
			echo '<td><img src="' , $product->images->image->sourceURL , '" /></td>';
			echo '<td><p class="product_name">' , $product->name , '</p><p class="description">' , $product->fullDescription , '</p></td>';
			echo '<td>$' , $product->minPrice , '</td>';
			echo '<td><form action="buy.php" method="GET"><input name="buy" value="' , $product['id'] , '" type="hidden" /><input class="btn buy" value="Buy" type="submit" /></form></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

	}

?>

</body>
</html>
