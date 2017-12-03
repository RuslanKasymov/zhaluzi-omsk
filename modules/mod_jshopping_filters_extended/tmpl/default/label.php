<?php if (is_array($listLabels) && count($listLabels)){?>  
    <div class="filter_labels<?php echo $span;?>">
    <input type="hidden" name="labels[]" value="0" /> 
    <?php     
    $html_head='<div class="head">'.JText::_('LABEL').":".'</div> ';
    $html_body =""; 
    if($show_labels=='1'){
        foreach($listLabels as $label){
                $disabled="" ; 
                if(!in_array($label->id,$labelInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
                if(!in_array($label->id,$labelInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
                if( $hide==0){
                    $checked="";
                    if (is_array($labels_active) && in_array($label->id, $labels_active)) $checked='checked="checked"' ;
                    $html_body .= '<div class="filter_item"><input type="checkbox" name="labels[]" value="'.$label->id.'" '.$checked.$disabled.' onclick="jshop_filters_submit('.$filter_number.');" /> '.$label->name.'</div>';
        }
    }
    }elseif($show_labels=='2') { 
        $_class = '';
        $multiple = '';
        if($use_select_chosen){
          $_class = 'class="chosen-select"';
          if($use_select_chosen_multiple){
            $multiple = 'multiple="multiple"';         
          }
        }
        $html_body .='<div class="filter_item"><select name="labels[]" data-placeholder="'.JText::_('JSELECT').'" style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');">';
        if($use_select_chosen && $use_select_chosen_multiple){
            $html_body .='<option value=""></option>';
        } else{
           $html_body .='<option value="">'.JText::_('JALL').'</option>'; 
        }
        foreach($listLabels as $label){
                $disabled="" ; 
                if(!in_array($label->id,$labelInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
                if(!in_array($label->id,$labelInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
                if( $hide==0){
                    $checked="";
                    if (is_array($labels_active) && in_array($label->id, $labels_active)) $checked='selected="selected"' ;
                    $html_body .= '<option  value="'.$label->id.'" '.$checked.$disabled.'>'.$label->name.'</option>';
         } 
        }
        $html_body .='</select></div>'; 
    }
    if ($html_body!='') {
        echo $html_head.$html_body;                          
    }
    ?>
    </div>
    <!--<div class="filter_space"></div>-->  
<?php $j++;} ?>   