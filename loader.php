<?php
require_once 'Faker/src/autoload.php';
require_once 'db.php';

define ('INGREDIENTS_COUNT', 103);
define ('TAGS_COUNT', 10);
define ('CATEGORIES_COUNT', 4);
define ('DISHES_COUNT', 38);

$faker = Faker\Factory::create();
$conn = dbConnect();


/*  generates a random length set of unique integers from the range defined */
function generateRandomTuples($rangeMin, $rangeMax, $lengthMin, $lengthMax) {
  $faker = Faker\Factory::create();
  $length = rand($lengthMin, $lengthMax);

  for($j=0; $j<$length; $j++){
      $tuple[] = $faker->unique()->numberBetween($min = $rangeMin, $max = $rangeMax);
  }
  return $tuple;
}


// get languages
$result = $conn->query("select id from languages");
if ($result) {
   while ($row = $result->fetch_assoc()) {
       $lang[]=$row['id'];
   }
}

// load table ingredients
echo "loading INGREDIENTS ...";
for ($i=1; $i<=INGREDIENTS_COUNT; $i++){
  $sql = "insert into ingredients (label_id, slug) values (" . $i . ", 'ingredient_" . $i . "')";
  $conn->query($sql);
  // echo $sql . ";\n";

  // add translations
  foreach ($lang as $value){
  $sql = "insert into t_ingredients (label_id, language_id, label) values (". $i .", '" . $value . "', 'INGR_" . $faker->word . "_" . strtoupper($value) . "')";
  $conn->query($sql);
  // echo $sql . ";\n";
  }
}
echo "done!\n";


// load table tags
echo "loading TAGS ...";
for ($i=1; $i<=TAGS_COUNT; $i++){
  $sql = "insert into tags (label_id, slug) values (" . $i . ", 'tag_" . $i . "')";
  $conn->query($sql);
  // echo $sql . ";\n";

  // add translations
  foreach ($lang as $value){
  $sql = "insert into t_tags (label_id, language_id, label) values (". $i .", '" . $value . "', 'TAG_" . $faker->word . "_" . strtoupper($value) . "')";
  $conn->query($sql);
  // echo $sql . ";\n";
  }
}
echo "done!\n";

// load categories
echo "loading CATEGORIES ...";
for ($i=1; $i<=CATEGORIES_COUNT; $i++){
  $sql = "insert into categories (label_id, slug) values (" . $i . ", 'category_" . $i . "')";
  $conn->query($sql);
  // echo $sql . ";\n";

  // add translations
  foreach ($lang as $value){
  $sql = "insert into t_categories (label_id, language_id, label) values (". $i .", '" . $value . "', 'CATEGORY_" . $faker->word . "_" . strtoupper($value) . "')";
  $conn->query($sql);
  // echo $sql . ";\n";
  }
}
echo "done!\n";

echo "loading DISHES ...";
for ($i=1; $i<=DISHES_COUNT; $i++){
  //random categories
  $category = $faker->optional($weight = 0.8, $default = "NULL")->numberBetween($min = 1, $max = CATEGORIES_COUNT);
  $sql = "insert into dishes (label_id, slug, description_id, category_id) values (" . $i . ", 'dish_" . $i . "', " . $i . ", ". $category .")";
  $conn->query($sql);
  //echo $sql . ";\n";

  $ingredients = generateRandomTuples(1,INGREDIENTS_COUNT,1,7);
  foreach ($ingredients as $j){
    $sql = "insert into dish_ingredients (dish_id, ingredient_id) values (" . $i . ", " . $j .")";
    $conn->query($sql);
    //echo $sql . ";\n";
  }
  $ingredients = generateRandomTuples(1,TAGS_COUNT,1,3);
  foreach ($ingredients as $j){
    $sql = "insert into dish_tags (dish_id, tag_id) values (" . $i . ", " . $j .")";
    $conn->query($sql);
    // echo $sql . ";\n";
  }

  // add translations
  foreach ($lang as $value){
  $sql = "insert into t_dishes (label_id, language_id, label, description) values (". $i .", '" . $value . "', 'DISH_" . $faker->word . "_" . strtoupper($value) . "', '" . strtoupper($value) . " - " . $faker->text . "')";
  $conn->query($sql);
  // echo $sql . ";\n";
  }
}
echo "done!\n";

dbDisconnect($conn);

?>
