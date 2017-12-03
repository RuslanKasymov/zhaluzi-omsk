<?php 
/**
* @version      4.9.1 13.08.2013
* @author       MAXXmarketing GmbH
* @package      Jshopping
* @copyright    Copyright (C) 2010 webdesigner-profi.de. All rights reserved.
* @license      GNU/GPL
*/
defined('_JEXEC') or die('Restricted access');
?>
<div class="jshop list_product" id="comjshop_list_product">
<?php print $this->_tmp_list_products_html_start?>
<?php foreach ($this->rows as $k=>$product) : ?>
    <?php if ($k % $this->count_product_to_row == 0) : ?>
        <div class = "row-fluid clearfix">
    <?php endif; ?>
    
    <div class = "sblock<?php echo $this->count_product_to_row;?>">
        <div class = "block_product clearfix">
            <?php include(dirname(__FILE__)."/".$product->template_block_product);?>
        </div>
    </div>
            
    <?php if ($k % $this->count_product_to_row == $this->count_product_to_row - 1) : ?>
        <div class = "clearfix"></div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($k % $this->count_product_to_row != $this->count_product_to_row - 1) : ?>
    <div class = "clearfix"></div>
    </div>
<?php endif; ?>
<?php print $this->_tmp_list_products_html_end;?>
</div>

<script>
    jQuery(function(){
        jQuery('input[name="quantity"]').bind("keyup", changeQuantity);
        jQuery('input[name="quantity"]').bind("change", changeQuantity);
        //jQuery('input[name="quantity"]').(function changeQuantity());

        function changeQuantity(){
            if (this.value.match(/[^0-9]/g)) {
                this.value = this.value.replace(/[^0-9]/g, "");
            };
            var qVal = parseInt(jQuery(this).val());
            console.log(qVal+" qVal");
            var initBlock = jQuery(this).parents(".oiproduct");
            var minVal = parseInt(jQuery(".min_quantity", initBlock).val());
            console.log(minVal+" minVal");
            if ( (typeof qVal !=="undefined") && ( qVal !="") && ( qVal >= minVal) ) {
                jQuery(".addtocart_button", initBlock).removeAttr("disabled");
                console.log("больше");
            } else {
                jQuery(".addtocart_button", initBlock).attr("disabled","disabled");
                console.log("меньше");
            };
        };
    });
</script>