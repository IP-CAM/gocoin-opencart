<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/gocoinlogo.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_gocoinmerchant; ?></td>
            <td><input type="text" name="gocoin_gocoinmerchant" id="gocoin_gocoinmerchant" value="<?php echo $gocoin_gocoinmerchant; ?>" />
                <input type="hidden" name="cid" id="cid" value="<?php echo $gocoin_gocoinmerchant; ?>" />
              <?php if ($error_gocoinmerchant) { ?>
              <span class="error"><?php echo $error_gocoinmerchant; ?></span>
              <?php } ?>
               
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_gocoinsecretkey; ?></td>
            <td><input type="text" name="gocoin_gocoinsecretkey" id="gocoin_gocoinsecretkey" value="<?php echo $gocoin_gocoinsecretkey; ?>" />
              <input type="hidden" name="csec" id="csec" value="<?php echo $gocoin_gocoinsecretkey; ?>" />
                <?php if ($error_gocoinsecretkey) { ?>
              <span class="error"><?php echo $error_gocoinsecretkey; ?></span>
              <?php } ?></td>
          </tr>
          <tr>
            <td><?php echo $entry_gocointoken; ?></td>
            <td><input type="text" name="gocoin_gocointoken" value="<?php echo $gocoin_gocointoken; ?>" />
              <?php if ($error_gocointoken) { ?>
              <span class="error"><?php echo $error_gocointoken; ?></span>
              <?php } ?></td>
          </tr>       
          <tr>
            <td><?php echo $entry_order_status; ?></td>
            <td><select name="gocoin_order_status_id">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $gocoin_order_status_id) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_geo_zone; ?></td>
            <td><select name="gocoin_geo_zone_id">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $gocoin_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_status; ?></td>
            <td><select name="gocoin_status">
                <?php if ($gocoin_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
          </tr>
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="gocoin_sort_order" value="<?php echo $gocoin_sort_order; ?>" size="1" /></td>
          </tr>
          
          <tr>
            <td colspan='2'>
                  <div style="margin-top:5px;"> 
                    <span class="notice">you can click button to get access token from gocoin.com</span>
                    <button id="btn_get_token" title="Get API Token" class="scalable " onclick="get_api_token(); return false;" style="">
                        <span><span><span>Get API Token</span></span></span>
                    </button>
                </div>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
    <script type="text/javascript">
        var base = '<?php echo $base;?>';
        var mer_id = '<?php echo $entry_gocoinmerchant; ?>';
        var access_key = '<?php echo $entry_gocoinsecretkey; ?>';
        function get_api_token() 
        {
                var client_id       = document.getElementById('gocoin_gocoinmerchant').value;
                var client_secret   = document.getElementById('gocoin_gocoinsecretkey').value;
                if (!client_id) {
                    alert('Please input Client Id !');
                    return;
                }
                if (!client_secret) {
                   alert('Please input Secret Key !');
                    return;
                }
                var cid = document.getElementById('cid').value;
                var csec = document.getElementById('csec').value;
                
                if (client_id  != cid || client_secret != csec) {
                    alert('Please save changed Client Id and Client Secret Key first!');
                    return;
                }
                
                var currentUrl =  base+ 'index.php?route=payment/gocoin/gettoken';
                
                var url = "https://dashboard.gocoin.com/auth?response_type=code"
                            + "&client_id=" + client_id
                            + "&redirect_uri=" + currentUrl
                            + "&scope=user_read+merchant_read+invoice_read_write";
                          
                    
                var strWindowFeatures = "location=yes,height=570,width=520,scrollbars=yes,status=yes";
                var win = window.open(url, "_blank", strWindowFeatures);
                return;
            }
    </script>
<?php echo $footer; ?>