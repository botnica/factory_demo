<?php
require_once 'db.php';

function validate($conn){
	if(checkNumericList("tags")){
		return array("res" => FALSE, "msg" => "Tag value '". $_GET["tags"] ."' is not valid");
	}
	if(checkNumeric("category") && (getValue("category")!="!NULL") && (getValue("category")!="NULL")){
		return array("res" => FALSE, "msg" => "Category value '". $_GET["category"] ."' is not valid");
	}
	if(checkNumeric("diff_time")){
		return array("res" => FALSE, "msg" => "Diff_time value '". $_GET["diff_time"] ."' is not valid");
	}
	if(empty($_GET["lang"])){
		return array("res" => FALSE, "msg" => "Language not set");
	} elseif (checkLanguage($_GET["lang"], $conn)) {
		return array("res" => FALSE, "msg" => "Language value '". $_GET["lang"] ."' not exists");
	}
	if(empty($_GET["per_page"]) && !empty($_GET["page"])){
		return array("res" => FALSE, "msg" => "Per page is not defined");
	}
	if(checkNumeric("per_page")){
		return array("res" => FALSE, "msg" => "Per_page value '". $_GET["per_page"] ."' is not valid");
	}
	if(checkNumeric("page")){
		return array("res" => FALSE, "msg" => "Page value '". $_GET["page"] ."' is not valid");
	}

	return  array("res" => TRUE, "msg" => "OK");
}



function checkNumericList($param){
	/* check if csv string contains any non-numeric value */
	if(isset($_GET[$param])){
		$val = $_GET[$param];
		(strpos($val, ",")!==false) ? $list = explode(",", $val) : $list[] = $val;
		foreach($list as $l){
			if(!filter_var($l, FILTER_VALIDATE_INT) || (integer)$l<=0){
				return TRUE;
			}
		}
	}
	return FALSE;
}




function checkNumeric($param){
	if(isset($_GET[$param])){
		$val = $_GET[$param];
		if(!filter_var($val, FILTER_VALIDATE_INT) || (integer)$val<=0){
			return TRUE;
		}
	}
	return FALSE;
}




function checkLanguage($lang, $conn){
	$sql = "select id from languages where id = '". $lang ."'";
	$result = $conn->query($sql);
	(($result->num_rows) > 0)? $res=FALSE : $res=TRUE;
	return $res;
}




function getLanguage(){
	if (!($lang = getValue('lang'))) {
		$lang = "en";
	}
	return $lang;
}




function getValue($tag){
  if (isset($_GET[$tag]) && !empty($_GET[$tag])){
  	return $_GET[$tag];
  }
  return FALSE;
}



function searchDishes($conn) {
	/*
	build a search query, returns dishes id on search criteria
	*/
	$sql = "
	select d.id as DISH_ID
	from dishes d
	inner join t_dishes t on d.label_id = t.label_id";

	if(getValue('category') && getValue('category') != "NULL"){
		$sql = $sql . "
		inner join categories c on d.category_id = c.id";
	}
	if(getValue('tags')){
		$sql = $sql . "
		left join dish_tags dt on d.id = dt.dish_id";
	}

	$sql = $sql . "
	where t.language_id = '". getLanguage() ."'";

	if ($tags = getValue("tags")){
		$tagsCount = count(explode(",", $tags));
		$sql = $sql . "
		and dt.tag_id in (". $tags . ")";
	}

	if($category = getValue('category')){
		if ($category=="!NULL"){
			$sql = $sql . "
			and d.category_id IS NOT NULL";
		} else if ($category=="NULL"){
			$sql = $sql . "
			and d.category_id IS NULL";
		} else {
			$sql = $sql . "
			and d.category_id = ". $category ."";
		}
	}

	if($diff_time = getValue('diff_time')){
		$sql = $sql . "
		and unix_timestamp(d.created) > " . $diff_time;
	}  else {
		$sql = $sql . "
		and d.status = 'created'";
	}
	if (getValue("tags")){
		$sql = $sql . "
		group by d.id
		having count(*) = " . $tagsCount;
	}
	$sql = $sql . "
	order by d.created asc";

	// echo $sql . ";\n";

	$items = array();
	if($result = $conn->query($sql)){
		while($row = $result->fetch_assoc()){
			$items[] = $row["DISH_ID"];
		}
		return $items;
	}
	return NULL;
}



function getDish($item_id, $conn) {
	/*
	returns dish_id data
	*/

	$options = displayOptions();
	$dish = array();

	$sql = "
					select d.id as id, t.label as label, t.description as description, d.status as status, d.category_id as category, unix_timestamp(d.created) created
	        from dishes d
	        inner join t_dishes t on d.label_id = t.label_id
	        where t.language_id = '". getLanguage() . "'
					and d.id = ". $item_id;

	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
  $dish = $row;
  unset($dish["category"]);


  if($options["category"] && isset($row["category"])){
		$sql = "
						select c.id as id, t.label as label, c.slug as slug
						from categories c
						inner join t_categories t on c.label_id = t.label_id
						where t.language_id = '". getLanguage() . "'
						and c.id = ". $row["category"];

		if ($result = $conn->query($sql)) {
			$row = $result->fetch_assoc();
			$dish["category"] = $row;
		}
	}

	if($options["ingredients"]){

		$sql = "
						select i.id as id, t.label as label, i.slug as slug
						from ingredients i
						inner join t_ingredients t on i.label_id = t.label_id
						inner join dish_ingredients di on i.id = di.ingredient_id
						where t.language_id = '". getLanguage() . "'
						and di.dish_id = ". $item_id;

		if ($result = $conn->query($sql)) {

			$ingredients = array();
			while($row = $result->fetch_assoc()){
				$ingredients[]=$row;
			}

			$dish["ingredients"] = $ingredients;
		}
	}

	if($options["tags"]){

		$sql = "select tt.id as id, t.label as label, tt.slug as slug
						from tags tt
						inner join t_tags t on tt.label_id = t.label_id
						inner join dish_tags dt on tt.id = dt.tag_id
						where t.language_id = '". getLanguage() . "'
						and dt.dish_id = ". $item_id;

		if ($result = $conn->query($sql)) {

			$tags = array();
			while($row = $result->fetch_assoc()){
				$tags[]=$row;
			}

			$dish["tags"] = $tags;
		}
	}

return $dish;

}


function displayOptions(){
	/* return options from the GET parameter 'with' */
	$options = array("category" => FALSE, "ingredients" => FALSE, "tags" => FALSE);

	if ($val = getValue("with")){
		if(strpos($val, "category") !== false) { $options["category"] = TRUE; }
		if(strpos($val, "ingredients")!==false) { $options["ingredients"] = TRUE; }
		if(strpos($val, "tags")!==false) { $options["tags"] = TRUE; }
	}
	return $options;
}


function createResponse ($itemList, $conn) {
/*
check if paging is requested as _GET variable
*/
	$totalItems = count($itemList);
  if ($itemsPerPage = (integer)getValue('per_page')){
    $totalPages = ceil($totalItems/$itemsPerPage);
    if($currentPage = (integer)getValue('page')){
      $firstItem = ($currentPage - 1) * $itemsPerPage;
    } else {
      $firstItem = 0;
      $currentPage = 1;
    }
  } else {
    $firstItem = 0;
    $currentPage = 1;
    $totalPages = 1;
    $itemsPerPage = $totalItems;
  }
  if ($itemsPerPage + $firstItem >= $totalItems){
    $lastItem = $totalItems - 1;
  } else {
    $lastItem = $firstItem + $itemsPerPage - 1;
  }

	$response["meta"]= array("currentPage" => $currentPage, "totalItems" => $totalItems, "itemsPerPage" => $itemsPerPage, "totalPages" => $totalPages);

	/* Fetch Dishes data */
	$dishes = array();
	for ($i=$firstItem; $i<=$lastItem; $i++ ){
		$dishes[] = getDish($itemList[$i], $conn);
	}

	$response["data"]=$dishes;

  /* Create paging links  */
  /* fetching the url base string */
  $url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];
  /* set language from $_GET  */
  if($val = getValue('lang')){
    $url = $url . "?lang=" . $val;
  }

  /* restore the rest of variables from $_GET */
  $controls = array('category','tags', 'with', 'per_page', 'diff_time');
  foreach($controls as $ctr){
    if($val = getValue($ctr)){
      $url = $url ."&". $ctr ."=" . $val;
    }
  }

  /* setting URLs for links in links section of the response */
  if ($currentPage<$totalPages){
    $nextUrl = $url . "&page=". ((integer)$currentPage + 1);
  } else {
    $nextUrl = "NULL";
  }
  if ($currentPage > 1){
    $previousUrl = $url . "&page=". ((integer)$currentPage - 1);
  } else {
    $previousUrl = "NULL";
  }
  $currentUrl = $url . "&page=". $currentPage;

	$response["links"] = array("self" => $currentUrl, "previous" => $previousUrl, "next" => $nextUrl);


  return $response;
}

?>
