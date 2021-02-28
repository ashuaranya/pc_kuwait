<?php include_once(plugin_dir_path( dirname(__FILE__) ) . 'functions/index.php'); ?>

<!doctype html>
<html <?php language_attributes(); ?> >
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <?php wp_head(); ?>
        <style>
          .mstore_input {
            margin-bottom: 10px;
            width:400px !important;
			  padding: .857em 1.214em !important;
				background-color: transparent;
				color: #818181 !important;
				line-height: 1.286em !important;
				outline: 0;
				border: 0;
				-webkit-appearance: none;
				border-radius: 1.571em !important;
				box-sizing: border-box;
				border-width: 1px;
				border-style: solid;
				border-color: #ddd;
				box-shadow: inset 0 1px 2px rgba(0,0,0,.07) !important;
				transition: 50ms border-color ease-in-out;
				font-family: "Open Sans",HelveticaNeue-Light,"Helvetica Neue Light","Helvetica Neue",Helvetica,Arial,"Lucida Grande",sans-serif;
			    touch-action: manipulation;
          }
          .mstore_button {
            position: relative;
            border: 0 none;
            border-radius: 3px !important;
            color: #fff !important;
            display: inline-block;
            font-family: 'Poppins','Open Sans', Helvetica, Arial, sans-serif;
            font-size: 12px;
            letter-spacing: 1px;
            line-height: 1.5;
            text-transform: uppercase;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            margin-bottom: 21px;
            margin-right: 10px;
            line-height: 1;
            padding: 12px 30px;
            background: #39c36e !important;
            -webkit-transition: all 0.21s ease;
            -moz-transition: all 0.21s ease;
            -o-transition: all 0.21s ease;
            transition: all 0.21s ease;
          }
          .mstore_title{
            font-size: 18px;
            font-weight: 500;
            margin-bottom: .5em;
            line-height: 1.1;
            display: block;
            margin-inline-start: 0px;
            margin-inline-end: 0px;
          }
          .mstore_list{
			margin: 0;
			padding: 0;
			border: 0;
			font-size: 100%;
			font: inherit;
			vertical-align: baseline;
			display: block;
			margin-block-start: 1em;
			margin-block-end: 1em;
			margin-inline-start: 0px;
			margin-inline-end: 0px;
			padding-inline-start: 40px;
            list-style: none;
          }
          .mstore_list li{
            list-style-type: square;
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 6px;
            display: list-item;
            text-align: -webkit-match-parent;
          }
			.mstore_number_list li{
				list-style-type:decimal;
			}
			.mstore_link{
				margin-inline-start: 0px;
            	margin-inline-end: 0px;
				color: #0099ff;
				text-decoration: none;
				outline: 0;
				transition-property: border,background,color;
				transition-duration: .05s;
				transition-timing-function: ease-in-out;
				margin: 0;
				padding: 0;
				border: 0;
				font-size: 100%;
				font: inherit;
				vertical-align: baseline;
				margin-bottom: 20px;
				display: block;
			}

      .mstore_table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 1.236rem;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
        display: table;
        border-color: grey;
      }

      .mstore_table a{
        color: #0099ff;
				text-decoration: none;
      }

      .mstore_table th, .mstore_table td {
        text-align: left;
      }
        </style>
    </head>
  <body>
<div class="wrap">
	<h1>MStore API Settings</h1>

  <br>

  <div class="thanks">
  <p style="font-size: 16px;">Thank you for installing Mstore API plugins.</p>
  <?php 
   $verified = get_option("mstore_purchase_code");
   if(isset($verified) && $verified == true){
     ?>
      <p style="font-size: 16px;color: green">Your website have been license and all the API features are unlocked. </p>
     <?php
   }
  ?>
	</div>
</div>
<?php
$verified = get_option("mstore_purchase_code");
if(!isset($verified) || $verified == false){
?>
  <form action="" enctype="multipart/form-data" method="post" style="margin-bottom:50px">
    <?php
    if (isset($_POST['but_verify'])) {     
      $verified = verifyPurchaseCode($_POST['code']);

      if ($verified !== true) {
        ?>
        <p style="font-size: 16px;color: red;"><?=$verified?></p>
        <?php
      }else{
        ?>
        <p style="font-size: 16px;color: green">Your website have been license and all the API features are unlocked. </p>
      <?php
      }
    }
    ?>
    <div class="form-group" style="margin-top:10px">
        <input name="code" placeholder="Purchase Code" type="text" class="mstore_input">
    </div>
    <div>
    <h4 class="mstore_title">What is purchase code?</h4>
    <ul class="mstore_list">
      <li>A purchase code is a license identifier which is issued with the item once a purchase has been made and included with your download.</li>
      <li>One purchase code is used for one website only.</li>
      <li>It's required to active to unlock the API use to connect with the app.</li>
    </ul>
    <h4 class="mstore_title">How can I get my purchase code? </h4>
    <ul class="mstore_list mstore_number_list">
      <li>Log into your Envato Market account.</li>
      <li>Hover the mouse over your username at the top of the screen.</li>
      <li>Click ‘Downloads’ from the drop-down menu.`</li>
      <li>Click ‘License certificate & purchase code’ (available as PDF or text file).</li>
    </ul>
    <a class="mstore_link" href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-">https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-</a>
    </div>
    <button type="submit" class="mstore_button" name='but_verify'>Verify</button>
  </form>
<?php
}else{
  checkCurrentPurchaseCode();
}

if(isset($verified) && $verified == true){
?>
<div class="thanks">
  <p style="font-size: 16px;">This setting limit the number of product per category to use cache data in home screen</p>
</div>
<form action="" method="post">
    <?php 
    $limit = get_option("mstore_limit_product");
    ?>
    <div class="form-group" style="margin-top:10px;margin-bottom:40px">
        <input type="number" value="<?=(!isset($limit) || $limit == false) ? 10 : $limit ?>" class="mstore-update-limit-product">
    </div>
</form>

<div class="thanks">
  <p style="font-size: 16px;">The server key firebase is used to push notification when order status changed.</p>
  <p style="font-size: 12px;">(Firebase project -> Project Settings -> Cloud Messaging -> Server key)</p>
</div>
<form action="" method="post">
    <?php 
    $serverKey = get_option("mstore_firebase_server_key");
    ?>
    <div class="form-group" style="margin-top:10px;margin-bottom:40px">
        <textarea class="mstore-update-firebase-server-key mstore_input" style="height: 120px"><?=$serverKey ?></textarea>
    </div>
</form>

<div class="thanks">
  <p style="font-size: 16px;">New Order Message</p>
</div>
<form action="" method="post">
    <?php 
    $newOrderTitle = get_option("mstore_new_order_title");
    if (!isset($newOrderTitle) || $newOrderTitle == false) {
      $newOrderTitle = "New Order";
    }
    $newOrderMsg = get_option("mstore_new_order_message");
    if(!isset($newOrderMsg) || $newOrderMsg == false){
      $newOrderMsg = "Hi {{name}}, Congratulations, you have received a new order! ";
    }
    ?>
    <div class="form-group" style="margin-top:10px;">
        <input type="text" placeholder="Title" value="<?=$newOrderTitle ?>" class="mstore-update-new-order-title mstore_input">
    </div>
    <div class="form-group" style="margin-top:10px;margin-bottom:40px">
        <textarea placeholder="Message" class="mstore-update-new-order-message mstore_input" style="height: 120px"><?=$newOrderMsg ?></textarea>
    </div>
</form>

<div class="thanks">
  <p style="font-size: 16px;">Order Status Changed Message</p>
</div>
<form action="" method="post">
    <?php 
    $statusOrderTitle = get_option("mstore_status_order_title");
    if (!isset($statusOrderTitle) || $statusOrderTitle == false) {
      $statusOrderTitle = "Order Status Changed";
    }
    $statusOrderMsg = get_option("mstore_status_order_message");
    if (!isset($statusOrderMsg) || $statusOrderMsg == false) {
      $statusOrderMsg = "Hi {{name}}, Your order: #{{orderId}} changed from {{prevStatus}} to {{nextStatus}}";
    }
    ?>
    <div class="form-group" style="margin-top:10px;">
        <input type="text" placeholder="Title" value="<?=$statusOrderTitle ?>" class="mstore-update-status-order-title mstore_input">
    </div>
    <div class="form-group" style="margin-top:10px;margin-bottom:40px">
        <textarea placeholder="Message" class="mstore-update-status-order-message mstore_input" style="height: 120px"><?=$statusOrderMsg ?></textarea>
    </div>
</form>

<div class="thanks">
  <p style="font-size: 16px;">This setting help to speed up the mobile app performance,  upload the config_xx.json from the common folder:</p>
</div>
<?php
  $uploads_dir   = wp_upload_dir();
  $folder = trailingslashit( $uploads_dir["basedir"] )."/2000/01";
  if (!file_exists($folder)) {
    mkdir($folder, 0777, true);
  }
  $configs = [];
  if(file_exists($folder)){
    $files = scandir($folder);
    foreach ($files as $file) {
      if (strpos($file, "config") > -1 && strpos($file, ".json") > -1) {
        $configs[] = $file;
      }
    }
  }
  if(!empty($configs)){
?>
<form action="" method="POST">
<table class="mstore_table">
  <tr>
    <th>File</th>
    <th>Download / Delete</th>
  </tr>
  <?php
  foreach ($configs as $file) {
    ?>
    <tr>
      <td><?=$file?></td>
      <td><a href="<?=$uploads_dir['baseurl']."/2000/01/".$file?>" target="_blank">Download</a> / <a data-id="<?=$file?>" class="mstore-delete-json-file">Delete</a></td>
    </tr>
    <?php
  }
  ?>
</table>
</form>
<?php
  }
?>
  <form action="" enctype="multipart/form-data" method="post">
  
    <div class="form-group" style="margin-top:30px">
        <input id="fileToUpload" accept=".json" name="fileToUpload" type="file" class="form-control-file">
    </div>
    
    <p style="font-size: 14px; color: #1B9D0D; margin-top:10px">
    <?php
    if (isset($_POST['but_submit'])) {     
      wp_upload_bits($_FILES['fileToUpload']['name'], null, file_get_contents($_FILES['fileToUpload']['tmp_name'])); 
      $upload_dir = $uploads_dir["basedir"];
      $source      = $_FILES['fileToUpload']['tmp_name'];
      $destination = trailingslashit( $upload_dir ) . '2000/01/'.$_FILES['fileToUpload']['name'];
      if (!file_exists($upload_dir."/2000/01")) {
        mkdir($upload_dir."/2000/01", 0777, true);
      }
      move_uploaded_file($source, $destination);
      echo "<script type='text/javascript'>
      location.reload();
        </script>";
    }
    ?>
    </p>

    <button type="submit" class="mstore_button" name='but_submit'>Save</button>
    </form>
<?php
}
?>
  </body>
</html>