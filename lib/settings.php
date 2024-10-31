<div class="wrap">
    <h2>SEO  Manager</h2>
	<div id="seo_manager-tab-menu"><a id="seo_manager-general" class="seo_manager-tab-links active" >General</a> <a  id="seo_manager-advance" class="seo_manager-tab-links">Advance Settings</a> <a  id="seo_manager-support" class="seo_manager-tab-links">Our other plugins</a></div>
    <form method="post" action="options.php" class="seo-manager-form"> 
		  <?php 	
		  // get register all post type 
			$post_types = $custompostype = get_post_types(array('public' => true,'_builtin' => false),'names','and'); 
			array_push($post_types,'post');array_push($post_types,'page');
			?>
        <div class="seo_manager-setting">
			<!-- General Setting -->	
			<div class="first seo_manager-tab" id="div-seo_manager-general">
				<table class="form-table">
				<tr><td  valign="top" nowrap>
				<table>
					<tr valign="top">
						<td><h4><?php _e('Enable SEO:');?> <input type="checkbox" name="seo_manager_enable" id="seo_manager_enable" value="1" <?php checked(get_option('seo_manager_enable'),1);?>></h4>				
						</td>
					</tr>  
					<tr valign="top">
						<?php $seo_manager_post_types = (isset(get_option('seo_manager_post_types')[0])) ? get_option('seo_manager_post_types') : array('page','post');
					
					?>
						
    
					<td><h4><?php _e('Select Post Types');?></h4>
							
							<select multiple="multiple" id="MultiPostType" name="seo_manager_post_types[]" class="multipleselecte">
    <?php
							       sort($post_types);
									foreach($post_types as $val)
									{
										$selected  = ($seo_manager_post_types!='' && in_array($val,$seo_manager_post_types)) ? ' selected="selected" ' : '';
										 echo '<option value="'.$val.'" '.$selected.'>'.ucfirst($val).'</option>';
										}
							?>
      
    </select><i>SEO meta section will be publish only on selected post type</i>
						</td>
					</tr>
					<tr valign="top">
						<td scope="row" valign="top"><h4><?php _e('Select Taxonomy Types');?></h4>
						<?php $seo_manager_term_types = get_option('seo_manager_term_types'); ?>
							<select name="seo_manager_term_types[]" id="MultiTermType" id="seo_manager_term_types" multiple class="multipleselecte">
								<?php 
								$selectedTerm = get_option('seo_manager_term_types') ? get_option('seo_manager_term_types'): array('category');
								$args = array(
								  'public'   => true,
								  '_builtin' => false
								  
								); 
								$output = 'names'; // or objects
								$operator = 'and'; // 'and' or 'or'
								$taxonomies = get_taxonomies( $args, $output, $operator ); 
								array_push($taxonomies,"category");array_push($taxonomies,"post_tag");
								if ( $taxonomies ) {
								foreach ( $taxonomies as $taxonomy ) {
									echo '<option value="'.$taxonomy.'" '.selected(in_array($taxonomy, $selectedTerm)).'>'.ucfirst(str_replace('_',' ',$taxonomy)).'</option>';
								}
								}
							?>    
						</select><i>SEO meta section will be publish only on selected taxonomies</i>
						</td>
					</tr>
					</table>
					</td>
					</tr>
				</table>
			</div>
			<div class="seo_manager-tab" id="div-seo_manager-advance"> 
				<table>
				<tr valign="top">
				<th align="left"><h3>SEO meta for archive pages</h3><hr></th>
				</tr>
			    <tr valign="top">
				<td>
				 <ol>
                  <?php 
					  $seofields = $this->seo_manager_meta_fields('seom_archive_'); // get seo fields
					   foreach ( $custompostype  as $post_type ) {
						echo '<li><h3>'.ucfirst(str_replace('_',' ',$post_type)).' </h3>';
				    			foreach ($seofields['fields'] as $val){
									if($val['type']=='heading'){continue;}
									  $dbvalue = get_option($val['id'].'_'.$post_type);
									  switch($val['type']){
									   case 'text':
									   echo '<div class="form-field term-meta-phone-wrap">
											  <label for="'.$val['name'].'_'.$post_type.'">'.$val['title'].'</label>
											  <input type="text" name="'.$val['name'].'_'.$post_type.'" id="'.$val['id'].'_'.$post_type.'" value="'.$dbvalue.'" class="term-meta-text-field" /> <br />'.$val['desc'].'
											</div>';
										break;
									case 'textarea':
									   echo '<div class="form-field term-meta-phone-wrap">
											  <label for="'.$val['name'].'_'.$post_type.'">'.$val['title'].'</label>
											  <textarea name="'.$val['name'].'_'.$post_type.'" id="'.$val['id'].'_'.$post_type.'" class="term-meta-text-field">'.$dbvalue.'</textarea> <br />'.$val['desc'].'
											</div>';
										break;
									   case 'select':
									   echo '<div class="form-field term-meta-phone-wrap">
											  <label for="'.$val['name'].'">'.$val['title'].'_'.$post_type.'</label>
											  <select name="'.$val['name'].'_'.$post_type.'" id="'.$val['id'].'_'.$post_type.'">';
										$optionVal=isset($val['options']) ? $val['options'] : array();
										foreach($optionVal as $optVal):
										if($dbvalue==$optVal){
										$valseleted =' selected="selected"';}else {
											 $valseleted ='';
											}
										echo '<option value="'. $optVal. '" '.$valseleted .'>'. $optVal. '</option>';
										endforeach;
										echo '</select> <br />'.$val['desc'].'
											</div>';
										break;
										default:
										//$formHtml = '';
										break;	
										}
									   } 
				         echo '</li>';
			        }
                      ?>
					  </ol>
					</td>
				 </tr>
				 <tr valign="top">
					<th scope="row"><h2><?php _e('Tracking Code');?></h2><hr></th>
				</tr>
				 <tr valign="top">
					<th scope="row"><?php _e('Site Header');?></br>
					<textarea rows="10" cols="100" id="seo_manager_head" name="seo_manager_head"  placeholder=""><?php echo get_option('seo_manager_head'); ?></textarea><br><i>Script or style will be call between <tt>&lt;head&gt;&lt;/head&gt;</tt> tag section </i><hr></th>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Site Footer');?></br>
					<textarea rows="10" cols="100"  id="seo_manager_footer" name="seo_manager_footer" ><?php echo get_option('seo_manager_footer');?></textarea><br><i>Script or style will be call footer of the site </i></p>
					</th>
				</tr>
	           </table>
			</div>
			<div class="last seo_manager-tab" id="div-seo_manager-support">
				<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4624D4L4LT6NU" target="_blank" style="font-size: 17px; font-weight: bold;"><img src="<?php echo  plugins_url( '../images/btn_donate_LG.gif' , __FILE__ );?>" title="Donate for this plugin"></a></p>
				<p><strong>Plugin Author:</strong><br><a href="http://www.mrwebsolution.in" target="_blank">WP-Experts.In Team</a></p>
				<p><a href="mailto:raghunath.0087@gmail.com" target="_blank" class="contact-author">Contact Author</a></p>
				
				<h2>Other plugins</h2>
				<p>
								  <ol>
					<li><a href="https://wordpress.org/plugins/custom-share-buttons-with-floating-sidebar" target="_blank">Custom Share Buttons With Floating Sidebar</a></li>
					<li><a href="https://wordpress.org/plugins/seo-manager/" target="_blank">SEO Manager</a></li>
							<li><a href="https://wordpress.org/plugins/protect-wp-admin/" target="_blank">Protect WP-Admin</a></li>
							<li><a href="https://wordpress.org/plugins/wp-sales-notifier/" target="_blank">WP Sales Notifier</a></li>
							<li><a href="https://wordpress.org/plugins/wp-tracking-manager/" target="_blank">WP Tracking Manager</a></li>
							<li><a href="https://wordpress.org/plugins/wp-categories-widget/" target="_blank">WP Categories Widget</a></li>
							<li><a href="https://wordpress.org/plugins/wp-protect-content/" target="_blank">WP Protect Content</a></li>
							<li><a href="https://wordpress.org/plugins/wp-version-remover/" target="_blank">WP Version Remover</a></li>
							<li><a href="https://wordpress.org/plugins/wp-posts-widget/" target="_blank">WP Post Widget</a></li>
							<li><a href="https://wordpress.org/plugins/wp-importer" target="_blank">WP Importer</a></li>
							<li><a href="https://wordpress.org/plugins/wp-csv-importer/" target="_blank">WP CSV Importer</a></li>
							<li><a href="https://wordpress.org/plugins/wp-testimonial/" target="_blank">WP Testimonial</a></li>
							<li><a href="https://wordpress.org/plugins/wc-sales-count-manager/" target="_blank">WooCommerce Sales Count Manager</a></li>
							<li><a href="https://wordpress.org/plugins/wp-social-buttons/" target="_blank">WP Social Buttons</a></li>
							<li><a href="https://wordpress.org/plugins/wp-youtube-gallery/" target="_blank">WP Youtube Gallery</a></li>
							<li><a href="https://wordpress.org/plugins/tweets-slider/" target="_blank">Tweets Slider</a></li>
							<li><a href="https://wordpress.org/plugins/rg-responsive-gallery/" target="_blank">RG Responsive Slider</a></li>
							<li><a href="https://wordpress.org/plugins/cf7-advance-security" target="_blank">Contact Form 7 Advance Security WP-Admin</a></li>
							<li><a href="https://wordpress.org/plugins/wp-easy-recipe/" target="_blank">WP Easy Recipe</a></li>
					</ol>
				</p>
			</div>
		</div>
		  <?php settings_fields('seo-manager-group'); ?>
        <h2><?php @submit_button(); ?></h2>
       <hr>
					<h3>Video Tutorial:</h3>
					<iframe width="560" height="315" src="https://www.youtube.com/embed/Oc3dRk37yK4?autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
					
    </form>
</div>
