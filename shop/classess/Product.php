<?php
$filepath = realpath(dirname(__FILE__));
include_once ($filepath.'/../lib/Database.php');
include_once ($filepath.'/../helpers/Formate.php');

?>

<?php

class Product{
	
private $db;
private $fm;

	public function __construct()
	{
		$this->db = new Database();
		$this->fm = new Format();
	}

	public function productInsert($data,$file){
		// Input validation
		$productName = $this->fm->sanitizeString($data['productName']);
		$catId = $this->fm->sanitizeNumber($data['catId']);
		$brandId = $this->fm->sanitizeNumber($data['brandId']);
		$body = $this->fm->sanitizeString($data['body']);
		$price = $this->fm->sanitizeString($data['price']);
		$type = $this->fm->sanitizeString($data['type']);

		$productName = $this->fm->escapeString($productName, $this->db->link);
		$catId = $this->fm->escapeString($catId, $this->db->link);
		$brandId = $this->fm->escapeString($brandId, $this->db->link);
		$body = $this->fm->escapeString($body, $this->db->link);
		$price = $this->fm->escapeString($price, $this->db->link);
		$type = $this->fm->escapeString($type, $this->db->link);

		$permited  = array('jpg', 'jpeg', 'png', 'gif');
		$file_name = $file['image']['name'];
		$file_size = $file['image']['size'];
		$file_temp = $file['image']['tmp_name'];

		$div = explode('.', $file_name);
		$file_ext = strtolower(end($div));
		$unique_image = substr(md5(time()), 0, 10).'.'.$file_ext;
		$uploaded_image = "uploads/".$unique_image;

		if ($productName == "" || $catId == "" || $brandId == "" || $body == "" || $price == "" || $file_name == "" || $type == "") {
			$msg = "<span class='error'>Fields must not be empty !</span>";
			return $msg;
		} elseif ($file_size >1048567) {
			echo "<span class='error'>Image Size should be less then 1MB!</span>";
		} elseif (in_array($file_ext, $permited) === false) {
			echo "<span class='error'>You can upload only:-".implode(', ', $permited)."</span>";
		} else {
			move_uploaded_file($file_temp, $uploaded_image);
			
			// Use prepared statement
			$query = "INSERT INTO tbl_product(productName,catId,brandId,body,price,image,type) VALUES(?,?,?,?,?,?,?)";
			$params = [$productName, $catId, $brandId, $body, $price, $uploaded_image, $type];
			$types = "siisdss";
			
			$inserted_row = $this->db->insert($query, $params, $types);
			if ($inserted_row) {
				$msg = "<span class='success'>Product inserted Successfully.</span>";
				return $msg;
			} else {
				$msg = "<span class='error'>Product Not inserted.</span>";
				return $msg;
			}
		}
	}

	public function getAllProduct(){
		$query = "SELECT p.*,c.catName,b.brandName
				  FROM tbl_product as p,tbl_category as c, tbl_brand as b
				  WHERE p.catId = c.catId AND p.brandId = b.brandId
				  ORDER BY p.productId DESC";
		
		$result = $this->db->select($query);
		return $result;
	}

	public function getProById($id){
		// Validate ID
		$id = $this->fm->validatePositiveInt($id);
		if (!$id) {
			return false;
		}
		
		$query = "SELECT * FROM tbl_product WHERE productId = ?";
		$result = $this->db->select($query, [$id], 'i');
		return $result;
	}

	public function productUpdate($data,$file,$id){
		// Validate ID
		$id = $this->fm->validatePositiveInt($id);
		if (!$id) {
			$msg = "<span class='error'>Invalid product ID!</span>";
			return $msg;
		}

		$productName = $this->fm->sanitizeString($data['productName']);
		$catId = $this->fm->sanitizeNumber($data['catId']);
		$brandId = $this->fm->sanitizeNumber($data['brandId']);
		$body = $this->fm->sanitizeString($data['body']);
		$price = $this->fm->sanitizeString($data['price']);
		$type = $this->fm->sanitizeString($data['type']);

		$productName = $this->fm->escapeString($productName, $this->db->link);
		$catId = $this->fm->escapeString($catId, $this->db->link);
		$brandId = $this->fm->escapeString($brandId, $this->db->link);
		$body = $this->fm->escapeString($body, $this->db->link);
		$price = $this->fm->escapeString($price, $this->db->link);
		$type = $this->fm->escapeString($type, $this->db->link);

		$permited  = array('jpg', 'jpeg', 'png', 'gif');
		$file_name = $file['image']['name'];
		$file_size = $file['image']['size'];
		$file_temp = $file['image']['tmp_name'];

		$div = explode('.', $file_name);
		$file_ext = strtolower(end($div));
		$unique_image = substr(md5(time()), 0, 10).'.'.$file_ext;
		$uploaded_image = "uploads/".$unique_image;

		if ($productName == "" || $catId == "" || $brandId == "" || $body == "" || $price == "" || $type == "") {
			$msg = "<span class='error'>Fields must not be empty !</span>";
			return $msg;
		} else {
			if (!empty($file_name)) {
				if ($file_size >1048567) {
					echo "<span class='error'>Image Size should be less then 1MB!</span>";
				} elseif (in_array($file_ext, $permited) === false) {
					echo "<span class='error'>You can upload only:-".implode(', ', $permited)."</span>";
				} else {
					move_uploaded_file($file_temp, $uploaded_image);
					
					// Use prepared statement
					$query = "UPDATE tbl_product SET productName=?, catId=?, brandId=?, body=?, price=?, image=?, type=? WHERE productId=?";
					$params = [$productName, $catId, $brandId, $body, $price, $uploaded_image, $type, $id];
					$types = "siisdssi";
					
					$updatedted_row = $this->db->update($query, $params, $types);
					if ($updatedted_row) {
						$msg = "<span class='success'>Product Updated Successfully.</span>";
						return $msg;
					} else {
						$msg = "<span class='error'>Product Not Updated.</span>";
						return $msg;
					}
				}
			} else {
				// Use prepared statement
				$query = "UPDATE tbl_product SET productName=?, catId=?, brandId=?, body=?, price=?, type=? WHERE productId=?";
				$params = [$productName, $catId, $brandId, $body, $price, $type, $id];
				$types = "siissi";
				
				$updatedted_row = $this->db->update($query, $params, $types);
				if ($updatedted_row) {
					$msg = "<span class='success'>Product Updated Successfully.</span>";
					return $msg;
				} else {
					$msg = "<span class='error'>Product Not Updated.</span>";
					return $msg;
				}
			}
		}
	}

	public function delProById($id){
		// Validate ID
		$id = $this->fm->validatePositiveInt($id);
		if (!$id) {
			$msg = "<span class='error'>Invalid product ID!</span>";
			return $msg;
		}
		
		$query = "SELECT * FROM tbl_product WHERE productId = ?";
		$getData = $this->db->select($query, [$id], 'i');
		
		if ($getData) {
			while ($delImg = $getData->fetch_assoc()) {
				$dellink = $delImg['image'];
				if (file_exists($dellink)) {
					unlink($dellink);
				}
			}
		}

		$delquery = "DELETE FROM tbl_product WHERE productId = ?";
		$deldata = $this->db->delete($delquery, [$id], 'i');
		
		if ($deldata) {
			$msg = "<span class='success'>Product Deleted Successfully.</span>";
			return $msg;
		} else {
			$msg = "<span class='error'>Product Not Deleted !</span>";
			return $msg;
		}
	}

	public function getFeaturedProduct(){
		$query = "SELECT * FROM tbl_product WHERE type = '0' ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}

	public function getNewProduct(){
		$query = "SELECT * FROM tbl_product ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}

	public function getSingleProduct($id){
		// Validate ID
		$id = $this->fm->validatePositiveInt($id);
		if (!$id) {
			return false;
		}
		
		$query = "SELECT p.*,c.catName,b.brandName
				  FROM tbl_product as p,tbl_category as c, tbl_brand as b
				  WHERE p.catId = c.catId AND p.brandId = b.brandId AND p.productId = ?";
		$result = $this->db->select($query, [$id], 'i');
		return $result;
	}

	public function latestFromIphone(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '4' ORDER BY productId DESC LIMIT 1";
		$result = $this->db->select($query);
		return $result;
	}
	
	public function latestFromSamsung(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '2' ORDER BY productId DESC LIMIT 1";
		$result = $this->db->select($query);
		return $result;
	}
	
	public function latestFromAcer(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '5' ORDER BY productId DESC LIMIT 1";
		$result = $this->db->select($query);
		return $result;
	}
	
	public function latestFromCanon(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '3' ORDER BY productId DESC LIMIT 1";
		$result = $this->db->select($query);
		return $result;
	}

	public function productByCat($id){
		// Validate ID
		$id = $this->fm->validatePositiveInt($id);
		if (!$id) {
			return false;
		}
		
		$query = "SELECT * FROM tbl_product WHERE catId = ?";
		$result = $this->db->select($query, [$id], 'i');
		return $result;	
	}

	public function insertCompareData($cmprid,$cmrId){
		// Validate IDs
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		$productId = $this->fm->validatePositiveInt($cmprid);
		
		if (!$cmrId || !$productId) {
			$msg = "<span class='error'>Invalid data!</span>";
			return $msg;
		}

		$cquery = "SELECT * FROM tbl_compare WHERE cmrId = ? AND productId = ?";
		$check = $this->db->select($cquery, [$cmrId, $productId], 'ii');
		
		if ($check) {
			$msg = "<span class='error'>Already Added !</span>";
			return $msg;
		}
		
		$query = "SELECT * FROM tbl_product WHERE productId = ?";
		$result = $this->db->select($query, [$productId], 'i');
		
		if ($result) {
			$row = $result->fetch_assoc();
			$productId = $row['productId'];
			$productName = $row['productName'];
			$price = $row['price'];
			$image = $row['image'];

			$query = "INSERT INTO tbl_compare(cmrId,productId,productName,price,image) VALUES (?,?,?,?,?)";
			$params = [$cmrId, $productId, $productName, $price, $image];
			$types = "iisds";
			
			$inserted_row = $this->db->insert($query, $params, $types);
			if ($inserted_row) {
				$msg = "<span class='success'>Added ! Check Compare Page</span>";
				return $msg;
			} else {
				$msg = "<span class='error'>Not Added !</span>";
				return $msg;
			}
		}
	}

	public function getCompareData($cmrId){
		// Validate ID
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		if (!$cmrId) {
			return false;
		}
		
		$query = "SELECT * FROM tbl_compare WHERE cmrId = ? ORDER BY id desc";
		$result = $this->db->select($query, [$cmrId], 'i');
		return $result;
	}

	public function delCompareData($cmrId){
		// Validate ID
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		if (!$cmrId) {
			return false;
		}
		
		$query = "DELETE FROM tbl_compare WHERE cmrId = ?";
		$deldata = $this->db->delete($query, [$cmrId], 'i');
		return $deldata;
	}

	public function saveWishListData($id,$cmrId){
		// Validate IDs
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		$productId = $this->fm->validatePositiveInt($id);
		
		if (!$cmrId || !$productId) {
			$msg = "<span class='error'>Invalid data!</span>";
			return $msg;
		}

		$cquery = "SELECT * FROM tbl_wlist WHERE cmrId = ? AND productId = ?";
		$check = $this->db->select($cquery, [$cmrId, $productId], 'ii');
		
		if ($check) {
			$msg = "<span class='error'>Already Added !</span>";
			return $msg;
		}
		
		$pquery = "SELECT * FROM tbl_product WHERE productId = ?";
		$result = $this->db->select($pquery, [$productId], 'i');
		
		if ($result) {
			$row = $result->fetch_assoc();
			$productId = $row['productId'];
			$productName = $row['productName'];
			$price = $row['price'];
			$image = $row['image'];

			$query = "INSERT INTO tbl_wlist(cmrId,productId,productName,price,image) VALUES (?,?,?,?,?)";
			$params = [$cmrId, $productId, $productName, $price, $image];
			$types = "iisds";
			
			$inserted_row = $this->db->insert($query, $params, $types);
			if ($inserted_row) {
				$msg = "<span class='success'>Added ! Check wishlist Page</span>";
				return $msg;
			} else {
				$msg = "<span class='error'>Not Added !</span>";
				return $msg;
			}
		}
	}

	public function checkWlistData($cmrId){
		// Validate ID
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		if (!$cmrId) {
			return false;
		}
		
		$query = "SELECT * FROM tbl_wlist WHERE cmrId = ? ORDER BY id desc";
		$result = $this->db->select($query, [$cmrId], 'i');
		return $result;	
	}
	
	public function delWlistData($cmrId, $productId){
		// Validate IDs
		$cmrId = $this->fm->validatePositiveInt($cmrId);
		$productId = $this->fm->validatePositiveInt($productId);
		
		if (!$cmrId || !$productId) {
			return false;
		}
		
		$query = "DELETE FROM tbl_wlist WHERE cmrId = ? AND productId = ?";
		$delete = $this->db->delete($query, [$cmrId, $productId], 'ii');
		return $delete;
	}

	public function getTopbrandAcer(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '5' ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}
	
	public function getTopbrandSamsung(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '2' ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}

	public function getTopbrandCanon(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '3' ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}

	public function getTopbrandIphone(){
		$query = "SELECT * FROM tbl_product WHERE brandId = '4' ORDER BY productId DESC LIMIT 4";
		$result = $this->db->select($query);
		return $result;
	}
}

?>