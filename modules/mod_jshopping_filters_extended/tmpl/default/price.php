<?php if (count($prices_list)){?>
    <div class="price_head<?php echo $span;?>">
        <a href="#" onclick="modFilterclearPriceFilter(<?php print $filter_number?>);"><?php print JText::_('Any_price')?></a>
        <?php foreach($prices_list as $pricesx){?>
            <div class="filter_price_between">
                <?php
                if ($pricesx[0]==$fprice_from && $pricesx[1]==$fprice_to){
                    $style_price_select = "active";
                }else{
                    $style_price_select = "";
                }
                ?>
                <a href="#" class="<?php print $style_price_select?>" onclick="setFilterPrice('<?php print $pricesx[0]?>','<?php print $pricesx[1]?>', <?php print $filter_number?>);"><?php print $pricesx[0]?> - <?php print $pricesx[1]?> <?php print $jshopConfig->currency_code?></a>
            </div>
        <?php } ?>
    </div>
<?php $j++; }?>


<?php if ($show_prices ){?>
<div class="show_prices<?php echo $span;?>">
    <span class="filter_price">
    <div class="head"><?php print JText::_('PRICE').' ('.$jshopConfig->currency_code.')'?>:</div>   
        <span class="box_price_from"><?php print JText::_('FROM')?> <input type = "text" class = "inputbox" name = "fprice_from" id="price_from" size="7" value="<?php if ($fprice_from>0) print $fprice_from?>" /></span>
        <span class="box_price_to"><?php print JText::_('TO')?> <input type = "text" class = "inputbox" name = "fprice_to"  id="price_to" size="7" value="<?php if ($fprice_to>0) print $fprice_to?>" /></span>
    </span>
    <?php if($show_price_button) {?>
        <button class="btn-go" onclick="submit();"><?php print JText::_('GO')?></button>
    <?php }?>
    </div>
<?php $j++;}else{?>
    <input type = "hidden" name = "fprice_from" id="price_from" size="7" value="<?php if ($fprice_from>0) print $fprice_from?>" />
    <input type = "hidden" name = "fprice_to"  id="price_to" size="7" value="<?php if ($fprice_to>0) print $fprice_to?>" />
<?php }?> 

<?php if ($show_prices_slider && $maxminPrices[count]>1){?>
    <div class="show_prices<?php echo $span;?>">
    <div class="head"><?php print JText::_('PRICE')?>:</div>
    <div id="amount"></div>
    <div id="slider-range"></div>
    </div>
<?php $j++;}?>    