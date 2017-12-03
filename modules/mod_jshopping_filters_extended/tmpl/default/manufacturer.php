<?php if (is_array($filter_manufactures) && count($filter_manufactures)) {?>
<div class="box_manufacrurer<?php echo $span;?>">
    <div class="head">
    <?php print JText::_('MANUFACTURER').":"?>
    </div>
    <?php if($show_manufacturers=='1'){?>
    <?php foreach($filter_manufactures as $v){ ?>
    <div class="filter_item"><input type="checkbox" name="manufacturers[]" value="<?php print $v->id;?>" <?php if (in_array($v->id, $manufacturers)) print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print $v->name;?></div>
    <?php }?>
    <?php }elseif($show_manufacturers=='2') {?>
    <div class="filter_item">
    <?php
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
    echo JHTML::_('select.genericlist', $filter, 'manufacturers[]', 'size = "1" style="width:100%;" data-placeholder="'.JText::_('JSELECT').'" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');"','id', 'name', $manufacturers); ?>
    </div>
    <?php }?>
    <input type="hidden" name="manufacturers[]" value="0" />
</div>
<!--<div class="filter_space"></div>-->  
<?php $j++;}?>

