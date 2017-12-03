<script type="text/javascript">
function jshop_filters_submit(n){
    jQuery('#jshop_filters_'+n).submit();
}
function modFilterclearPriceFilter(n){
    jQuery("#price_from").val("");
    jQuery("#price_to").val("");    
    jshop_filters_submit(n);
}
function setFilterPrice(pricef, pricet, n){
    jQuery("#price_from").val(pricef);
    jQuery("#price_to").val(pricet);
    jshop_filters_submit(n);
}
function modFilterclearAll(n){
    jQuery("#price_from").val("");
    jQuery("#price_to").val("");
    var c = new Array();
    c = document.forms['jshop_filters_'+n].getElementsByTagName('input');
    for (var i = 0; i < c.length; i++){
        if (c[i].type == 'checkbox')
        {
          c[i].checked = false;
        }
        if (c[i].type == 'radio')
        {
          c[i].value = '0';
        }   
        if (c[i].type == 'text' )
        {
          c[i].value = '';
        }      
    }
    cs = document.forms['jshop_filters_'+n].getElementsByTagName('select');
    for (var i = 0; i < cs.length; i++){
        cs[i].value = '';
    }    
    jshop_filters_submit(n);
}
</script>
<?php 
$filter_all[] = JHTML::_('select.option',  '', JText::_(JALL), 'id', 'name' );
if (!isset($GLOBALS['filter_number'])){
    $GLOBALS['filter_number'] = 1;
    $filter_number = $GLOBALS['filter_number'];
}else{
    $GLOBALS['filter_number']++;
    $filter_number = $GLOBALS['filter_number'];
}
?>
<div class="jshop_filters">
<form action="<?php echo $form_action;?>" method="post" name="jshop_filters_<?php print $filter_number?>" id="jshop_filters_<?php print $filter_number?>">
    <div class="row controls controls_top">
        <button class="btn-go" onclick="submit();"><?php print JText::_('GO')?></button>
        <button class="clear_filter_mod" onclick="modFilterclearAll(<?php print $filter_number?>);return false;"><?php print JText::_('RESET_FILTER')?></button>
        </div>
    <div class="row">
    <?php 
    $folder = dirname(__FILE__).'/default/';
    $j=0;
    foreach ($filter_order as $v_order) {
        if ($show_horizontal && $j==$columns_count){
               $j=0;?>
            </div><div class="row">   
        <?php }
        include $folder.$v_order.'.php';
    }
    ?>            
    </div>    
    <div class="row controls controls_bottom">
        <button class="btn-go" onclick="submit();"><?php print JText::_('GO')?></button>
        <button class="clear_filter_mod" onclick="modFilterclearAll(<?php print $filter_number?>);return false;"><?php print JText::_('RESET_FILTER')?></button>
    </div>          
</form>
</div>
