<?php if (is_array($filter_vendors) && count($filter_vendors)) {?>
<input type="hidden" name="vendors[]" value="0" />
<div class="box_vendors <?php echo $span;?>">
    <div class="head"> 
    <?php print JText::_('VENDORS').":"?>
    </div>
    <?php if($show_vendors=='1'){?>
    <?php foreach($filter_vendors as $v){ ?>
    <div class="filter_item"><input type="checkbox" name="vendors[]" value="<?php print $v->id;?>" <?php if (in_array($v->id, $vendors)) print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print $v->shop_name;?></div>
    <?php }?>
    <?php }elseif($show_vendors=='2') {?>
    <div class="filter_item">
    <?php 
    $text_all = JText::_('JALL');
    if($use_select_chosen && $use_select_chosen_multiple){
        $text_all = '';
    }
    $_class = '';
    $multiple = '';
    if($use_select_chosen){
      $_class = 'class="chosen-select"';
      if($use_select_chosen_multiple){
        $multiple = 'multiple="multiple"';
        $filter = $filter_manufactures;           
      }else{
         $filter = array_merge($filter_all, $filter_manufactures); 
      }
    }else{
        $filter = array_merge($filter_all, $filter_manufactures);
    } 
    $filter_all_vendors[] = JHTML::_('select.option',  '', $text_all, 'id', 'shop_name' );
    $filter = array_merge($filter_all_vendors, $filter_vendors);
    echo JHTML::_('select.genericlist', $filter, 'vendors[]', 'size = "1" data-placeholder="'.JText::_('JSELECT').'"style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');"','id', 'shop_name', $vendors); ?>
    </div>
    <?php }?>         
</div>
<!--<div class="filter_space"></div>-->
<?php $j++;}?>    