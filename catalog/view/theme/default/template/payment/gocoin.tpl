<form action="<?php echo $action; ?>" method="post">
	
  <input type="hidden" name="total" value="<?php echo $total; ?>" />
  <input type="hidden" name="cart_order_id" value="<?php echo $cart_order_id; ?>" />
  <input type="hidden" name="card_holder_name" value="<?php echo $card_holder_name; ?>" />
  <input type="hidden" name="street_address" value="<?php echo $street_address; ?>" />
  <input type="hidden" name="city" value="<?php echo $city; ?>" />
  <input type="hidden" name="state" value="<?php echo $state; ?>" />
  <input type="hidden" name="zip" value="<?php echo $zip; ?>" />
  <input type="hidden" name="country" value="<?php echo $country; ?>" />
  <input type="hidden" name="email" value="<?php echo $email; ?>" />
  <input type="hidden" name="phone" value="<?php echo $phone; ?>" />
  <input type="hidden" name="ship_street_address" value="<?php echo $ship_street_address; ?>" />
  <input type="hidden" name="ship_city" value="<?php echo $ship_city; ?>" />
  <input type="hidden" name="ship_state" value="<?php echo $ship_state; ?>" />
  <input type="hidden" name="ship_zip" value="<?php echo $ship_zip; ?>" />
  <input type="hidden" name="ship_country" value="<?php echo $ship_country; ?>" />
  <?php $i = 0; ?>
  <?php foreach ($products as $product) { ?>
  <input type="hidden" name="c_prod_<?php echo $i; ?>" value="<?php echo $product['product_id']; ?>,<?php echo $product['quantity']; ?>" />
  <input type="hidden" name="c_name_<?php echo $i; ?>" value="<?php echo $product['name']; ?>" />
  <input type="hidden" name="c_description_<?php echo $i; ?>" value="<?php echo $product['description']; ?>" />
  <input type="hidden" name="c_price_<?php echo $i; ?>" value="<?php echo $product['price']; ?>" />
  <?php $i++; ?>
  <?php } ?>
  <input type="hidden" name="id_type" value="1" />
  <?php if ($demo) { ?>
  <input type="hidden" name="demo" value="<?php echo $demo; ?>" />
  <?php } ?>
  <input type="hidden" name="lang" value="<?php echo $lang; ?>" />
  
  <div class="buttons">
    <div class="right">
       <div id="payment_result_loading"></div> 
      <input type="button" id="button-confirm" value="<?php echo $button_confirm; ?>" class="button" />
    </div>
  </div>
</form>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({
		url: 'index.php?route=payment/gocoin/processorder',
		type: 'post',
		data: $('#payment :input'),
		dataType: 'json',		
		beforeSend: function() {
			$('#button-confirm').attr('disabled', true);
			$('#payment_result_loading').html('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> Loading...</div>');
		},
		complete: function() {
			$('#button-confirm').attr('disabled', false);
			$('.attention').remove();
            $('#payment_result_loading').html('');
		},
        success: function(json) {
			if (json['error']) {
				alert(json['error']);
			}
			else if (json['success']) {
				window.location = json['success'];
			}
		}
	});
});
//--></script>