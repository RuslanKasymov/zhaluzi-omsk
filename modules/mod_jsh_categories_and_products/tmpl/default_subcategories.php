<?php if($category->subcategories) { ?>
            <ul class="categories <?php echo 'sub'.$category->category_id ?>" <?php echo $category_ul ?>>
				<?php  foreach($category->subcategories as $category): ?>
                
                <?php $category_class = 'category';
				if (count($category->subcategories)  or (count($category->products) && $params->get('products') == 1)) {
					$category_class .= ' parent';
				}
				if ($category->category_link == JUri::getInstance()->toString(array('path', 'query'))) {
					$category_class .= ' current';
				}
				if (in_array($category->category_id, $categories_id)) { 
					$indicator='<span onClick=\'return toggleShow(".sub'.$category->category_id.'" ,this)\' class="open"></span>';
					$category_ul='';
					$category_class .= ' active';
					$indicator_class=' class="open"';
				} else { 
        			$indicator='<span onClick=\'return toggleShow(".sub'.$category->category_id.'" ,this)\' class="closed"></span>';
					$category_ul='style="display:none"';
					$indicator_class=' class="closed"';
				} 
				if ($params->get('accordion') == 0) {
					$indicator='';
					$category_ul='';
					$indicator_class='';
				} else {
					$click_name='';
					if ($params->get('click_name') == 1){
						$indicator='';
						$click_name=' onClick=\'return toggleShow(".sub'.$category->category_id.'" ,this)\'';						
					} else {
						$indicator_class='';
					}
				}
	?>
                
                <li class="<?php echo $category_class; ?>"> 
                	<?php if (count($category->subcategories)  or (count($category->products) && $params->get('products') == 1)){ ?>
        				<?php echo $indicator ?>
        			<?php } else {
						$indicator_class='';
						$click_name='';
					} ?>
					<a<?php echo $click_name.$indicator_class; ?> href="<?php echo $category->category_link?>">
						<?php echo $category->name; ?>
                        <?php if ($show_image && $category->category_image){?>
                    <img src = "<?php print $jshopConfig->image_category_live_path."/".$category->category_image?>" alt = "<?php print $category->name?>" />
                <?php } ?>
                	<?php if ($params->get('counter') == 1 && $category->products) { ?>
                        <span class="counter">(<?php echo count($category->products); ?>)</span>
                    <?php } ?>
                    </a>
                    <?php if($category->products && $params->get('products') == 1) { ?>
                		<ul class="products <?php echo 'sub'.$category->category_id ?>" <?php echo $category_ul ?>>
						<?php foreach($category->products as $product): ?>
                        	<?php 
							$product_class = '';
							$product_name = $product->name;
							if ($product->product_link == JUri::getInstance()->toString(array('path', 'query'))) {
								$product_class = 'current';
							} 
                            if ($params->get('ean') == 1 && $product->product_ean) {
								$product_name = $product->product_ean;
							}
		 					?>                          
							<li class="product-<?php echo $product->product_id; ?> <?php echo $product_class; ?>"> 
								<a href="<?php echo $product->product_link?>">
                                    <?php echo $product_name; ?>
                                	<?php if ($show_product_image && $product->image){?>
                   						<img src = "<?php echo $product->image?>" alt = "<?php print $product->name?>" />
                					<?php } ?>
                    			</a>
                        	</li>
                        <?php endforeach ?>
                    </ul>
                    <?php } ?>
					<?php require JModuleHelper::getLayoutPath('mod_jsh_categories_and_products', $params->get('layout', 'default').'_subcategories'); ?> 
                </li>
				<?php endforeach; ?> 
            </ul>
<?php } ?>


                  

