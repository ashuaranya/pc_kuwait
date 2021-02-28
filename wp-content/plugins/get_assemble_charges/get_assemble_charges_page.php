<?php
/*
	Plugin Name: Assemble Charges
*/
function add_style_sheet(){
?>
<style>
	table{
		width: 95%;
    overflow: hidden;
    max-height: 500px;
    overflow-y: scroll;
    margin-top: 40px;
    border-collapse: collapse;
	}
	thead th, tbody td {
    border: 1px solid #505050;
        text-align: center;
        padding: 5px 10px;
}
</style>
<?php
}
add_action( 'admin_head', 'add_style_sheet' );

function assemble_charges_activation(){
	global $wpdb;
	$table = $wpdb->prefix.'charges';
	$sql =
         "CREATE TABLE {$table} (
         id INT(10) NOT NULL AUTO_INCREMENT,
         min_amount varchar(255) NOT NULL,
		 max_amount varchar(255) NOT NULL,
		 charges varchar(255) NOT NULL,
         PRIMARY KEY  (id)
         )";	
	
  	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	//$wpdb->query($sql);
	DBdelta($sql);
}
register_activation_hook(__FILE__,'assemble_charges_activation');


function get_assemble_charges(){
	add_menu_page('Add Assemble Charges', 'Assemble Charges', 9, __FILE__, 'get_assemble_charges_page');
}
add_action('admin_menu', 'get_assemble_charges');

function get_assemble_charges_page(){
	if(isset($_POST['saveData'])){
		$min_amount = $_POST['min_amount'];
		$max_amount = $_POST['max_amount'];
		$charges = $_POST['charges'];
		global $wpdb;
		$table = $wpdb->prefix.'charges';
		$sql = array(
			'min_amount' => $min_amount,
			'max_amount' => $max_amount,
			'charges' => $charges,
		);
		$result = $wpdb->insert($table, $sql);
		if($result){
			echo '<br><br>Data submitted successfully.';
		}
	}

	if(isset($_POST['editData'])){
		$id = stripslashes($_POST['id']);
		$min_amount = stripslashes($_POST['min_amount']);
		$max_amount = stripslashes($_POST['max_amount']);
		$charges = stripslashes($_POST['charges']);
		global $wpdb;
		$table = $wpdb->prefix.'charges';
		$sql = array(
			'min_amount' => $min_amount,
			'max_amount' => $max_amount,
			'charges' => $charges,
		);
		$result = $wpdb->query ($wpdb->prepare("UPDATE $table SET min_amount=%d, max_amount=%d, charges=%d WHERE id=%d",$min_amount, $max_amount, $charges, $id));
		//$result = $wpdb->update($table, array('min_amount' => $min_amount ,'max_amount' => $max_amount,'charges' => $charges), array('id' => $id));
		//var_dump($result);
		if($result){
			echo '<br><br>Data updated successfully.';
		}
	}

?>

<br><br>
<form name="get_data" id="assemble_charges_form" action="" method="post">
	<input type="hidden" name="id" id="cid" value="">
	<label>Min Ammount</label>
	<input type="text" name="min_amount" id="min_amount" required><br><br>
	<label>Max Ammount</label>
	<input type="text" name="max_amount" id="max_amount" required><br><br>
	<label>Charges</label>
	<input type="text" name="charges" id="charges" required><br><br>
	<input type="submit" style="display: block;" name="saveData" id="saveData" value="submit">
	<input type="submit" style="display: none;" name="editData" id="editData" value="Update">
	<input type="submit" style="display: none;" onclick="cancleData()" id="cancleData" value="Cancle">
</form>
<?php
	global $wpdb;
	$table = $wpdb->prefix.'charges';
	$sql = $wpdb->get_results("SELECT * FROM $table");
?>
<table>
	<thead>
		<tr>
			<th>Id</th>
			<th>Min_Amount</th>
			<th>Max_amount</th>
			<th>Charges</th>
			<th>Action</th>
		</tr>
	</thead>
	<?php
		$count = 1;
		foreach ($sql as $value) {
			echo '<tr>'.
					'<td>'. $count++ .'</td>'.
					'<td>'. $value->min_amount .'</td>'.
					'<td>'. $value->max_amount .'</td>'.
					'<td>'. $value->charges .'</td>'.
					'<td><button class="btn" style="margin-right: 5px;" onclick="edit_charges('. $value->id .')">Edit</button><button class="btn" onclick="delete_charges('. $value->id .')">Delete</button></td>'.
				'</tr>';
		}
	?>
</table>
<?php
}
	add_action('wp_ajax_edit_assemble_charges','edit_assemble_charges');
	add_action('wp_ajax_nopriv_edit_assemble_charges','edit_assemble_charges');
	function edit_assemble_charges(){
		$id = $_POST['id'];
		global $wpdb;
		$table = $wpdb->prefix.'charges';
		$sql = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id));
		//print_r($sql);
		echo json_encode($sql);
		die();
	}

	add_action('wp_ajax_delete_assemble_charges','delete_assemble_charges');
	add_action('wp_ajax_nopriv_delete_assemble_charges','delete_assemble_charges');
	function delete_assemble_charges(){
		$id = $_POST['id'];
		global $wpdb;
		$table = $wpdb->prefix.'charges';
		$result = $wpdb->query ($wpdb->prepare("DELETE FROM $table WHERE id=%d", $id));
		echo $result;
		die();
	}

	function load_script_file(){
?>

<script type="text/javascript">
	var $ = jQuery.noConflict();
	function edit_charges(id){
		//alert(id);
		var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
			console.log(ajaxUrl);
		jQuery.ajax({
			type: 'post',
			url: ajaxUrl,
			datatype: 'json',
			data: {
				action: 'edit_assemble_charges',
				id: id,
			},
			success: function(res){
				//alert('success');	
				var res = JSON.parse(res);
				for (var i = 0; i < res.length; i++) {
				   $('#cid').val(res[i].id);
				   $('#min_amount').val(res[i].min_amount);
				   $('#max_amount').val(res[i].max_amount);
				   $('#charges').val(res[i].charges);
				   $('#saveData').css('display','none');
				   $('#editData').css('display','inline-block');
				   $('#cancleData').css('display','inline-block');
				}
			}
		})
	}
	function cancleData(){
		$('#assemble_charges_form')[0].reset();	
	}
	function delete_charges(id){
		var status = confirm("Are you sure you want to delete record?");
		if(status){
			var ajaxUrl = "../wp-admin/admin-ajax.php";
			$.ajax({
				type: 'post',
				url: ajaxUrl,
				datatype: 'json',
				data: {
					action: 'delete_assemble_charges',
					id: id,
				},
				success: function(res){
					//console.log(res);
					location.reload();
				}
			})
		}		
	}
</script>
<?php
}
add_action( 'admin_footer', 'load_script_file' );