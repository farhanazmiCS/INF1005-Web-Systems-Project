<?php
    $errorMsg = "";
    function addItemToCart($user_id, $product_id, $size, $quantity) {
        global $errorMsg, $success;
        // Establish connection first
        $success = true;
        $config = parse_ini_file('../private/db-config.ini');
        $conn = new mysqli($config['servername'], $config['username'],
        $config['password'], $config['dbname']);
        // Check connection
        if ($conn->connect_error)
        {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false;
        }
        else {
            if ($user_id != 0) {
                $stmt_select_product = $conn->prepare("SELECT * FROM products WHERE product_id=?");
                $stmt_select_product->bind_param("i", $product_id);
                if (!$stmt_select_product->execute()) {
                    $errorMsg = "Your error is: " . $stmt_select_product->errno;
                    $success = false;
                }
                $result_product = $stmt_select_product->get_result();

                if ($result_product->num_rows != 0) {
                    $row_product = $result_product->fetch_assoc();
                    $product_id = $row_product["product_id"];
                    $stmt_select_product->close();

                    // First, check if the current item exist
                    $stmt_select_cart = $conn->prepare("SELECT * FROM carts WHERE user_id=? AND item_id=? AND size=?");
                    $stmt_select_cart->bind_param("iis", $user_id, $product_id, $size);
                    $stmt_select_cart->execute();
                    $result_cart = $stmt_select_cart->get_result();
                    $stmt_select_cart->close();

                    if ($result_cart->num_rows == 0) {
                        $stmt_insert_cart = $conn->prepare("INSERT INTO carts (user_id, item_id, quantity, size) VALUES (?, ?, ?, ?)");
                        $stmt_insert_cart->bind_param("iiis", $user_id, $product_id, $quantity, $size);
                        if (!$stmt_insert_cart->execute()) {
                            $errorMsg = "This error is " . $stmt_insert_cart->errno;
                            $success = false;
                        }
                        $stmt_insert_cart->close();
                    }
                    else {
                        $row = $result_cart->fetch_assoc();
                        $new_quantity = $row["quantity"] + $quantity;
                        $stmt_update_cart = $conn->prepare("UPDATE carts SET quantity=? WHERE user_id=? AND item_id=? AND size=?");
                        $stmt_update_cart->bind_param("iiis", $new_quantity, $user_id, $product_id, $size);
                        if (!$stmt_update_cart->execute()) {
                            $errorMsg = "HHAHAH Failed" . $stmt_update_cart->errno;
                            $success = false;
                        }
                        $stmt_update_cart->close();
                    }
                }
            }     
        }
        $conn->close();
    }
    
    function removeItemFromCart() {
        // Todo
    }
    
    



            
    addItemToCart(3, 1, "M", 3);
    
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>Cart Processed</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
            integrity=
            "sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
            crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="css/main.css">
        <!--jQuery-->
        <script defer
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous">
        </script>
        <!--Bootstrap JS-->
        <script defer
            src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"
            integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm"
            crossorigin="anonymous">
        </script>
        <!-- Custom Javascript -->
        <script defer src="js/main.js"></script>
    </head>
    <body>
        <?php
            include "nav.inc.php";
        ?>
            <main>
                    <?php
                    if ($success) {
                        echo '<div class="container">';
                        echo "<h1>Success</h1>";
                        echo "<h3>$fname, $user_id</h3>";
                        echo "<h3>$product_id, $product_name</h3>";
                        echo "</div>";
                        
                    }
                    else {
                        echo '<div class="container">';
                        echo "<h1>Failed!</h1>";
                        echo "<hr><h3>Failed:</h3>";
                        echo "<p>$errorMsg</p>";
                        echo "</div>";
              
                    }
                    ?>
            </main>
    <body>
</html>
