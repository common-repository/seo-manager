<?php
/*
Plugin Name: SEO Manager
Description: Very Simple "SEO Manager" plugin with all SEO Meta features.
Author: WP Experts Team
Author URI: https://www.wp-experts.in/
Version: 1.8
License GPL2
Copyright 2018-2021  WP-Experts.In  (email raghunath.0087@gmail.com)

This program is free software; you can redistribute it andor modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('Seo_Manager'))
{
    class Seo_Manager
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
			// Installation and uninstallation hooks
			register_activation_hook(__FILE__, array(&$this, 'seo_manager_activate'));
			register_deactivation_hook(__FILE__, array(&$this, 'seo_manager_deactivate'));
			// admin settings links
			add_filter("plugin_action_links_".plugin_basename(__FILE__), array(&$this,'seo_manager_settings_link'));
            // register actions
			add_action('admin_init', array(&$this, 'seo_manager_admin_init'));
			add_action('admin_menu', array(&$this, 'seo_manager_add_menu'));
			
			add_action( 'admin_bar_menu', array(&$this,'toolbar_link_to_sm'), 999 );	
			
			// Is enable settings from admin
			$isEnable = get_option('seo_manager_enable');
			if(!$isEnable){
			return;
			}
			add_action( 'admin_enqueue_scripts', array(&$this, '_seo_manager_load_media') );	
			//define action for create new meta boxes
			add_action( 'add_meta_boxes', array(&$this, 'add_seo_manager_post_meta_box') );
			//Define action for save to "Video" Meta Box fields Value
			add_action( 'save_post', array(&$this, 'save_seo_manager_post_meta_box') );
			
				add_action( 'admin_print_footer_scripts', array(&$this, 'add_sm_media_script') );
        } // END public function __construct
		
		
		/*
	 * Add media upload script in admin footer
	 * @since 1.0.0
	 */
	public function add_sm_media_script() { 
		wp_enqueue_media();
		wp_enqueue_script('jquery');
		$footerscript ='
		 if(typeof jQuery!="undefined")
		 { 			
		 jQuery(document).ready( function(){
			 jQuery("body").on("click",".ve_tax_media_button",function(){
			 _orig_send_attachment = wp.media.editor.send.attachment;
			   var button_id = "#"+jQuery(this).attr("id"); 
			   var button_filed_id = "#"+jQuery(this).attr("data-fieldid");
			   var send_attachment_bkp = wp.media.editor.send.attachment;
			   var button = jQuery(button_id);
			   _custom_media = true;
			   wp.media.editor.send.attachment = function(props, attachment){
				 if ( _custom_media) {
				   jQuery(button_filed_id).val(attachment.id);
				   jQuery(button_filed_id+\'-image-wrapper\').html(\'<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:150px;float:none;" />\');
				   jQuery(button_filed_id+\'-image-wrapper .custom_media_image\').attr(\'src\',attachment.url).css(\'display\',\'block\');
						  _custom_media = false;
				 } else {
				   return _orig_send_attachment.apply( button_id, [props, attachment] );
				 }
				}
			 wp.media.editor.open(button);
			 return false;
		   });
		 jQuery(\'body\').on(\'click\',\'.ct_tax_media_remove\',function(){
		   var button_id = \'#\'+jQuery(this).attr(\'id\'); 
		   var button_filed_id = \'#\'+jQuery(this).attr(\'data-fieldid\');
		   jQuery(button_filed_id).val(\'\');
		   jQuery(button_filed_id+\'-image-wrapper\').html(\'<img class="custom_media_image" src="" style="margin:0;padding:0;max-height:100px;float:none;" />\');
		 });
	   });
	   // Thanks: http://stackoverflow.com/questions/15281995/wordpress-create-category-ajax-response
		 jQuery(document).ajaxComplete(function(event, xhr, settings) {
		   var queryStringArr = settings.data.split(\'&\');
		   if( jQuery.inArray(\'action=add-tag\', queryStringArr) !== -1 ){
			 var xml = xhr.responseXML;
			 $response = jQuery(xml).find(\'term_id\').text();
			 if($response!=""){
			   // Clear the thumb image
			   jQuery(\'.hooks-image-wrapper\').html(\'\');
			 }
		   }
		 }); 
   }
		 ';

wp_add_inline_script( 'jquery-core', $footerscript );
} 
		/**
		* hook to add link under adminmenu bar
		*/	
			 
		function toolbar_link_to_sm( $wp_admin_bar ) {
			$args = array(
				'id'    => 'sm_menu_bar',
				'title' => 'SEO Manager',
				'href'  => admin_url('options-general.php?page=seo_manager'),
				'meta'  => array( 'class' => 'sm-toolbar-page' )
			);
			$wp_admin_bar->add_node( $args );
			//second lavel
			$wp_admin_bar->add_node( array(
				'id'    => 'sm-second-sub-item',
				'parent' => 'sm_menu_bar',
				'title' => 'Settings',
				'href'  => admin_url('options-general.php?page=seo_manager'),
				'meta'  => array(
					'title' => __('Settings'),
					'target' => '_self',
					'class' => 'sm_menu_item_class'
				),
			));
		}
		/*-------------------------------------------------
				 Sanitize post data
		 ------------------------------------------------- */
		public function seo_sanitize_fields($type='',$val='')
		{
				// Is this textarea
				if($type='textarea')
				{
				  $val = sanitize_textarea_field($val);
				}else
				{
					$val = sanitize_text_field($val);
				}
				return $val;
			  }
		/**
		 * hook into WP's admin_init action hook
		 */
		public function seo_manager_admin_init()
		{
			// Set up the settings for this plugin
			$this->seo_manager_init_settings();
			// Possibly do additional admin_init tasks
			if( ! isset( $_POST['taxonomy'] ) && ! isset( $_POST['_seo_manager_term_meta_text_nonce'] )) {
		 return;
		}
			 // EDIT FIELD TO CATEGORY TERM PAGE
			$CURRENTTAXANOMY = $this->seo_sanitize_fields('text',$_POST['taxonomy']);
			add_action( 'edited_'.$CURRENTTAXANOMY , array(&$this,'_seo_manager_save_term_meta_box'), 10, 2 );
			add_action( 'create_'.$CURRENTTAXANOMY, array(&$this,'_seo_manager_save_term_meta_box'),10,2);
		} // END public static function activate
		
		/*-------------------------------------------------
				 Start Taxonomy Meta Boxes
		 ------------------------------------------------- */
		/**
		  * Taxonomy Term Meta Fields
		  */
		public function seo_manager_meta_fields($prefix='')
		{		 
		  $term_meta_box = '';
		  $term_meta_box=array(
			'id'      => 'seop-term-meta-box',
			'title'   => 'SEO Manager Taxonomy Meta',
			'fields'  => array(
								array(
								'title' => 'SEO Manager',
								'desc' => 'Modify meta title by editing it right here',
								'id'   => $prefix.'seom_heading',
								'name'   => $prefix.'seom_heading',
								'type' => 'heading',
								'placeholder'  => '',
								'std'  => ''
								),
								array(
								'title' => 'Meta Title',
								'desc' => 'Modify seo meta title by editing it right here',
								'id'   => $prefix.'meta_title',
								'name'   => $prefix.'meta_title',
								'type' => 'text',
								'placeholder'  => '',
								'std'  => ''
								),
								array(
								'title' => 'Meta Description',
								'desc' => 'Modify seo meta description by editing it right here',
								'id'   => $prefix.'meta_description',
								'name'   => $prefix.'meta_description',
								'type' => 'textarea',
								'placeholder'  => '',
								'std'  => ''
								),
								array(
								'title' => 'Meta Keywords',
								'desc' => 'Modify seo meta keywords by editing it right here',
								'id'   => $prefix.'meta_keywords',
								'name'   => $prefix.'meta_keywords',
								'type' => 'textarea',
								'placeholder'  => '',
								'std'  => ''
								),
								array(
								'title' => 'Canonical URL',
								'desc' => 'The canonical URL that this page should point to, leave empty to default to permalink. <a target="_blank" href="http://googlewebmastercentral.blogspot.com/2009/12/handling-legitimate-cross-domain.html">Cross domain canonical</a> supported too.',
								'id'   => $prefix.'canonical',
								'name'   => $prefix.'canonical',
								'type' => 'text',
								'placeholder'  => '',
								'std'  => ''
								),
								array(
								'title' => 'Meta Robots',
								'desc' => '',
								'id'   => $prefix.'meta_robots',
								'name'   => $prefix.'meta_robots',
								'type' => 'select',
								'options' => array('default','noindex,nofollow','noindex,follow','index,nofollow'),
								'placeholder'  => '',
								'std'  => ''
								),
								array(
									'title' => 'OG Image',
									'desc' => '',
									'placeholder' =>'',
									'id'   => $prefix.'banner',
									'name'   => $prefix.'banner',
									'type' => 'image',
									'std'  => ''
									)
								)
							);
		   return $term_meta_box;
		}
		/*
		 * Hooks to add meta fields on edit taxonomy edit screen
		 * 
		 * */
		function _seo_manager_load_media(){
				$allowTerm = get_option('seo_manager_term_types') ? get_option('seo_manager_term_types') : array('category');
				
				if( ! isset( $_GET['taxonomy'] )) {
				 return;
				}
				
				if(!in_array($_GET['taxonomy'],$allowTerm)) {
				 return;
				}
			   // EDIT FIELD TO CATEGORY TERM PAGE
				$CURRENTTAXANOMY = $this->seo_sanitize_fields('text',$_GET['taxonomy']);
				add_action( $CURRENTTAXANOMY.'_edit_form_fields', array(&$this,'_seo_manager_edit_form_field_term_meta_box'));
				// ADD FIELD TO CATEGORY TERM PAGE
				add_action( $CURRENTTAXANOMY.'_add_form_fields', array(&$this,'_seo_manager_add_form_field_term_meta_box') );
				wp_enqueue_media();
		}
		/*
		 * @Add new meta fields 
		 * @Create category 
		 * 
		 * */
		public function _seo_manager_add_form_field_term_meta_box() {
			wp_nonce_field( basename( __FILE__ ), '_seo_manager_term_meta_text_nonce' );
			$fields = $this->seo_manager_meta_fields('_seo_manager_term_');
			$formHtml = '';
			foreach ($fields['fields'] as $val){
			  switch($val['type']){
			   case 'heading':
			    echo '<div class="form-title">
					  <h3>'.$val['title'].'</h3><hr>
					</div>';
				break;
			   case 'text':
			   echo '<div class="form-field term-meta-phone-wrap">
					  <label for="'.$val['name'].'">'.$val['title'].'</label>
					  <input type="text" name="'.$val['name'].'" id="'.$val['id'].'" value="" class="term-meta-text-field" /> <br />'.$val['desc'].'
					</div>';
				break;
			case 'textarea':
			   echo '<div class="form-field term-meta-phone-wrap">
					  <label for="'.$val['name'].'">'.$val['title'].'</label>
					  <textarea name="'.$val['name'].'" id="'.$val['id'].'" class="term-meta-text-field"></textarea> <br />'.$val['desc'].'
					</div>';
				break;
			case 'image':
				echo '<div class="form-field term-group">
					 <label for="'.$val['name'].'">'.$val['title'].'</label>
					 <input type="hidden" id="'.$val['id'].'" name="'.$val['name'].'" class="custom_media_url" value="">
					 <div id="'.$val['id'].'-image-wrapper" class="hooks-image-wrapper"></div>
					 <p>
					   <input type="button" class="button button-secondary ve_tax_media_button" id="ve_tax_media_button" data-fieldid="'.$val['id'].'" name="ve_tax_media_button" value="Add Image" />
					   <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" data-fieldid="'.$val['id'].'" name="ct_tax_media_remove" value="Remove Image" />
					</p>
				   </div>'; 
				break;	
			   case 'select':
			   echo '<div class="form-field term-meta-phone-wrap">
					  <label for="'.$val['name'].'">'.$val['title'].'</label>
					  <select name="'.$val['name'].'" id="'.$val['id'].'">';
				$optionVal=isset($val['options']) ? $val['options'] : array();
				foreach($optionVal as $key => $optVal):
				/*if($meta==$key){
				$valseleted =' selected="selected"';}else {
					 $valseleted ='';
					}*/
				echo '<option value="'. $optVal. '">'. $optVal. '</option>';
			    endforeach;
				echo '</select> <br />'.$val['desc'].'
					</div>';
				break;
				default:
				//$formHtml = '';
				break;	
				}
			   }
		 }
		/*
		 * @Add new meta fields 
		 * @Edit category 
		 * 
		 * */
		public function _seo_manager_edit_form_field_term_meta_box( $term ) {
			wp_nonce_field( basename( __FILE__ ), '_seo_manager_term_meta_text_nonce' );
			$fields = $this->seo_manager_meta_fields('_seo_manager_term_');
			foreach ($fields['fields'] as $val){
			   $value = get_term_meta($term->term_id, $val['name'], true);
			  switch($val['type']){
			   case 'heading':
			   echo '<tr class="form-title">
					  <th scope="row" colspan="2"><h3>'.$val['title'].'</h3><hr></th>
			    </tr>';
			   break;
			   case 'text':
			   echo '<tr class="form-field term-meta-text-wrap">
					  <th scope="row"><label for="'.$val['name'].'">'.$val['title'].'</label></th>
					  <td>
					  <input type="text" name="'.$val['name'].'" id="'.$val['id'].'" value="'.$value.'" class="term-meta-text-field"  /> <br />'.$val['desc'].'
					  </td>
			   </tr>';
			   break;
			   case 'textarea':
			   echo '<tr class="form-field term-meta-text-wrap">
					  <th scope="row"><label for="'.$val['name'].'">'.$val['title'].'</label></th>
					  <td>
					  <textarea name="'.$val['name'].'" id="'.$val['id'].'" class="term-meta-text-field">'.$value.'</textarea> <br />'.$val['desc'].'
					  </td>
			   </tr>';
			   break;
			   case 'select':
			   echo '<tr class="form-field term-meta-text-wrap">
					  <th scope="row"><label for="'.$val['name'].'">'.$val['title'].'</label></th>
					  <td><select name="'.$val['name'].'" id="'.$val['id'].'">';
					    $optionVal=isset($val['options']) ? $val['options'] : array();
						foreach($optionVal as $key => $optVal):
						if($value==$optVal){
						$valseleted =' selected="selected"';}else {
							 $valseleted ='';
							}
						echo '<option value="'. $optVal. '" '.$valseleted.'>'. $optVal. '</option>';
						endforeach;
					  echo '</select> <br />'.$val['desc'].'</td>
			   </tr>';
			     break;
			  case 'image':
			  $term_image_id = get_term_meta($term->term_id, $val['name'], true);
			   echo  '<tr class="form-field term-group-wrap">
						 <th scope="row">
						   <label for="'.$val['name'].'">OG Image</label>
						 </th>
						 <td>
						   <input type="hidden" id="'.$val['id'].'" name="'.$val['name'].'" class="custom_media_url" value="'.$term_image_id.'">';
							echo '<div id="'.$val['id'].'-image-wrapper" class="hooks-image-wrapper">';
							if ( $term_image_id ) {
							 echo wp_get_attachment_image($term_image_id, 'thumbnail');  
							} 
						   echo ' </div>';
						  echo '<p>
							 <input type="button" class="button button-secondary ve_tax_media_button" id="'.$val['id'].'_add" data-fieldid="'.$val['id'].'" name="ve_tax_media_button" value="Add Image" />
					         <input type="button" class="button button-secondary ct_tax_media_remove" id="'.$val['id'].'_remove" data-fieldid="'.$val['id'].'" name="ct_tax_media_remove" value="Remove Image" /></p>
						 </td>
					   </tr>';
			   break;
			   default :
			   // silent
			   break;
		        }
			}
		 }
		// SAVE TERM META (on term edit & create)
		public function _seo_manager_save_term_meta_box( $term_id ) {
			// verify the nonce --- remove if you don't care
			if ( ! isset( $_POST['_seo_manager_term_meta_text_nonce'] ) || ! wp_verify_nonce( $_POST['_seo_manager_term_meta_text_nonce'], basename( __FILE__ ) ) )
				return;
			$fields = $this->seo_manager_meta_fields('_seo_manager_term_');
			foreach ($fields['fields'] as $val){
				$old_value = $this->_seo_manager_get_term_meta_box_value($term_id,$val['name']);
				$new_value = $this->seo_sanitize_fields($val['type'],isset( $_POST[$val['name']] ) ? ( $_POST[$val['name']] ) : '');
				if ( $old_value && '' === $new_value )
				delete_term_meta( $term_id, $val['name'] );
				else if ( $old_value !== $new_value )
				update_term_meta( $term_id, $val['name'], $new_value );
			}
		}
		// GETTER (will be sanitized)
		public function _seo_manager_get_term_meta_box_value( $term_id , $name) {
		  $value = get_term_meta( $term_id, $name, true );
		  $value = ( $value );
		  return $value;
		}
		/*-------------------------------------------------
				 End Taxonomy Meta Boxes
		 ------------------------------------------------- */
		/*-------------------------------------------------
				 Start POST Meta Boxes
		 ------------------------------------------------- */
		 function add_seo_manager_post_meta_box(){
				$screens = get_option('seo_manager_post_types') ? get_option('seo_manager_post_types') : array('post','page');
				foreach ( $screens as $screen ) {
					add_meta_box(
						'seo-manager-meta-box',
						__( 'SEO Manager', 'mrwebsolution' ),
						array(&$this,'show_seo_manager_meta_box'),
						$screen
					);
				}
			}
		 public function show_seo_manager_meta_box()
			{
				global $post;
				$seo_manager_meta_box = $this->seo_manager_meta_fields('_seo_manager_post_');
				wp_nonce_field( '_seo_manager_comman_box_field', '_seo_manager_comman_box_meta_box_once' );
				echo '<table class="form-table"><tbody>';
				foreach ($seo_manager_meta_box['fields'] as $field) {
					// get current post meta data
					$meta = get_post_meta($post->ID, $field['id'], true);
					echo '<tr>';
					if($field['type']!=='heading'){
					echo '<td><label for="', $field['id'], '">', $field['title'], '</label>','</td>';}
					switch ($field['type']) {
					case 'text':
					echo '<td><input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" placeholder="', $field['placeholder'] ? $field['placeholder'] : '', '" size="60" />', '<br />', $field['desc'],'</td>';
					break;
					case 'checkbox':
					echo '<td><input type="checkbox" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'],'"', checked( $meta, 'yes' ),' size="60" />', '<br />', $field['desc'],'</td>';
					break;
					case 'image':
					  $banner_image_id = $meta ? $meta : $field['std'];
					   echo  '<td>
								   <input type="hidden" id="'.$field['id'].'" name="'.$field['id'].'" class="custom_media_url" value="'.$banner_image_id.'">';
									echo '<div id="'.$field['id'].'-image-wrapper" class="hooks-image-wrapper">';
									if ( $banner_image_id ) {
									 echo wp_get_attachment_image($banner_image_id, array(100,100));  
									} 
								   echo ' </div>';
								  echo '<p>
									 <input type="button" class="button button-secondary ve_tax_media_button" id="'.$field['id'].'_add" data-fieldid="'.$field['id'].'" name="ve_tax_media_button" value="Add Image" />
									 <input type="button" class="button button-secondary ct_tax_media_remove" id="'.$field['id'].'_remove" data-fieldid="'.$field['id'].'" name="ct_tax_media_remove" value="Remove Image" /></p>
								 </td>';
					   break;
					case 'textarea':
					echo '<td><textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4"  placeholder="', $field['placeholder'] ? $field['placeholder'] : '', '">', $meta ? $meta : $field['std'], '</textarea>', '<br />', $field['desc'],'</td>';
					break;
					case 'select':
					echo '<td><select name="', $field['id'], '" id="', $field['id'], '" >';
					$optionVal=$field['options'];
					foreach($optionVal as $optVal):
					if($meta==$optVal){
					$valseleted =' selected="selected"';}else {
						 $valseleted ='';
						}
					echo '<option value="', $optVal, '" ',$valseleted,' id="', $field['id'], '">', $optVal, '</option>';
				endforeach;
				echo '</select>','<br />',$field['desc'],'</td>';
				break;
				echo '</tr>';
				}

				}
				
			echo '</tbody></table>';
		}
		 public function save_seo_manager_post_meta_box($post_id) {
			global $post_types;
			 $post_types = get_option('seo_manager_post_types') ? get_option('seo_manager_post_types') : array('post','page');
			// Check if our nonce is set.
			 if ( ! isset( $_POST['_seo_manager_comman_box_meta_box_once'] ) ) {
					return;
				}
			$seo_manager_meta_box = $this->seo_manager_meta_fields('_seo_manager_post_');
			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
			}

			// check permissions
			if (!in_array($_POST['post_type'],$post_types)) 
			{
				if (!current_user_can('edit_page', $post_id))
				return $post_id;
			} 
			elseif(!current_user_can('edit_post', $post_id)){
			return $post_id;
			}
			
			foreach ($seo_manager_meta_box['fields'] as $field) 
			{
			   if($field['type']=='heading')
			   {
				continue;
				}
				
				$old = get_post_meta($post_id, $field['id'], true);
				$new = $this->seo_sanitize_fields($field['type'],$_POST[$field['id']]);
				if ($new && $new != $old){
				 update_post_meta($post_id, $field['id'], $new);
				} 
				elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
				}
			}
		}
		/*-------------------------------------------------
				 End POST Meta Boxes
		 ------------------------------------------------- */
		/**
		 * Initialize some custom settings
		 */     
		public function seo_manager_init_settings()
		{
			// register the settings for this plugin
			register_setting('seo-manager-group', 'seo_manager_enable');
			register_setting('seo-manager-group', 'seo_manager_post_types');
			register_setting('seo-manager-group', 'seo_manager_term_types');
			register_setting('seo-manager-group', 'seo_manager_head');
			register_setting('seo-manager-group', 'seo_manager_footer');
			 $customType =  get_post_types(array('public' => true,'_builtin' => false),'names','and');
			 $seofields = $this->seo_manager_meta_fields('seom_archive_');
			 foreach ($customType  as $post_type){
							foreach ( $seofields['fields']  as $fields ) {
							  if($fields['type']=='heading'){continue;}
							  register_setting('seo-manager-group',$fields['id'].'_'.$post_type); 
							}
						}
			
		} // END public function init_custom_settings()
		/**
		 * add a menu
		 */     
		public function seo_manager_add_menu()
		{
			add_options_page('SEO Manager Settings', 'SEO Manager', 'manage_options', 'seo_manager', array(&$this, 'seo_manager_settings_page'));
		} // END public function add_menu()

		/**
		 * Menu Callback
		 */     
		public function seo_manager_settings_page()
		{
			if(!current_user_can('manage_options'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/lib/settings.php", dirname(__FILE__)));
			//include(sprintf("%s/css/admin.css", dirname(__FILE__)));
			// Style Files
			wp_register_style( 'seo_manager_admin_style', plugins_url( 'css/seom-admin.css',__FILE__ ) );
			wp_enqueue_style( 'seo_manager_admin_style' );
			// JS files
			wp_register_script('seo_manager_admin_script', plugins_url('/js/seom.js',__FILE__ ), array('jquery'));
            
            // Localize the script with new data
            $post_types_array = $custompostype = get_post_types(array('public' => true,'_builtin' => false),'names','and'); 
			$post_types_array['post'] = 'post';
			$post_types_array['page'] = 'page';
			
			wp_localize_script( 'seo_manager_admin_script', 'sm_post_type_array', $post_types_array);
            
            wp_enqueue_script('seo_manager_admin_script');
            
            
		} // END public function plugin_settings_page()
        /**
         * Activate the plugin
         */
        public static function seo_manager_activate()
        {
            // Do nothing
        } // END public static function activate
    
        /**
         * Deactivate the plugin
         */     
        public static function seo_manager_deactivate()
        {
            // Do nothing
        } // END public static function deactivate
        // Add the settings link to the plugins page
		function seo_manager_settings_link($links)
		{ 
			$settings_link = '<a href="options-general.php?page=seo_manager">Settings</a>'; 
			array_unshift($links, $settings_link); 
			return $links; 
		}
    } // END class Seo_Manager
} // END if(!class_exists('Seo_Manager'))
if(class_exists('Seo_Manager'))
{
    // instantiate the plugin class
    $seo_manager_plugin_template = new Seo_Manager();
}
// Render the hooks functions
include(sprintf("%s/lib/class.php", dirname(__FILE__)));
