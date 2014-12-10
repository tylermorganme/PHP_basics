<?php

function get_list_view_html($product) {
    
    $output = "";

    $output = $output . "<li>";
    $output = $output . '<a href="' . BASE_URL . 'shirts/' . $product["sku"] . '/">';
    $output = $output . '<img src="' . BASE_URL . $product["img"] . '" alt="' . $product["name"] . '">';
    $output = $output . "<p>View Details</p>";
    $output = $output . "</a>";
    $output = $output . "</li>";

    return $output;
}

function get_product($id) {
    $product = array();
    $all = get_products_all();
    if (isset($_GET["id"])) {
        $product_id = $_GET["id"];
        if (isset($all[$product_id])) {
            $product = $all[$product_id];
        }
    }

    return $product;
}

function get_products_recent() {
    require(ROOT_PATH . "inc/database.php");
    try {
        $results = $db->query("
            SELECT name, price, img, sku, paypal
            FROM products
            ORDER BY sku DESC
            LIMIT 4
            ");
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }

    $recent = $results->fetchAll(PDO::FETCH_ASSOC);
    $recent = array_reverse($recent);
    return $recent;
}

function get_products_search($s){
    require(ROOT_PATH . "inc/database.php");
    try {
        $results = $db->prepare("
            SELECT name, price, img, sku, paypal
            FROM products
            WHERE name LIKE ?
            ORDER BY sku
            ");
        $results->bindValue(1, "%" . $s . "%");
        $results->execute();
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }
    $matches = $results->fetchAll(PDO::FETCH_ASSOC);
    return $matches;
}

function get_products_count(){
    require(ROOT_PATH . "inc/database.php");
    try {
        $results = $db->query("
            SELECT COUNT(sku)
            FROM products
            ");
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }

    return intval($results->fetchColumn(0));
}

function get_products_subset($positionStart, $positionEnd) {
    $offset = $positionStart - 1;
    $rows = $positionEnd - $positionStart + 1;
    require(ROOT_PATH . "inc/database.php");
    try {
        $results = $db->prepare("
            SELECT name, price, img, sku, paypal
            FROM products
            ORDER BY sku
            LIMIT ?, ?
            ");
        $results->bindParam(1, $offset, PDO::PARAM_INT);
        $results->bindParam(2, $rows, PDO::PARAM_INT);
        $results->execute();
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }
    $subset = $results->fetchAll(PDO::FETCH_ASSOC);
    return $subset;
}

function get_products_all() {

    include(ROOT_PATH . "inc/database.php");

    try {
        $results = $db->query("SELECT name, price, img, sku, paypal FROM products ORDER BY sku ASC");
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }

    $products = $results->fetchAll(PDO::FETCH_ASSOC);

    return $products;
}

/*
* Returns an array of product information for the product that matches the sku;
* returns a boolean false is no product matches the sku
* @param int $sku the sku
* @return mixed array list of product information for the one matching product
*/

function get_product_single($sku) {
    require(ROOT_PATH . "inc/database.php");

    try {
        $results = $db->prepare("SELECT name, price, img, sku, paypal FROM products WHERE sku = ?");
        $results->bindParam(1, $sku);
        $results->execute();
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }

    $product = $results->fetch(PDO::FETCH_ASSOC);

    if($product === false) {
        return $product;
    }

    $product["sizes"] = array();
    try {
        $results = $db->prepare("
            SELECT size 
            FROM products_sizes ps 
            INNER JOIN sizes s on ps.size_id = s.id
            WHERE product_sku = ?
            ORDER BY `order`"
            );
        $results->bindParam(1, $sku);
        $results->execute();
    } catch (Exception $e) {
        echo "Query Failed.";
        exit;
    }

    while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
        $product["sizes"][] = $row["size"];
    }


    return $product;
}

?>