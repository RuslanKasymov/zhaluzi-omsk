<?php if ($show_attributes){?>  
 <?php if (is_array($listAttribut) && count($listAttribut)){ ?>    
    <div class="filter_attr<?php echo $span;?>">
     <input type="hidden" name="attr_val[]" value="0" /> 
     <?php $html_char_name='<div class="head">'.JText::_('Product_attributes').":".'</div> '; $head_only_one='1';?>  

    <?php foreach($listAttribut as $attr){
            $html_head = '<div class="head_item" >'.$attr->name.'</div>';
            $html_body=""; 
            if($show_attributes=='1'){
            foreach ($attr->values as $attr_values) {
                $disabled="";
                if (!in_array($attr_values->value_id,$attributvaluesInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';  
                if (!in_array($attr_values->value_id,$attributvaluesInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0;
                if ($hide==0){
                    if (is_array($attribut_active) && in_array($attr_values->value_id, $attribut_active)) $checked=' checked="checked" '; else $checked="";
                    if (in_array($attr->attr_id, $show_attribute_image) || in_array('0', $show_attribute_image)) $attr_img = ' <img src="'.$jshopConfig->image_attributes_live_path.'/'.$attr_values->image.'" alt="" />';
                    else $attr_img="";
                    $html_body.='<div class="filter_item"><input type="checkbox" name="attr_val[]" value="'.$attr_values->value_id.'" '.$checked.$disabled.' onclick="jshop_filters_submit('.$filter_number.');" />'.$attr_img.' '.$attr_values->name.'</div>';
                } 
            }
            }elseif($show_attributes=='2') { 
                $_class = '';
                $multiple = '';
                if($use_select_chosen){
                  $_class = 'class="chosen-select"';
                  if($use_select_chosen_multiple){
                    $multiple = 'multiple="multiple"';         
                  }
                }  
                $html_body .='<div class="filter_item"><select name="attr_val[]" data-placeholder="'.JText::_('JSELECT').'"style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');">';
                if($use_select_chosen && $use_select_chosen_multiple){
                    $html_body .='<option value=""></option>';
                } else{
                   $html_body .='<option value="">'.JText::_('JALL').'</option>'; 
                }                      
                foreach ($attr->values as $attr_values) {  
                    $disabled="";
                    if(!in_array($attr_values->value_id,$attributvaluesInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';  
                    if(!in_array($attr_values->value_id,$attributvaluesInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0;
                    if( $hide==0){ 
                                if (is_array($attribut_active) && in_array($attr_values->value_id, $attribut_active)) $checked=' selected="selected" '; else $checked="";
                                $html_body.='<option value="'.$attr_values->value_id.'" '.$checked.$disabled.'>'.$attr_values->name.'</option>';
                    } 
                }                    
                $html_body .='</select></div>'; 

            }
              if ($html_body!='' && $head_only_one=='1'){
                   echo $html_char_name;  
                   $head_only_one='0';
              }                
              if ($html_body!='') echo $html_head.$html_body;
    }?>
    </div>
    <!--<div class="filter_space"></div>-->
<?php } ?>   
 <?php $j++;} ?>   