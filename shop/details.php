<?php include 'inc/header.php'; ?>

<?php
// Require a valid product id
$id = null;
if (isset($_GET['proid'])) {
    $id = filter_input(INPUT_GET, 'proid', FILTER_VALIDATE_INT);
    if ($id === false || $id <= 0) {
        die("Invalid product ID.");
    }
}

// Ensure CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$addCart   = null;
$insertCom = null;
$saveWlist = null;

// Handle cart add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 20] // set a sensible max
    ]);

    if ($quantity === false) {
        $addCart = "<span class='error'>Invalid quantity.</span>";
    } elseif ($id) {
        $addCart = $ct->addToCart($quantity, $id);
    }
}

// Handle compare
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['compare'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $productId = filter_input(INPUT_POST, 'productId', FILTER_VALIDATE_INT);
    if ($productId) {
        $insertCom = $pd->insertCompareData($productId, $cmrId);
    }
}

// Handle wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wlist'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    if ($id) {
        $saveWlist = $pd->saveWishListData($id, $cmrId);
    }
}
?>

<style>
    .mybutton { width: 100px; float: left; margin-right: 50px; }
</style>

<div class="main">
    <div class="content">
        <div class="section group">
            <div class="cont-desc span_1_of_2">    
                <?php 
                $getPd = $pd->getSingleProduct($id);
                if ($getPd) {
                    while ($result = $getPd->fetch_assoc()) {
                ?>          
                <div class="grid images_3_of_2">
                    <img src="admin/<?php echo htmlspecialchars($result['image']); ?>" alt="" />
                </div>
                <div class="desc span_3_of_2">
                    <h2><?php echo htmlspecialchars($result['productName']); ?> </h2>                
                    <div class="price">
                        <p>Price: <span>TK.<?php echo number_format($result['price'], 2); ?></span></p>
                        <p>Category: <span><?php echo htmlspecialchars($result['catName']); ?></span></p>
                        <p>Brand:<span><?php echo htmlspecialchars($result['brandName']); ?></span></p>
                    </div>
                    <div class="add-cart">
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                            <input type="number" class="buyfield" name="quantity" value="1" min="1" max="20" />
                            <input type="submit" class="buysubmit" name="submit" value="Buy Now"/>
                        </form>                
                    </div>

                    <span style="color: red;font-size: 18px;">
                        <?php 
                        if ($addCart)   echo $addCart;
                        if ($insertCom) echo $insertCom;
                        if ($saveWlist) echo $saveWlist;
                        ?>
                    </span>

                    <?php 
                    $login = Session::get("cuslogin");
                    if ($login == true) {
                    ?>
                    <div class="add-cart">
                        <div class="mybutton">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="productId" value="<?php echo (int)$result['productId']; ?>"/>
                                <input type="submit" class="buysubmit" name="compare" value="Add to Compare"/>
                            </form>    
                        </div>

                        <div class="mybutton">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="submit" class="buysubmit" name="wlist" value="Save to List"/>
                            </form>    
                        </div>        
                    </div>
                    <?php } ?>
                </div>
                <div class="product-desc">
                    <h2>Product Details</h2>
                    <?php echo $result['body']; ?>
                </div>
                <?php 
                    } // end while
                } // end if getPd
                ?>  
            </div>

            <div class="rightsidebar span_3_of_1">
                <h2>CATEGORIES</h2>
                <ul>
                    <?php 
                    $getCat = $cat->getAllCat();
                    if ($getCat) {
                        while ($result = $getCat->fetch_assoc()) {
                    ?>
                    <li><a href="productbycat.php?catId=<?php echo (int)$result['catId']; ?>">
                        <?php echo htmlspecialchars($result['catName']); ?>
                    </a></li>
                    <?php }} ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php include 'inc/footer.php'; ?>