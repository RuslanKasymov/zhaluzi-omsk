<?php if (is_array($filter_categorys) && count($filter_categorys)) {?>
<input type="hidden" name="categorys[]" value="0" />
<div class="box_category <?php echo $span;?>">
    <div class="head"> 
    <?php print JText::_('CATEGORY').":"?>
    </div>
    <?php if($show_categorys=='1'){?>
    <?php foreach($filter_categorys as $v){ ?>
    <div class="filter_item"><input type="checkbox" name="categorys[]" value="<?php print $v->id;?>" <?php if (in_array($v->id, $categorys)) print "checked";?> onclick="jshop_filters_submit(<?php print $filter_number?>);"> <?php print $v->name;?></div>
    <?php }?>
    <?php }elseif($show_categorys=='2') {?>
    <div class="filter_item">
    <?php
    $_class = '';
    $multiple = '';
    if($use_select_chosen){
      $_class = 'class="chosen-select"';
      if($use_select_chosen_multiple){
        $multiple = 'multiple="multiple"';
        $filter = $filter_categorys;           
      }else{
         $filter = array_merge($filter_all, $filter_categorys); 
      }
    }else{
        $filter = array_merge($filter_all, $filter_categorys);
    }    
    echo JHTML::_('select.genericlist', $filter, 'categorys[]', 'size = "1" data-placeholder="'.JText::_('JSELECT').'"style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');"','id', 'name', $categorys); ?>
    </div>
    <?php }?>        
</div>
<!--<div class="filter_space"></div>-->
<?php $j++;}?>
