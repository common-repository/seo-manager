<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(!class_exists('WP_Seo_Plugin_Hooks'))
{
    class WP_Seo_Plugin_Hooks
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
			// Is enable settings from admin
			$isEnable = get_option('seo_manager_enable');
			if(!$isEnable){
			return;
			}
            // register actions
			add_filter('document_title_parts', array(&$this, '_seo_manager_hooks_wp_title'),10,1 );
			add_action('wp_head', array(&$this,'_seo_manager_seo_meta_hooks_func'),1);
			add_action('wp_head', array(&$this,'_seo_manager_head_hooks_func'),10);
			add_action('wp_footer', array(&$this,'_seo_manager_footer_hooks_func'));
        } // END public function __construct
     /*
	 * @wp_head action hook
	 * @add script into head section
	 * */
       public function _seo_manager_head_hooks_func()
       {
		   // added header tracking code
			$seo_manager_head = get_option('seo_manager_head');
			if($seo_manager_head!='')
			print $seo_manager_head;
		   }
	/*
	 * @wp_footer action hook
	 * @add script into footer section
	 * */
       public function _seo_manager_footer_hooks_func()
       {
			$seo_manager_footer = get_option('seo_manager_footer');
			if($seo_manager_footer!='')
			print $seo_manager_footer;
		   }
        /**
		 * Filters wp_title to print a neat <title> tag based on what is being viewed.
		 *
		 * @param string $title Default title text for current view.
		 * @param string $sep   Optional separator.
		 * @return string The filtered title.
		 */
		public static function _seo_manager_hooks_wp_title( $title ) {
			global $meta_box, $post;
					if ( (isset($post) && is_singular($post->post_type)) || (is_home() && !is_front_page())) {
					  $postID  = $post->ID;
				      if(is_home() && !is_front_page()){$postID = get_option( 'page_for_posts' );}
				
						$title = get_post_meta($postID,'_seo_manager_post_meta_title',true) ? get_post_meta($postID,'_seo_manager_post_meta_title',true) : $title;
					}
					// hooks to overide taxonomy title
					if(is_category() || is_tax())
					{
						
						$title = get_term_meta(get_queried_object_id(), '_seo_manager_term_meta_title', true) ? get_term_meta(get_queried_object_id(), '_seo_manager_term_meta_title', true) : $title;
						
						}
				if ( is_post_type_archive() ) {
						$archive_meta_title = get_option('seom_archive_meta_title_'.get_queried_object()->name);	
						if($archive_meta_title!='')
						$title = $archive_meta_title;
					}
					
					$title = is_array($title) ? ((isset($title[0]) && is_array($title[0])) ? $title[0]['title'] : $title['title']) : $title;
					
					return array($title);
		}
/**
* Implement seo meta into head section 
* @return seo meta title
*/
public function return_title_meta_val($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $title = get_post_meta($id,'_seo_manager_post_meta_title',true) ? get_post_meta($id,'_seo_manager_post_meta_title',true) : '';
			 break;
			case 'term':
			 $title = get_term_meta($id,'_seo_manager_term_meta_title',true) ? get_term_meta($id,'_seo_manager_term_meta_title',true) : '';
			 break;
			case 'archive':
			 $title = get_option('seom_archive_meta_title_'.$id);
			 break;
			case 'default':
			 $title =  '';
			 break;
		endswitch;
	 return $title; 	
   }	
}
/**
* Implement seo meta into head section 
* @return seo meta robots
*/
public function return_robots_meta_val($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $robots = get_post_meta($id,'_seo_manager_post_meta_robots',true) ? get_post_meta($id,'_seo_manager_post_meta_robots',true) : '';
			 break;
			case 'term':
			 $robots = get_term_meta($id,'_seo_manager_term_meta_robots',true) ? get_term_meta($id,'_seo_manager_term_meta_robots',true) : '';
			 break;
			case 'archive':
			 $robots = get_option('seom_archive_meta_robots_'.$id);
			 break;
			case 'default':
			 $robots =  '';
			 break;
		endswitch;
	 return $robots; 	
   }	
}
/**
* Implement seo meta into head section 
* @return seo meta og image
*/
public function return_ogimage($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $ogimage = get_post_meta($id,'_seo_manager_post_banner',true) ? get_post_meta($id,'_seo_manager_post_banner',true) : '';
			 break;
			case 'term':
			 $ogimage = get_term_meta($id,'_seo_manager_term_banner',true) ? get_term_meta($id,'_seo_manager_term_banner',true) : '';
			 break;
			case 'archive':
			 $ogimage = get_option('seom_archive_meta_banner_'.$id);
			 break;
			case 'default':
			 $ogimage =  '';
			 break;
		endswitch;
	 return wp_get_attachment_image_src($ogimage,'full');	
   }	
}
public function return_keyowrds_meta_val($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $keywords = get_post_meta($id,'_seo_manager_post_meta_keywords',true) ? get_post_meta($id,'_seo_manager_post_meta_keywords',true) : '';
			 break;
			case 'term':
			 $keywords = get_term_meta($id,'_seo_manager_term_meta_keywords',true) ? get_term_meta($id,'_seo_manager_term_meta_keywords',true) : '';
			 break;
			case 'archive':
			 $keywords = get_option('seom_archive_meta_keywords_'.$id);
			 break;
			case 'default':
			 $keywords =  '';
			 break;
		endswitch;
	 return $keywords; 	
   }	
}
public function return_description_meta_val($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $description = get_post_meta($id,'_seo_manager_post_meta_description',true) ? get_post_meta($id,'_seo_manager_post_meta_description',true) : '';
			 break;
			case 'term':
			 $description = get_term_meta($id,'_seo_manager_term_meta_description',true) ? get_term_meta($id,'_seo_manager_term_meta_description',true) : '';
			 break;
			case 'archive':
			 $description = get_option('seom_archive_meta_description_'.$id);
			 break;
			case 'default':
			 $description =  '';
			 break;
		endswitch;
	 return $description; 	
   }
}
public function return_canonical_meta_val($id='',$type='post')
{
	if($id!='')
	{
		switch($type):
			case 'post':
			 $canonical = get_post_meta($id,'_seo_manager_post_canonical',true) ? get_post_meta($id,'_seo_manager_post_canonical',true) : '';
			 break;
			case 'term':
			 $canonical = get_term_meta($id,'_seo_manager_term_canonical',true) ? get_term_meta($id,'_seo_manager_term_canonical',true) : '';
			 break;
			case 'archive':
			 $canonical = get_option('seom_archive_canonical_'.$id);
			 break;
			case 'default':
			 $canonical =  '';
			 break;
		endswitch;
	 return $canonical; 	
   }
}
/**
* Implement seo meta into head section 
* @meta_keywords
* @meta_description
* @meta_index
* @meta_canonical
*/
public function _seo_manager_seo_meta_hooks_func()
{
global $meta_box, $post;
$_seo_manager_meta_title = $_seo_manager_ogimage  = $_seo_manager_meta_canonical = $_seo_manager_meta_description = $_seo_manager_meta_keywords = $_seo_manager_meta_robots = ''; 
echo '
<!-- Start SEO Manager Tags-->';
if ( (isset($post) && is_singular($post->post_type)) || (is_home() && !is_front_page())) {
$postID  = $post->ID;
	$seo_manager_post_types = get_option('seo_manager_post_types') ? get_option('seo_manager_post_types'): array();
	if(in_array($post->post_type,$seo_manager_post_types))
	{
	// define default blog page id
	if(is_home() && !is_front_page()){$postID = get_option( 'page_for_posts' );}
	$_seo_manager_meta_robots       = $this->return_robots_meta_val($postID,'post');
	$_seo_manager_meta_keywords     = $this->return_keyowrds_meta_val($postID,'post'); 
	$_seo_manager_meta_description  = $this->return_description_meta_val($postID,'post');
	$_seo_manager_meta_canonical    = $this->return_canonical_meta_val($postID,'post'); 	
	$_seo_manager_ogimage           = $this->return_ogimage($postID,'post'); 	
	$_seo_manager_meta_title        = $this->return_title_meta_val($postID,'post'); 
	}

}
// hooks to overide taxonomy title
$seo_manager_term_types = get_option('seo_manager_term_types') ? get_option('seo_manager_term_types'): array();
if(is_category() || is_tax() || is_tag())
{
    $term_id = get_queried_object_id();	
	if(in_array(get_queried_object()->taxonomy,$seo_manager_term_types))
	{
	$_seo_manager_meta_robots       = $this->return_robots_meta_val($term_id,'term');
	$_seo_manager_meta_keywords     = $this->return_keyowrds_meta_val($term_id,'term'); 
	$_seo_manager_meta_description  = $this->return_description_meta_val($term_id,'term');
	$_seo_manager_meta_canonical    = $this->return_canonical_meta_val($term_id,'term');  
	$_seo_manager_ogimage           = $this->return_ogimage($term_id,'term'); 
	$_seo_manager_meta_title        = $this->return_title_meta_val($term_id,'term'); 
	}
}

//hooks to overide custom post type archive pages meta
if ( is_post_type_archive() ) {
$postyType = get_queried_object()->name;
$_seo_manager_meta_robots       = $this->return_robots_meta_val($postyType,'archive');
$_seo_manager_meta_keywords     = $this->return_keyowrds_meta_val($postyType,'archive'); 
$_seo_manager_meta_description  = $this->return_description_meta_val($postyType,'archive');
$_seo_manager_meta_canonical    = $this->return_canonical_meta_val($postyType,'archive');  
$_seo_manager_ogimage           = $this->return_ogimage($postyType,'archive');  
$_seo_manager_meta_title           = $this->return_title_meta_val($postyType,'archive');  
}
echo '
<meta property="og:type" content="website" />
<meta property="og:locale" content="en_GB" />
<meta property="og:site_name" content="'.get_bloginfo('name').'" />';
//start seo meta section
if($_seo_manager_meta_title!=''){
echo '
<meta property="og:title" content="'.$_seo_manager_meta_title.'">
<meta name="twitter:title" content="'.$_seo_manager_meta_title.'">';
}

if($_seo_manager_meta_keywords!='')
echo '
<meta name="keywords" content="'.$_seo_manager_meta_keywords.'" />';

if($_seo_manager_meta_description!='' ){
echo '
<meta name="description" content="'.$_seo_manager_meta_description.'" />
<meta property="og:description" content="'.$_seo_manager_meta_description.'" />
<meta name="twitter:description" content="'.$_seo_manager_meta_description.'" />';}

if(is_array($_seo_manager_ogimage) && $_seo_manager_ogimage[0]!=''){
echo '
<meta property="og:image" content="'.$_seo_manager_ogimage[0].'" />
<meta name="twitter:image" content="'.$_seo_manager_ogimage[0].'">';}

if($_seo_manager_meta_robots!='' && $_seo_manager_meta_robots!='default')
echo '
<meta name="robots" content="'.$_seo_manager_meta_robots.'">';

if($_seo_manager_meta_canonical !='')
echo '
<link rel="canonical" href="'.$_seo_manager_meta_canonical.'">';

echo '
<!-- END SEO Manager Tags -->
';	
}
     }
}
add_action('init','init_class_wp_seo_plugin_hooks');
function init_class_wp_seo_plugin_hooks()
{
	new WP_Seo_Plugin_Hooks();
	}
