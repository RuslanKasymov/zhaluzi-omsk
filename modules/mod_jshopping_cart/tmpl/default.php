<div id = "jshop_module_cart">
<table width = "100%" >
  <tr>
    <td colspan="3" align="center">
      <a class="in_cart" href = "<?php print SEFLink('index.php?option=com_jshopping&controller=cart&task=view', 1)?>"><?php print JText::_('Корзина')?></a>
    </td>
</tr>
<tr class="none_cart">
    <td>
      <span id = "jshop_quantity_products"><?php print $cart->count_product?></span>&nbsp;<?php print JText::_('')?>
    </td>
    <td></td>
    <td>
      <span id = "jshop_summ_product"><?php print formatprice($cart->getSum(0,1))?></span>
    </td>
</tr>
</table>
</div>