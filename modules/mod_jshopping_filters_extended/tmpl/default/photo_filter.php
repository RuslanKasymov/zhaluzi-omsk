<?php if ($show_photo_filter){?>  
<div class="photo_filter<?php echo $span;?>">
        <div class="head">
    <?php print JText::_('Photo_filter').":"?>
    </div>
    <div class="filter_item"><input type="radio" name="photo_filter" value="0" <?php if ($photo_filter == '0' || !($quantity_filter)) print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('All')?></div> 
    <div class="filter_item"><input type="radio" name="photo_filter" value="1" <?php if ($photo_filter == '1') print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('With_photo')?></div> 
    <div class="filter_item"><input type="radio" name="photo_filter" value="2" <?php if ($photo_filter == '2') print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print JText::_('Without_photo')?></div> 
</div>
 <!--<div class="filter_space"></div>-->  
<?php $j++;} ?> 