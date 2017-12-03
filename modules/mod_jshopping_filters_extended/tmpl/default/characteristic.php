<?php if ($show_characteristics){?>
<?php if (is_array($characteristic_fields) && count($characteristic_fields)){?> 
<!--<div class="filter_space"></div>-->  
    <div class="filter_characteristic<?php echo $span;?>">
    <?php $html_char_name='<div class="head">'.JText::_('Product_Characteristics').":".'</div> '; $head_only_one='1';?>
    <?php $group_name='';  $html_head_group=""; $html_head=''; $html_body ="";
    foreach($characteristic_fields as $ch_id){

        if ($ch_id->groupname!='' && $ch_id->groupname!=$group_name){
            $group_name= $ch_id->groupname;    
            $html_head_group = '<div class="head group_name">'.$ch_id->groupname.'</div>';    
        }

        if ($ch_id->type=='0' && is_array($characteristic_fieldvalues[$ch_id->id]) && (in_array($ch_id->id, $show_characteristics_id) ||  in_array(0, $show_characteristics_id))){
            $html_head = '<div class="head_item">'.$ch_id->name.'</div> <input type="hidden" name="extra_fields['. $ch_id->id.'][]" value="0" /> ';
            $html_body ="";

            if($show_characteristics=='1') { 
            foreach($characteristic_fieldvalues[$ch_id->id] as $val_id=>$val_name){
                $disabled="" ; 
                if(!in_array($val_id,$characteristic_fieldvaluesInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
                if(!in_array($val_id,$characteristic_fieldvaluesInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
                if ($hide==0){
                   if (is_array($extra_fields_active[$ch_id->id]) && in_array($val_id, $extra_fields_active[$ch_id->id]))
                    $checked =' checked="checked" '; 
                   else
                    $checked="";
                   $html_body .=  '<div class="filter_item"><input type="checkbox" name="extra_fields['.$ch_id->id.'][]" value="'. $val_id.'" '.$disabled.$checked.'  onclick="jshop_filters_submit('.$filter_number.');" /> '.$val_name.'</div>';
                }
            }
            }elseif($show_characteristics=='2') {
                $_class = '';
                $multiple = '';
                if($use_select_chosen){
                  $_class = 'class="chosen-select"';
                  if($use_select_chosen_multiple){
                    $multiple = 'multiple="multiple"';         
                  }
                }                 
                $html_body .='<div class="filter_item"><select name="extra_fields['.$ch_id->id.'][]" data-placeholder="'.JText::_('JSELECT').'" style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');">';               
                if($use_select_chosen && $use_select_chosen_multiple){
                    $html_body .='<option value=""></option>';
                } else{
                   $html_body .='<option value="">'.JText::_('JALL').'</option>'; 
                } 
                foreach($characteristic_fieldvalues[$ch_id->id] as $val_id=>$val_name){
                    $disabled="" ; 
                    if(!in_array($val_id,$characteristic_fieldvaluesInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
                    if(!in_array($val_id,$characteristic_fieldvaluesInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
                    if ($hide==0){
                       if (is_array($extra_fields_active[$ch_id->id]) && in_array($val_id, $extra_fields_active[$ch_id->id]))
                        $checked =' selected="selected" '; 
                       else
                        $checked="";
                       $html_body .=  '<option  value="'. $val_id.'" '.$disabled.$checked.'>'.$val_name.'</option>';
                    }
                }                    
                $html_body .='</select></div>';
                if ($hide) $html_body = '';
            }

        }

        if ($ch_id->type=='1' && (in_array($ch_id->id,$show_characteristics_id) ||  in_array(0,$show_characteristics_id))){
            $html_body =""; 
            $disabled="" ; 
            if (!in_array($ch_id->id,$characteristic_fieldtextInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
            if (!in_array($ch_id->id,$characteristic_fieldtextInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0;                    
            if( $hide==0){ 
                $html_head = '<div class="head_item">'. $ch_id->name . '</div>'; 
                $html_body .= '<div class="filter_item"><input type="text" size="15" name="extra_fields['.$ch_id->id.']" value="'.htmlspecialchars($extra_fields_active[$ch_id->id], ENT_QUOTES).'" '.$disabled.'  />';
                if($show_characteristics_button){
                    $html_body .= '<button class="btn-go" onclick="submit();">'.JText::_('GO').'</button>';
                }
                $html_body .= '</div>';
            }
        }

        if ($html_body!='' && $head_only_one=='1'){
               echo $html_char_name;  
               $head_only_one='0';
        }

        if ($html_head_group!='' && $html_body) {
              echo $html_head_group;
             $html_head_group=""; 
        }

        if ($html_body!='') {
              echo $html_head.$html_body;   
              $html_head="";  
              $html_body="";                        
        }
    }
    ?>
    </div>
<!--    <div class="filter_space"></div>-->
    <?php } ?> 
<?php $j++;} ?>  