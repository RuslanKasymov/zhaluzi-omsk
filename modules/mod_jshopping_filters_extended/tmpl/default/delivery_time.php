<?php if (is_array($listDeliveryTimes) && count($listDeliveryTimes)){?>    
    <div class="filter_delivery<?php echo $span;?>">
     <input type="hidden" name="delivery_times[]" value="0" /> 
    <?php 
    $html_head='<div class="head">'.JText::_('Delivery_Time').":".'</div> ';
    $html_body ="";    

    if($show_delivery_time=='1'){
    foreach($listDeliveryTimes as $delivery_times){
        $disabled="" ; 
        if(!in_array($delivery_times->id,$delivery_timeInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
        if(!in_array($delivery_times->id,$delivery_timeInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
        if( $hide==0){
            $checked="";
            if (is_array($delivery_time_active) && in_array($delivery_times->id, $delivery_time_active)) $checked='checked="checked"' ;
            $html_body .= '<div class="filter_item"><input type="checkbox" name="delivery_times[]" value="'.$delivery_times->id.'" '.$checked.$disabled.' onclick="jshop_filters_submit('.$filter_number.');" /> '.$delivery_times->name.'</div>';                       
        }
    }

    }elseif($show_delivery_time=='2') {
        $_class = '';
        $multiple = '';
        if($use_select_chosen){
          $_class = 'class="chosen-select"';
          if($use_select_chosen_multiple){
            $multiple = 'multiple="multiple"';         
          }
        }         
        $html_body .='<div class="filter_item"><select name="delivery_times[]" data-placeholder="'.JText::_('JSELECT').'"style="width:100%;" '.$_class.' '.$multiple.' onchange="jshop_filters_submit('.$filter_number.');">';      
        if($use_select_chosen && $use_select_chosen_multiple){
            $html_body .='<option value=""></option>';
        } else{
           $html_body .='<option value="">'.JText::_('JALL').'</option>'; 
        } 
        foreach($listDeliveryTimes as $delivery_times){
            $disabled="" ; 
            if(!in_array($delivery_times->id,$delivery_timeInProducts) && $display_unavailable_value=='1') $disabled=' disabled="disabled" ';   
            if(!in_array($delivery_times->id,$delivery_timeInProducts) && $display_unavailable_value=='0') $hide=1; else $hide=0; 
            if( $hide==0){
                $checked="";
                if (is_array($delivery_time_active) && in_array($delivery_times->id, $delivery_time_active)) $checked='selected="selected"' ;
                $html_body .= '<option value="'.$delivery_times->id.'" '.$checked.$disabled.'>'.$delivery_times->name.'</option>';                       
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
<?php
    $j++;} 
?>    
