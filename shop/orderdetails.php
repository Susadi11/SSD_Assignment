<?php include 'inc/header.php';?>
<?php include 'inc/csrf.php';?>
<?php 
$login = Session::get("cuslogin");
if ($login == false) {
    header("Location:login.php");
}
 ?>

 <?php 
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delivery'])) {
        // ADD CSRF validation
        if (!csrf_validate('confirm_order', $_POST['csrf_token'] ?? null)) {
            csrf_fail();
        }
        
        $id = $_POST['customer_id'];
        $confirm = $ct->productShiftConfirm($id);
    }
?>




 <style>
     .tblone tr td{text-align: justify;}

 </style>
 <div class="main">
    <div class="content">
    	<div class="section group">
    		<div class="order">
    			<h2>Your Ordered Details</h2>
                <table class="tblone">


                            <tr>
                                <th>No</th>
                                <th>Product Name</th>
                                <th>Image</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            <tr>

                            <?php 
                            $cmrId = Session::get("cmrId");
                            $getOrder = $ct->getOrderedProduct($cmrId);
                            if ($getOrder) {
                                $i = 0;
                                while ($result = $getOrder->fetch_assoc()) {
                                
                                $i++;

                             ?>
                                <td><?php echo $i;?></td>
                                <td><?php echo $result['productName']; ?></td>
                                <td><img src="admin/<?php echo $result['image']; ?>" alt=""/></td>
                                <td><?php echo $result['quantity']; ?></td>
                    
                                <td>Tk. <?php echo $result['price'];?></td>
                         <td><?php echo $fm->formatDate($result['date']); ?></td>

                         <td><?php

                         if ($result['status'] == '0') {
                             echo "Pending";
                         }elseif($result['status'] == '1'){
                            echo "Shifted";
                       } else{ 
                            echo "Ok";
                         }


           ?></td>
                    </td>

                
                    <?php 
                    if ($result['status'] == '1') { ?>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <?php csrf_field('confirm_order'); ?>
                                <input type="hidden" name="customer_id" value="<?php echo $result['id']; ?>"/>
                                <button type="submit" name="confirm_delivery" style="background:none;border:none;color:blue;text-decoration:underline;cursor:pointer;">Confirm</button>
                            </form>
                        </td>
                    <?php } elseif($result['status'] == '2'){?>
                        <td>Ok</td>

                  <?php }elseif ($result['status'] == '0') {?>
                      <td>N/A</td>
                 <?php  }  ?>
                   
            </tr>
                            


                        <?php } } ?>    
                        </table>

    		</div>
    	</div>
    	
    	 	
       <div class="clear"></div>
    </div>
 </div>
<?php include 'inc/footer.php';?>