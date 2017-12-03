<?php if ($show_quantity){?>  
<div class="quantity_filter<?php echo $span;?>">
        <div class="head">
    <?php print JText::_('AVAILABILITY').":"?>
    </div>
    <div class="filter_item"><input type="radio" name="quantity_filter" value="0" <?php if ($quantity_filter == '0' || !($quantity_filter)) print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('All')?></div> 
    <div class="filter_item"><input type="radio" name="quantity_filter" value="1" <?php if ($quantity_filter == '1') print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('In_stock')?></div> 
    <div class="filter_item"><input type="radio" name="quantity_filter" value="2" <?php if ($quantity_filter == '2') print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('UNAVAILABLE')?></div> 
</div>
 <!--<div class="filter_space"></div>-->  
<?php $j++;} ?> 
