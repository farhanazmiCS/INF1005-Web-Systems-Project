<?php
    $errorMsg = "";
    $myCart;
    $success = true;
    function getMyCart($user_id) {
        global $errorMsg, $success, $myCart;
        
        $config = parse_ini_file('../private/db-config.ini');
        $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
        
        // Check connection
        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            $success = false; 
        }
        else {
            $stmt = $conn->prepare("SELECT * FROM carts WHERE user_id=?");
            $stmt->bind_param("i", $user_id);
            
            if (!$stmt->execute()) {
                $errorMsg = "ERROR: " . $stmt->errno;
                $success = false;
            }
            else {
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $myCart = $result->fetch_all(MYSQLI_ASSOC);
                    
                    foreach ($myCart as &$row) {
                        $item_id = $row["item_id"];
                        $stmt_item = $conn->prepare("SELECT * FROM products WHERE product_id=?");
                        $stmt_item->bind_param("i", $item_id);
                        
                        if (!$stmt_item->execute()) {
                            $errorMsg = "ERROR: " . $stmt_item->errno;
                            $success = false;
                        }
                        else {
                            $result_item = $stmt_item->get_result();
                            
                            if ($result_item->num_rows == 1) {
                                $item = $result_item->fetch_assoc();
                                $row["product_name"] = $item["product_name"];
                                $row["product_info"] = $item["product_info"];
                                $row["product_care"] = $item["product_care"];
                                $row["product_composition"] = $item["product_composition"];
                                $row["product_img_front"] = $item["product_img_front"];
                                $row["product_img_model"] = $item["product_img_model"];
                                $row["product_price"] = $item["product_price"];
                                $row["product_category"] = $item["category"];
                                $row["product_left"] = $item["quantity"];
                            }
                        }
                        $stmt_item->close();
                    }
                }
                else {
                    $myCart = array(array());
                }
            }
            
            $stmt->close();
        }
        $conn->close();
    }
    
    getMyCart(3);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Add to cart</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

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
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    </head>
    <body>
        <?php
            include "nav.inc.php";
        ?>
        <section class="h-100 h-custom" style="background-color: #d2c9ff;">
            <div class="container py-5 h-100">
              <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-12">
                  <div class="card card-registration card-registration-2" style="border-radius: 15px;">
                    <div class="card-body p-0">
                      <div class="row g-0">
                        <div class="col-lg-8">
                          <div class="p-5">
                            <div class="d-flex justify-content-between align-items-center mb-5">
                              <h1 class="fw-bold mb-0 text-black">Shopping Cart</h1>
                              <h6 class="mb-0 text-muted">
                                  <?php
                                    $numItems = count($myCart);
                                    echo "$numItems";
                                  ?>
                                   items
                              </h6>
                            </div>
                            <hr class="my-4">
                            
                            <?php 
                                if (count($myCart) != 0) {
                                    $total = 0;
                                    foreach ($myCart as $row) {
                                        $item_id = $row["item_id"];
                                        $quantity = $row["quantity"];
                                        $size = $row["size"];
                                        $name = $row["product_name"];
                                        $price = $row["product_price"] * $quantity;
                                        $total += $price;
                                        $category = $row["product_category"];
                                        $img = base64_encode($row["product_img_front"]);
                                        
                                        echo <<<HTML
                                            <div class="row mb-4 d-flex justify-content-between align-items-center">
                                                <div class="col-md-2 col-lg-2 col-xl-2">
                                                  <img
                                                    src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-shopping-carts/img5.webp"
                                                    class="img-fluid rounded-3" alt="Cotton T-shirt">
                                                </div>
                                                <div class="col-md-3 col-lg-3 col-xl-3">
                                                  <h6 class="text-muted">$category</h6>
                                                  <h6 class="text-black mb-0">$name</h6>
                                                  <h6 class="text-black mb-0 mt-2">Size: $size</h6>
                                                </div>
                                                <div class="col-md-3 col-lg-3 col-xl-2 d-flex">
                                                  <button class="btn btn-link px-2"
                                                    onclick="this.parentNode.querySelector('input[type=number]').stepDown()">
                                                    <i class="fas fa-minus"></i>
                                                  </button>
                                        HTML;
                                        
                                        echo "<input id='form1' min='0' name='quantity' value='$quantity' type='number' class='form-control form-control-sm w-50' />";
                                        
                                                    
                                        echo <<<HTML
                                                  <button class="btn btn-link px-2"
                                                    onclick="this.parentNode.querySelector('input[type=number]').stepUp()">
                                                    <i class="fas fa-plus"></i>
                                                  </button>
                                                </div>
                                                <div class="col-md-3 col-lg-2 col-xl-2 offset-lg-1">
                                                  <h6 class="mb-0">SGD $price</h6>
                                                </div>
                                                <div class="col-md-1 col-lg-1 col-xl-1 text-end">
                                                  <a href="#!" class="text-muted"><i class="fas fa-times"></i></a>
                                                </div>
                                            </div>

                                            <hr class="my-4">
                                        HTML;
                                    }
                                }
                                else {
                                    echo <<<HTML
                                            <div class="row mb-4 d-flex justify-content-between align-items-center">
                                                <h3>Your cart is empty :/</h3>
                                            </div>

                                            <hr class="my-4">
                                        HTML;
                                }
                            ?>

                            <div class="pt-5">
                              <h6 class="mb-0"><a href="#!" class="text-body"><i
                                    class="fas fa-long-arrow-alt-left me-2"></i>Back to shop</a></h6>
                            </div>
                          </div>
                        </div>
                        <div class="col-lg-4 bg-grey">
                          <div class="p-5">
                            <h3 class="fw-bold mb-5 mt-2 pt-1">Summary</h3>
                            <hr class="my-4">

                            <div class="d-flex justify-content-between mb-4">
                              <h5 class="text-uppercase">items</h5>
                              <h5>
                                  SGD
                                  <?php
                                    echo " $total";
                                  ?>
                              </h5>
                            </div>

                            <h5 class="text-uppercase mb-3">Shipping</h5>

                            <div class="mb-4 pb-2">
                              <select class="select">
                                <option value="1">Standard-Delivery- €5.00</option>
                                <option value="2">Two</option>
                                <option value="3">Three</option>
                                <option value="4">Four</option>
                              </select>
                            </div>

                            <h5 class="text-uppercase mb-3">Give code</h5>

                            <div class="mb-5">
                              <div class="form-outline">
                                <input type="text" id="form3Examplea2" class="form-control form-control-lg" />
                                <label class="form-label" for="form3Examplea2">Enter your code</label>
                              </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-between mb-5">
                              <h5 class="text-uppercase">Total price</h5>
                              <?php
                                echo "<h5>SGD $total</h5>";
                              ?>
                            </div>

                            <button type="button" class="btn btn-dark btn-block btn-lg"
                              data-mdb-ripple-color="dark">Register</button>

                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </section>
    </body>
</html>