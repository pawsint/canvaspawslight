<?php
// use page meta fields if page
function show_custom_page_meta_box(){
	global $custom_page_meta_fields,$custom_meta_fields;
	$custom_meta_fields=$custom_page_meta_fields;
	show_custom_meta_box();
}

// use post meta fields if post
function show_custom_post_meta_box(){
	global $custom_post_meta_fields,$custom_meta_fields;
	$custom_meta_fields=$custom_post_meta_fields;
	show_custom_meta_box();
}

// add some custom js to the head of the page
function add_custom_scripts() {
	global $custom_meta_fields, $post;
	if(!isset($_GET["page"])&& !isset($_GET['type'])){
		//wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-ui-slider');
		//wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/functions/page_options/page-options.js');
		//wp_enqueue_script('custom-js-page', get_stylesheet_directory_uri() . '/functions/page_options/page-options-custom.js');
	}
	$output = '<script type="text/javascript">
				jQuery(function() {';
	
	foreach ($custom_meta_fields as $field) { 

		if ($field['type'] == 'slider') {
			$value = get_post_meta($post->ID, $field['id'], true);
			if ($value == '') $value = $field['min'];
			$output .= '
					jQuery( "#'.$field['id'].'-slider" ).slider({
						value: '.$value.',
						min: '.$field['min'].',
						max: '.$field['max'].',
						step: '.$field['step'].',
						slide: function( event, ui ) {
							jQuery( "#'.$field['id'].'" ).val( ui.value );
						}
					});';
		}
	}
	
	$output .= '});';
		
	//echo $output;
}

add_action('admin_head','add_custom_scripts');

// The Callback
function show_custom_meta_box() {
	global $custom_meta_fields,$post;
	// Use nonce for verification
	echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	
	// Begin the field table and loop
	echo '<table class="form-table">';
	foreach ($custom_meta_fields as $field) {
		// get value of this field if it exists for this post
		$meta = get_post_meta($post->ID, $field['id'], true);
		// begin a table row with
		echo '<tr class="'.$field['class'].'">
				<th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
				';
				switch($field['type']) {
					//description
					case 'desc':
						echo '<td colspan=2><span class="description">'.$field['desc'].'</span></td>';
					break;
					// text
					case 'text':
						echo '<td><input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" /></td>
								<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// textarea
					case 'textarea':
						echo '<td><textarea name="'.$field['id'].'" id="'.$field['id'].'" cols="60" rows="4">'.$meta.'</textarea></td>
								<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// checkbox
					case 'checkbox':
						echo '<td><input type="checkbox" name="'.$field['id'].'" id="'.$field['id'].'" ',$meta ? ' checked="checked"' : '','/>
								<label for="'.$field['id'].'">'.$field['text'].'</label>
								</td><td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// select
					case 'select':
						echo '<td><select name="'.$field['id'].'" id="'.$field['id'].'">';
						foreach ($field['options'] as $option) {
							echo '<option', $meta == $option['value'] ? ' selected="selected"' : '', ' value="'.$option['value'].'">'.$option['label'].'</option>';
						}
						echo '</select>
							</td><td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// radio
					case 'radio':
						echo '<td>';
						foreach ( $field['options'] as $option ) {
							if ($meta=="") $meta=$field['default'];
							echo '<input type="radio" name="'.$field['id'].'" id="'.$field['id']."_".$option['value'].'" value="'.$option['value'].'" ',$meta == $option['value'] ? ' checked="checked"' : '',' />
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '</td><td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// checkbox_group
					case 'checkbox_group':
						foreach ($field['options'] as $option) {
							echo '<td><input type="checkbox" value="'.$option['value'].'" name="'.$field['id'].'[]" id="'.$option['value'].'"',$meta && in_array($option['value'], $meta) ? ' checked="checked"' : '',' /> 
									<label for="'.$option['value'].'">'.$option['label'].'</label><br />';
						}
						echo '</td><td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// tax_select
					case 'tax_select':
						echo '<td><select name="'.$field['id'].'" id="'.$field['id'].'">
								<option value="">Select One</option>'; // Select One
						$terms = get_terms($field['id'], 'get=all');
						$selected = wp_get_object_terms($post->ID, $field['id']);
						foreach ($terms as $term) {
							if (!empty($selected) && !strcmp($term->slug, $selected[0]->slug)) 
								echo '<option value="'.$term->slug.'" selected="selected">'.$term->name.'</option>'; 
							else
								echo '<option value="'.$term->slug.'">'.$term->name.'</option>'; 
						}
						$taxonomy = get_taxonomy($field['id']);
						echo '</select></td>
						<td><span class="description"><a href="'.get_bloginfo('home').'/wp-admin/edit-tags.php?taxonomy='.$field['id'].'">Manage '.$taxonomy->label.'</a></span></td>';
					break;
					// post_list
					case 'post_list':
					$items = get_posts( array (
						'post_type'	=> $field['post_type'],
						'posts_per_page' => -1
					));
						echo '<td><select name="'.$field['id'].'" id="'.$field['id'].'">
								<option value="">Select One</option>'; // Select One
							foreach($items as $item) {
								echo '<option value="'.$item->ID.'"',$meta == $item->ID ? ' selected="selected"' : '','>'.$item->post_type.': '.$item->post_title.'</option>';
							} // end foreach
						echo '</select></td>
							<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// unlimited sidebars
					case 'sidebar_list':
						global $wp_registered_sidebars;
					    if( empty( $wp_registered_sidebars ) )
					        return;
					    $name = $field['id'];
					    $current = ( $meta ) ? esc_attr( $meta ) : false;     
					    $selected = '';
					    echo "<td><select name='$name'>";
					    foreach( $wp_registered_sidebars as $sidebar ) : 
					        if( $current ) 
					            if($sidebar['name'] == $current)
					            	$selected = "selected";
					            else 
					            	$selected = "";
					        echo "<option value='".$sidebar['name']."' $selected>";
					        echo $sidebar['name'];
					    	echo "</option>";
					    endforeach;
					    echo "</select></td>";
						echo '<td><span class="description">'.$field['desc'].'</span></td>';
					break;  
					case 'menu_list':
						$menus = get_terms('nav_menu');
						$name = $field['id'];
					    $current = ( $meta ) ? esc_attr( $meta ) : false;     
					    $selected = '';
					    echo "<td><select name='$name'>";
					    echo "<!--option value=''>No Menu</option--><option value=''>Default Menu</option>";
					    foreach($menus as $menu) : 
					        if( $current ) 
					            if($menu->slug == $current)
					            	$selected = "selected";
					            else 
					            	$selected = "";
					        echo "<option value='".$menu->slug."' $selected>";
					        echo $menu->name;
					    	echo "</option>";
					    endforeach;
					    echo "</select></td>";
						echo '<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					case 'slider_list':
						echo '<td><select name="'.$field['id'].'" id="'.$field['id'].'">';
                		
                		global $wpdb;
                		global $table_prefix;
                		if (!isset($wpdb->tablename)) {
							$wpdb->tablename = $table_prefix . 'revslider_sliders';
						}
                		$revolution_sliders = $wpdb->get_results( 
							"
							SELECT title,alias 
							FROM $wpdb->tablename
							"
						);
					foreach ( $revolution_sliders as $revolution_slider ) 
					{
						$checked="";
            		 	if($revolution_slider->alias==$meta) $checked="selected";
            		 	echo "<option value='$revolution_slider->alias' $checked>".$revolution_slider->title."</option>";
					}
                	echo '</select>';
					break;
					// unlimited portfolios
					case 'portfolio_list':
						echo '<td><select name="'.$field['id'].'" id="'.$field['id'].'">';
                		$portfolio_counter = 0;
                		
	                	$portfolios = get_option("paws_theme_portfolios_options");
						$portfolio_slugs = array();
						$portfolio_name = array();
						$j = 1;
						if(is_array($portfolios)){
							foreach($portfolios as $key => $value){
								if($j%2==0){
						            array_push($portfolio_slugs,$value);
						            $j = 0 ;
						        }
						        else{
						            array_push($portfolio_name,$value);
						        }
						    	$j++;
							}
	                	}
	                		foreach ( $portfolio_slugs as $slug ){
	                			$checked="";
	                		 	if($slug==$meta) $checked="selected";
	                		 	echo "<option value='$slug' $checked>".$portfolio_name[$portfolio_counter++]."</option>";
	                		} 
                		echo '</select>';
					break;
					// date
					case 'date':
						echo '<td><input type="text" class="datepicker" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$meta.'" size="30" /></td>
								<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// slider
					case 'slider':
					$value = $meta != '' ? $meta : '0';
						echo '<td><div id="'.$field['id'].'-slider"></div>
								<input type="text" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$value.'" size="5" /></td>
								<td><span class="description">'.$field['desc'].'</span></td>';
					break;
					// image
					case 'image':
						$image_def = get_stylesheet_directory_uri().'/img/tiles/more.png';	
						$style = "margin-top:10px;";
						if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium');	$image = $image[0]; }	
						else {
							$image = $image_def;
							$style = "display:none";
						};			
						echo '<td><span class="custom_default_image" style="display:none">'.$image_def.'</span><a href="#" class="custom_media_upload button">Choose Image</a>&nbsp;<small>&nbsp;<a href="#" class="custom_clear_image_button">Remove Image</a></small>
								<img style="max-width:300px;'.$style.'" class="custom_media_image" src="'.$image.'" />
								<input class="custom_media_url" type="hidden" name="attachment_url" value="'.$image.'">
								<input class="custom_media_id" type="hidden" name="'.$field['id'].'" value="'.$meta.'"><br clear="all" /></td><td valign="top"><span class="description">'.$field['desc'].'</span></td>';
					break;
					// repeatable
					case 'repeatable':
						echo '<td><a class="repeatable-add button" href="#">+</a>
								<ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';
						$i = 0;
						if ($meta) {
							foreach($meta as $row) {
								echo '<li><span class="sort hndle">|||</span>
											<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$row.'" size="30" />
											<a class="repeatable-remove button" href="#">-</a></li>';
								$i++;
							}
						} else {
							echo '<li><span class="sort hndle">|||</span>
										<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />
										<a class="repeatable-remove button" href="#">-</a></li>';
						}
						echo '</ul></td>
							<td><span class="description">'.$field['desc'].'</span></td>';
					break;

					case 'home_list':
						//list of used home teasers
						$teaser_list_used="";
						$tp_home_teasers = $meta;
						$tp_showbiz_teasers = get_option("tp_showbiz_uniq");

						if(is_array($tp_home_teasers)){
							foreach ($tp_home_teasers as $teaser) {
								$teaser_headline_short=get_option("tp_showbiz_slug_".$teaser);
								if(in_array($teaser, $tp_showbiz_teasers) || $teaser_headline_short ==""){
									if ($teaser_headline_short=="") $teaser_headline_short="Page Content";
									if(strlen($teaser_headline_short)>14)
										$teaser_headline_short= substr($teaser_headline_short, 0,14)."...";
										$teaser_list_used .= '<li class="widget ui-draggable"><input name="paws_home_teasers[]" type="hidden" value="'.$teaser.'">'.$teaser_headline_short.'</li>
									';
								}
							}
						}

						//list of unused teasers
						$teaser_list_unused="";
						
						//if(in_array("Content", haystack))
						if(is_array($tp_showbiz_teasers)){
							foreach ($tp_showbiz_teasers as $teaser) {
								$teaser_headline_short=get_option("tp_showbiz_slug_".$teaser);
								if(strlen($teaser_headline_short)>14)
									$teaser_headline_short= substr($teaser_headline_short, 0,14)."...";
								if(!is_array($tp_home_teasers) || !in_array($teaser, $tp_home_teasers))
									$teaser_list_unused .= '<li class="widget ui-draggable"><input name="paws_home_teasers[]" type="hidden" value="'.$teaser.'" disabled>'.$teaser_headline_short.'</li>
								';
							}
						}

						if (!strpos($teaser_list_used, 'value="paws_home_content">Page Content</li>'))
						$teaser_list_unused .= '<li class="widget ui-draggable"><input name="paws_home_teasers[]" type="hidden" value="paws_home_content" disabled>Page Content</li>';	

						echo '<style>
						#used, #unused { list-style-type: none; margin: 0; padding: 0; float: left; background-color: #FCFCFC;border: 1px solid #DFDFDF; margin-right: 10px;  padding: 5px; width: 143px; min-height:45px;}
						#used li, #unused li { margin: 5px; padding: 5px; width: 120px; cursor:pointer;}
						</style>
						<script>
						jQuery(function() {
							jQuery( "#used" ).sortable({
								connectWith: "ul",
								receive: function(event, ui){
									$this = jQuery(this);
									$this.find("input").removeAttr("disabled");
								}
							});
							
							jQuery( "#unused" ).sortable({
								connectWith: "ul",
								receive: function(event, ui){
									$this = jQuery(this);
									$this.find("input").attr("disabled",true);
								}
							});

							jQuery( "#used, #unused" ).disableSelection();
						});
						</script>

					
						<td valign="top">
						In Use<br>
						<ul id="used">
							'.$teaser_list_used.'
						</ul>
						</td>
						<td valign="top">
						Available<br>
						<ul id="unused">
							'.$teaser_list_unused.'
						</ul>
						</td><td></td></tr>
						';
					break;
				} //end switch
		echo '</tr>';
	} // end foreach
	//echo '<tr><td colspan=3 align="right"><input name="save" type="button" class="button-primary tp_publish_buttons" id="mypublish" accesskey="p" value=""></td></tr>';
	echo '</table>'; // end table
}

function remove_taxonomy_boxes() {
	remove_meta_box('categorydiv', 'post', 'side');
}
//add_action( 'admin_menu' , 'remove_taxonomy_boxes' );

// Save the Data
function save_custom_meta($post_id) {
    //global $custom_meta_fields,$custom_post_portfolio_type_meta_fields,$custom_page_portfolio_meta_fields,$custom_page_meta_fields,$custom_page_woocommerce_meta_fields,$custom_post_meta_fields,$custom_portfolio_meta_fields,$custom_post_type_meta_fields;
    global $custom_meta_fields,$custom_page_meta_fields,$custom_post_meta_fields;
    if(isset($_POST['post_type'])){
	    // which fields to use
	    if ('page' == $_POST['post_type']) {
			$custom_meta_fields = array_merge($custom_page_meta_fields);
		}
		if ('post' == $_POST['post_type']) {
			$custom_meta_fields = array_merge($custom_post_meta_fields);
		}

		/*if (is_array($portfolio_slugs) && in_array($_POST['post_type'], $portfolio_slugs)) {
			$custom_meta_fields = array_merge($custom_portfolio_meta_fields,$custom_post_portfolio_type_meta_fields);
		}*/

		// verify nonce
		if(isset($_POST['custom_meta_box_nonce'])){
			if (!wp_verify_nonce($_POST['custom_meta_box_nonce'], basename(__FILE__))) 
				return $post_id;
		}
		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;
		// check permissions
		if ('page' == $_POST['post_type']) {
			if (!current_user_can('edit_page', $post_id))
				return $post_id;
			} elseif (!current_user_can('edit_post', $post_id)) {
				return $post_id;
		}

		// loop through fields and save the data
		foreach ($custom_meta_fields as $field) {
			if($field['type'] == 'tax_select') continue;

				$old = get_post_meta($post_id, $field['id'], true);
				
				if(isset($_POST[$field['id']]))
					$new = $_POST[$field['id']];
				else $new = "";
			
			if ($new && $new != $old) {
				update_post_meta($post_id, $field['id'], $new);
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		} // end foreach
		
		// save taxonomies
		//$post = get_post($post_id);
		//$category = $_POST['category'];
		//wp_set_object_terms( $post_id, $category, 'category' );
	}
}
add_action('save_post', 'save_custom_meta');
?>