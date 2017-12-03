jQuery(document).ready(function() {
    jQuery('.dd').nestable({ 
        maxDepth : 1        
    }).on('change', function() {
        var params = [];
        var nest = jQuery('.dd').nestable('serialize');
        for (var i=0; i<nest.length;i++){
           params.push(nest[i]['id']); 
        }
        jQuery('#jform_params_filter_order').val(params.join());
  });
});