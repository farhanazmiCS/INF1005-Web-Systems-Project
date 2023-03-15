<?php
    // When this page is loaded, fetch the user's cart
    $success = true;
    $cart = array(); // Cart to store all the items
    $errorMsg = $fname = "";
    $email = "test@gmail.com"; // In real world, this would not be hardcoded
    $config = parse_ini_file('../../private/db-config.ini');
    $conn = new mysqli($config['servername'], $config['username'],
    $config['password'], $config['dbname']);
    // Check connection
    if ($conn->connect_error)
    {
        $errorMsg = "Connection failed: " . $conn->connect_error;
        $success = false;
    }
    else {
        // Prepare the statement (Selecting the user):
            $stmt_select_user = $conn->prepare("SELECT * FROM users WHERE
            email=?");
            // Bind & execute the query statement:
            $stmt_select_user->bind_param("s", $email);
            $stmt_select_user->execute();
            $result = $stmt_select_user->get_result();
            if ($result->num_rows > 0)
            {
                // Note that email field is unique, so should only have
                // one row in the result set.
                $row = $result->fetch_assoc();
                $user_id = $row["user_id"];
                $fname = $row["fname"];
                $stmt_select_user->close();
                
                $stmt_cart = $conn->prepare("SELECT * FROM carts WHERE user_id=?");
                $stmt_cart->bind_param("i", $user_id);
                $stmt_cart->execute();
                $result_cart = $stmt_cart->get_result();
                
                if ($result_cart->num_rows > 0) {
                    $row_product = $result_cart->fetch_all();
                    for ($i = 0; $i < $result_cart->num_rows; $i++) {
                        $cart[$i]["item_id"] = $row_product[$i]["item_id"];
                        $cart[$i]["quantity"] = $row_product[$i]["quantity"];
                        $stmt_product = $conn->prepare("SELECT * FROM product WHERE product_id=?");
                        $stmt_product->bind_param("i", $row_product[$i]["product_id"]);
                        $stmt_product->execute();
                        $result_product = $stmt_product->get_result();
                        
                        if ($result_product->num_rows == 1) {
                            $row_product = $result_product->fetch_assoc();
                            $cart[$i]["product_name"] = $row_product["product_name"];
                            $cart[$i]["product_info"] = $row_product["product_info"];
                            $cart[$i]["product_composition"] = $row_product["product_composition"];
                            $cart[$i]["product_img_front"] = $row_product["product_img_front"];
                            $cart[$i]["product_img_model"] = $row_product["product_img_model"];
                            $cart[$i]["product_care"] = $row_product["product_care"];
                            $cart[$i]["product_price"] = $row_product["product_price"];
                            $cart[$i]["category"] = $row_product["category"];
                        }
                        else {
                            $success = false;
                            $errorMsg = "ERROR: " . $stmt_product->errno;
                        }
                        $stmt_product->close();
                    }
                }
            }
            else
            {
                $errorMsg = "Email not found or password doesn't match...";
                $success = false;
            }
            $stmt_cart->close();
    }
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Add to cart</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet"
            href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
            integrity=
            "sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
            crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="css/main.css">
        <!-- Fontawesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!--jQuery-->
        <script defer
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
            crossorigin="anonymous">
        </script>
        <!--Bootstrap JS-->
        <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php
            include "nav.inc.php";
        ?>
        <div class="container">
            <h3>My Shopping Bag</h3>
            <br/>
            <?php
                for ($i=0; i < count($cart); $i++) {
                    $item_name = $cart[i]["product_name"];
                    echo "<p>$item_name</p>";
                }
            ?>
            <form action="process_cart.php" method="POST">
                <button class="btn btn-primary" type="submit">Click</button>
            </form>
        </div>
    </body>
</html>