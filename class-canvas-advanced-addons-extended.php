<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

// CLASSES SECTION ---------------------------------------------------------------------------------------------------

/**
 * Canvas Advanced Addons Class - Extended
 *
 * All functionality pertaining to the dashboard widget feature.
 *
 * @package WordPress
 * @subpackage Canvas_Advanced_Addons_Extended
 * @category Plugin
 * @author Stuart Duff
 * @author Boris Uhlig for PAWS International
 * @since 1.0.0
 * Donate link: http://www.paws-int.com
 * Tags: themes, addon, styling, woothemes, canvas
 * Requires at least: 3.5
 * Tested up to: 3.5.2
 * Stable tag: 1.0.2
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

class Canvas_Advanced_Addons_Extended {
	private $dir;
	private $assets_dir;
	private $assets_url;
	private $token;
	public $version;
	private $file;

	/**
	 * Constructor function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct( $file ) {
		$this->dir = dirname( $file );
		$this->file = $file;
		$this->assets_dir = trailingslashit( $this->dir ) . 'img';
		$this->assets_url = esc_url( trailingslashit( get_stylesheet_directory_uri() . '/', $file  ) );
		$this->token = 'canvas_advanced_addons_extended';

		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		$woo_options = get_option( 'woo_options' );

		// Run this on activation.
		register_activation_hook( $this->file, array( &$this, 'activation' ) );

		//add_action( 'admin_print_styles', array( &$this, 'enqueue_admin_styles' ), 5 );
		add_action( 'init', array( &$this, 'woo_canvas_options_extended_add' ) );

		//Remove default Social Icons from main navigation
        add_action('init','remove_woo_nav_subscribe',20);
        function remove_woo_nav_subscribe() {
            remove_action('woo_nav_inside','woo_nav_subscribe',20);
        }

		// Add Social Icons To Header
		if ( isset( $woo_options['woo_top_nav_social_icons'] ) && ( 'header' == $woo_options['woo_top_nav_social_icons'] ) ) {
		add_action( 'woo_header_inside', array( &$this, 'header_social_icons_logic' ) );
		}

		// Add Social Icons To Navigation
		if ( isset( $woo_options['woo_top_nav_social_icons'] ) && ( 'navigation' == $woo_options['woo_top_nav_social_icons'] ) ) {
        add_action('woo_nav_inside',array( &$this, 'header_social_icons_logic' ) );
        }

		// Enable Business Slider On Homepage
		if ( isset( $woo_options['woo_biz_slider_homepage'] ) && ( 'true' == $woo_options['woo_biz_slider_homepage'] ) ) {
		add_action( 'get_header', array( &$this, 'business_slider_logic' ) );
		}

		// Enable Magazine Slider On Homepage
		if ( isset( $woo_options['woo_magazine_slider_homepage'] ) && ( 'true' == $woo_options['woo_magazine_slider_homepage'] ) ) {
		add_action( 'get_header', array( &$this, 'magazine_slider_logic' ) );
		}

		// Enable Magazine Page Content
		if ( isset( $woo_options['woo_magazine_page_content'] ) && ( 'true' == $woo_options['woo_magazine_page_content'] ) ) {
		add_action( 'init', array( &$this, 'magazine_page_content_logic' ) );
		}

		// WooCommerce Mini Cart Location
		if ( isset( $woo_options['woo_mini_cart_location'] ) && ( 'top-nav' == $woo_options['woo_mini_cart_location'] ) ) {
			add_action( 'init', array( &$this, 'remove_mini_cart_main_nav' ) );
			add_action( 'wp_nav_menu_items', array( &$this, 'move_mini_cart_to_top_nav' ), 10, 2 );
		}

		// Search Form Location
		if ( isset( $woo_options['woo_nav_search_location'] ) && ( 'true' == $woo_options['woo_nav_search_location'] ) ) {
            //moved to Functions Section -> add_action( 'woo_nav_inside', 'woo_custom_add_searchform', 10, 2 );
		}

		// Logo Location
		if ( isset( $woo_options['woo_nav_logo_location'] ) && ( 'true' == $woo_options['woo_nav_logo_location'] ) ) {
            add_action( 'init', 'woo_custom_move_logo', 10 );
		}

        // Show Login/Logout
        if ( isset( $woo_options['woo_misc_show_login'] ) && ( 'true' == $woo_options['woo_misc_show_login'] ) ) {
            add_filter( 'wp_nav_menu_items', 'add_loginout_link', 10, 2 );
        }

        // Add Shortcodes to Widgets
        if ( isset( $woo_options['woo_misc_add_shortcodes'] ) && ( 'true' == $woo_options['woo_misc_add_shortcodes'] ) ) {
            add_filter('widget_text', 'do_shortcode');
        }

        // Modify Tag Cloud Settings
        if ( isset( $woo_options['woo_misc_tagcloud_args'] ) && ( 'true' == $woo_options['woo_misc_tagcloud_args'] ) ) {
            add_filter( 'widget_tag_cloud_args', 'tagcloud_settings' );
        }

        // Activate Open Graph
        if ( isset( $woo_options['woo_misc_activate_opengraph'] ) && ( 'true' == $woo_options['woo_misc_activate_opengraph'] ) ) {
            add_action( 'wp_head', 'wp_put_opengraph_for_posts' );
        }

        // Activate Responsive Meta Tags
        if ( isset( $woo_options['woo_misc_load_responsive_meta_tags'] ) && ( 'true' == $woo_options['woo_misc_load_responsive_meta_tags'] ) ) {
            add_action('responsive_meta','woo_load_responsive_meta_tags');
        }

        // Activate Viewport
        if ( isset( $woo_options['woo_misc_load_viewport_meta_tags'] ) && ( 'true' == $woo_options['woo_misc_load_viewport_meta_tags'] ) ) {
            add_action( 'genesis_meta', 'paws_viewport_meta_tag' );
        }

        // LOAD JQUERY in FOOTER
        if ( isset( $woo_options['woo_misc_load_jquery_footer'] ) && ( 'true' == $woo_options['woo_misc_load_jquery_footer'] ) ) {
            add_action( 'wp_default_scripts', 'wp_print_jquery_in_footer' );
        }

        // LOAD ADDITIONAL SECONDARY MENU
        if ( isset( $woo_options['woo_nav_load_secondary_menu'] ) && ( 'true' == $woo_options['woo_nav_load_secondary_menu'] ) ) {
            add_action( 'init', 'woo_custom_add_secondary_menu_location', 10 );
            add_action( 'woo_nav_after', 'woo_custom_add_secondary_menu', 10 );
        }

        // LOAD ADDITIONAL FOOTER MENU
        if ( isset( $woo_options['woo_nav_load_footer_menu'] ) && ( 'true' == $woo_options['woo_nav_load_footer_menu'] ) ) {
            add_action( 'init', 'woo_custom_add_footer_menu_location', 10 );
            add_action( 'woo_footer_inside', 'woo_custom_add_footer_menu', 10 );
        }

        // REMOVE CANVAS AD AREA
        if ( isset( $woo_options['woo_header_remove_woo_top_ad'] ) && ( 'true' == $woo_options['woo_header_remove_woo_top_ad'] ) ) {
            add_action('init','remove_woo_header_top_ad',30);
            function remove_woo_header_top_ad() {
                remove_action( 'woo_header_inside', 'woo_top_ad', 30 );
            }
        }

        // LOAD ADDITIONAL WIDGET AREA IN HEADER I
        if ( isset( $woo_options['woo_header_add_widget_i'] ) && ( 'true' == $woo_options['woo_header_add_widget_i'] ) ) {
            add_action( 'init', 'custom_widgets_init_i' );  
            add_action( 'woo_header_inside', 'woo_header_widgetized_i' );  
        }

        // LOAD ADDITIONAL WIDGET AREA IN HEADER II
        if ( isset( $woo_options['woo_header_add_widget_ii'] ) && ( 'true' == $woo_options['woo_header_add_widget_ii'] ) ) {
            add_action( 'init', 'custom_widgets_init_ii' );
            add_action( 'woo_header_inside', 'woo_header_widgetized_ii' );
        }

        // CLOSE HALFOPENED TAGS
        if ( isset( $woo_options['woo_misc_parse_shortcode_content'] ) && ( 'true' == $woo_options['woo_misc_parse_shortcode_content'] ) ) {
            add_action( 'init', 'parse_shortcode_content' );
            add_action( 'wp_default_scripts', 'parse_shortcode_content' );
        }

        // ADMIN SHOW OPTION SETTINGS & VALUES
        if ( isset( $woo_options['woo_misc_admin_show_options'] ) && ( 'true' == $woo_options['woo_misc_admin_show_options'] ) ) {
            add_action('admin_menu', 'all_settings_link');
        }

        // ADMIN ACTIVATE BBPRESS FIX
        if ( isset( $woo_options['woo_misc_admin_bbpress_fix'] ) && ( 'true' == $woo_options['woo_misc_admin_bbpress_fix'] ) ) {
            add_filter( 'bbp_get_template_stack', 'woo_custom_deregister_bbpress_template_stack' );
            add_filter( 'template_include', 'woo_custom_maybe_load_bbpress_tpl', 99 );
        }

        // Loads Custom Styling
		add_action( 'woo_head', array( &$this, 'canvas_custom_styling' ) );

	} // End __construct()

   
	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'canvas-advanced-addons-extended', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'canvas-advanced-addons-extended';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Enqueue post type admin CSS.
	 * 
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function enqueue_admin_styles () {
		wp_register_style( 'canvas-advanced-addons-admin', $this->assets_url . 'css/admin.css', array(), '1.0.0' );
		wp_enqueue_style( 'canvas-advanced-addons-admin' );
	} // End enqueue_admin_styles()


	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'canvas-advanced-addons-extended' . '-version', $this->version );
		}
	} // End register_plugin_version()	

	/**
	 * Display Social Icons In The Header.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	
	public function header_social_icons_logic() {

		 global $woo_options;

		 $html = '';

		 $template_directory = get_stylesheet_directory_uri();

		 $profiles = array(
		 'twitter' => __( 'Follow us on Twitter' , 'canvas-advanced-addons-extended' ),
		 'facebook' => __( 'Connect on Facebook' , 'canvas-advanced-addons-extended' ),
		 'youtube' => __( 'Watch on YouTube' , 'canvas-advanced-addons-extended' ),
		 'flickr' => __( 'See photos on Flickr' , 'canvas-advanced-addons-extended' ),
		 'linkedin' => __( 'Connect on LinkedIn' , 'canvas-advanced-addons-extended' ),
		 'delicious' => __( 'Discover on Delicious' , 'canvas-advanced-addons-extended' ),
		 'googleplus' => __( 'View Google+ profile' , 'canvas-advanced-addons-extended' )
		 );

         if ( isset( $woo_options['woo_nav_social_icon_type'] ) && ( 'false' == $woo_options['woo_nav_social_icon_type'] ) ) {

		 // Open DIV tag.
		 $html .= '<div id="social-links" class="social-links">' . "\n";

		 foreach ( $profiles as $key => $text ) {
		 	if ( isset( $woo_options['woo_connect_' . $key] ) && $woo_options['woo_connect_' . $key] != '' ) {
		 		//$html .= '<a class="social-icon-' . $key . '" target="_blank" href="' . $woo_options['woo_connect_' . $key] . '" title="' . esc_attr( $text ) . '"></a>' . "\n";
		 		$html .= '<a target="_blank" href="' . $woo_options['woo_connect_' . $key] . '" title="' . esc_attr( $text ) . '"><img src="'. $this->assets_url . 'img/social/' . $key . '.png"></a>' . "\n";
		 	}
		 }

		 // Add a custom RSS icon, linking to Feedburner or default RSS feed.
		 $rss_url = get_bloginfo_rss( 'rss2_url' );
		 $rss_text = __( 'Subscribe to our RSS feed', 'canvas-advanced-addons-extended' );
		 if ( isset( $woo_options['woo_feed_url'] ) && ( $woo_options['woo_feed_url'] != '' ) ) { $rss_url = $woo_options['woo_feed_url']; }

		 $html .= '<a href="' . $rss_url . '" title="' . esc_attr( $rss_text ) . '"><img src="'. $this->assets_url . 'img/social/rss.png"></a>' . "\n";

         // Add a email icon, linking to your contacts page.
		 $email_text = __( 'Contact us!', 'canvas-advanced-addons-extended' );
         if ( isset( $woo_options['woo_subscribe_email'] ) && ( $woo_options['woo_subscribe_email'] ) ) { $email_url = $woo_options['woo_subscribe_email']; }  
         
		 $html .= '<a href="' . $email_url . '" title="' . esc_attr( $email_text ) . '"><img src="'. $this->assets_url . 'img/social/email.png"></a>' . "\n";

		 $html .= '</div><!--/#social-links .social-links -->' . "\n";

		 echo $html;	

         }

         if ( isset( $woo_options['woo_nav_social_icon_type'] ) && ( 'true' == $woo_options['woo_nav_social_icon_type'] ) ) {

	     $class = '';
         
         if ( isset( $woo_options['woo_header_cart_link'] ) && ( 'true' == $woo_options['woo_header_cart_link'] ) ) {
         $class = ' cart-enabled';
         }

         ?> <ul class="rss fr<?php echo $class; ?>">

             <?php 
             if ( ( isset( $woo_options['woo_subscribe_email'] ) ) && ( $woo_options['woo_subscribe_email'] ) ) { ?>
        		 <li class="sub-email"><a href="<?php echo esc_url( $woo_options['woo_subscribe_email'] ); ?>"></a></li>
		     <?php } ?>

        		 <?php 
             if ( isset( $woo_options['woo_nav_rss'] ) && ( $woo_options['woo_nav_rss'] == 'true' ) ) { ?>
            	 <li class="sub-rss"><a href="<?php if ( $woo_options['woo_feed_url'] ) { echo esc_url( $woo_options['woo_feed_url'] ); } else { echo esc_url( get_bloginfo_rss( 'rss2_url' ) ); } ?>"></a></li>
		     <?php } ?>

             <?php 
             if ( $woo_options['woo_connect_twitter' ] != "" ) { ?>
             <li class="twitter"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_twitter'] ); ?>" title="Twitter"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_facebook' ] != "" ) { ?>
             <li class="facebook"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_facebook'] ); ?>" title="Facebook"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_youtube' ] != "" ) { ?>
        		 <li class="youtube"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_youtube'] ); ?>" title="YouTube"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_flickr' ] != "" ) { ?>
             <li class="flickr"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_flickr'] ); ?>" title="Flickr"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_linkedin' ] != "" ) { ?>
             <li class="linkedin"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_linkedin'] ); ?>" title="LinkedIn"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_delicious' ] != "" ) { ?>
             <li class="delicious"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_delicious'] ); ?>" title="Delicious"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_googleplus' ] != "" ) { ?>
             <li class="googleplus"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_googleplus'] ); ?>" title="Google+"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_dribbble' ] != "" ) { ?>
             <li class="dribbble"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_dribbble'] ); ?>" title="Dribbble"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_instagram' ] != "" ) { ?>
             <li class="instagram"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_instagram'] ); ?>" title="Instagram"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_vimeo' ] != "" ) { ?>
             <li class="vimeo"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_vimeo'] ); ?>" title="Vimeo"></a></li>
             <?php } 

             if ( $woo_options['woo_connect_pinterest' ] != "" ) { ?>
             <li class="pinterest"><a target="_blank" href="<?php echo esc_url( $woo_options['woo_connect_pinterest'] ); ?>" title="Pinterest"></a></li>
        	     <?php } ?>
	     </ul>
    <?php
   
	} // Boostrap Options
    } // End header_social_icons_logic()	

	/**
	 * Display the "Business" slider above the default WordPress homepage.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	

	public function business_slider_logic() {

		if ( is_front_page() && ! is_paged() ) {
		    add_action( 'woo_main_before_home', 'woo_slider_biz', 10 );
		    add_action( 'woo_main_before_home', 'woo_custom_reset_biz_query', 11 );
		    add_action( 'woo_load_slider_js', '__return_true', 10 );
		    add_filter( 'body_class', 'woo_custom_add_business_bodyclass', 10 );
	    }  // End woo_custom_load_biz_slider()
		 
		function woo_custom_add_business_bodyclass ( $classes ) {
		    if ( is_home() ) {
		        $classes[] = 'business';
		    }
		    return $classes;
		} // End woo_custom_add_biz_bodyclass()
		 
		function woo_custom_reset_biz_query () {
		    wp_reset_query();
		} // End woo_custom_reset_biz_query()		

	} // End full_width_footer_logic()	

	/**
	 * Display the "Magazine" slider above the default WordPress homepage.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	
	public function magazine_slider_logic() {

		if ( is_front_page() && ! is_paged() ) {
		    add_action( 'woo_loop_before_home', 'woo_slider_magazine', 10 );
			add_action( 'woo_loop_before_home', 'woo_custom_reset_query', 11 );
			add_action( 'woo_load_slider_js', '__return_true', 10 );
			add_filter( 'body_class', 'woo_custom_add_magazine_bodyclass', 10 );
	    }  // End woo_custom_load_magazine_slider()
		 
		function woo_custom_add_magazine_bodyclass ( $classes ) {
		    if ( is_home() ) {
		        $classes[] = 'magazine';
		    }
		    return $classes;
		} // End woo_custom_add_magazine_bodyclass()
		 
		function woo_custom_reset_query () {
		    wp_reset_query();
		} // End woo_custom_reset_query()		

	} // End full_width_footer_logic()


	/**
	 * Display the Page Content below the magazine slider .
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	
	public function magazine_page_content_logic() {

		add_action( 'get_template_part_loop', 'woo_custom_display_page_content', 10, 2 );

	    function woo_custom_display_page_content ( $slug, $name ) {
	        if ( $name != 'magazine' ) { return; }
	            wp_reset_query();
	            global $post;
	            setup_postdata( $post );
		?>
	    <div <?php post_class( 'post' ); ?>>
	    <?php the_content(); ?>
	    </div><!--/.post-->
		<?php
	    } // End woo_custom_display_page_content()

	} // End magazine_page_content_logic()

	/**
	 * Remove the mini cart from the main navigation
	 * @access public
	 * @since 1.0.1
	 * @return void
	 **/
	public function remove_mini_cart_main_nav() {
		remove_action( 'woo_nav_inside', 'woo_add_nav_cart_link' );
	} // End remove_mini_cart_main_nav

	/**
	 * Move the mini cart to the top navigation
	 * @access public
	 * @since 1.0.1
	 * @param string $items
	 * @param array $args
	 * @return string
	 **/
	public function move_mini_cart_to_top_nav( $items, $args ) {
		global $woocommerce;
		if ( $args->menu_id == 'top-nav' ) {
			$items .= '</ul><ul class="nav cart fr"><li class="menu-item mini-cart-top-nav"><a class="cart-contents" href="'.esc_url( $woocommerce->cart->get_cart_url() ).'" title="'.esc_attr( 'View your shopping cart', 'woothemes' ).'">'.sprintf( _n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes' ), $woocommerce->cart->cart_contents_count ).' - '.$woocommerce->cart->get_cart_total().'</a></li>'; 
		}
		return $items;
	} // End move_mini_cart_to_top_nav

	/**
	 * Canvas Custom Styling.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	
	public function canvas_custom_styling() {

		global $woo_options;

		$output = '';

		// Add css for the header social icons
        if ( isset( $woo_options['woo_nav_social_icon_type'] ) && ( 'false' == $woo_options['woo_nav_social_icon_type'] ) ) {

			$output .= '#header #social-links { display: inline-block; margin-top: 5px;  margin-right:5px; height: 29px; }' . "\n";
			$output .= '#header #social-links img { width: 29px; height: 29px; }' . "\n";
			$output .= '#navigation #social-links { display: inline-block; margin-top: 5px;  margin-right:5px; height: 29px; }' . "\n";
			$output .= '#navigation #social-links img { width: 29px; height: 29px; }' . "\n";
		}	

        if ( isset( $woo_options['woo_nav_social_icon_type'] ) && ( 'true' == $woo_options['woo_nav_social_icon_type'] ) ) {

		}	

		// Add css for aligning the top navigation menu
		if ( isset( $woo_options['woo_top_nav_align'] ) && ( 'false' != $woo_options['woo_top_nav_align'] ) ) {

			$align_primary_nav = $woo_options['woo_top_nav_align'];

			if ( $align_primary_nav == 'centre' ) :
				$output .= '#top {text-align:center;}'. "\n";
		        $output .= '#top .col-full {float:none;display:inline-block;vertical-align:top;}'. "\n";
		        $output .= '#top .col-full li {display:inline;}'. "\n";
			elseif ( $align_primary_nav == 'right' ) : 
		        $output .= 'ul#top-nav {float:right; margin-right:-10px !important;}'. "\n";
		    endif;    		        	        

		}				

		// Add css for aligning the primary navigation menu
		if ( isset( $woo_options['woo_primary_nav_align'] ) && ( 'false' != $woo_options['woo_primary_nav_align'] ) ) {

			$align_primary_nav = $woo_options['woo_primary_nav_align'];

			if ( $align_primary_nav == 'centre' ) :
				$output .= '#navigation {text-align:center;}'. "\n";
		        $output .= 'ul#main-nav {float:none;display:inline-block;vertical-align:top;}'. "\n";
		        $output .= 'ul#main-nav li {display:inline;}'. "\n";
			elseif ( $align_primary_nav == 'right' ) : 
		        $output .= 'ul#main-nav {float:right;}'. "\n";
		    endif;    		        	        

		}		

		// Add css for aligning the social icons
		if ( isset( $woo_options['woo_nav_social_align'] ) && ( 'false' != $woo_options['woo_nav_social_align'] ) ) {

			$align_nav_social_icons = $woo_options['woo_nav_social_align'];

			if ( $align_nav_social_icons == 'left' ) :
				$output .= '#header #social-links .div {float:left;}'. "\n";
				$output .= '#navigation #social-links .div {float:left;}'. "\n";
				$output .= '#header ul.rss { margin-right: 0; margin-bottom: 0; margin-top: 0; float: left;} #header ul.rss:after {content: ""; display: block; clear: both;} #header ul.rss.cart-enabled { margin-right: 85px;} #header ul.rss li {float: left;}'. "\n";
			elseif ( $align_nav_social_icons == 'right' ) :
				$output .= '#header #social-links {float:right;}'. "\n";
				$output .= '#navigation #social-links {float:right;}'. "\n";
				$output .= '#header ul.rss { margin-right: 0; margin-bottom: 0; margin-top: 0; float: right;} #header ul.rss:after {content: ""; display: block; clear: both;} #header ul.rss.cart-enabled { margin-right: 85px;} #header ul.rss li {float: right;}'. "\n";
		    endif;    		        	        
		}		

		// Add css for top nav WooCommerce mini cart
		if ( isset( $woo_options['woo_mini_cart_location'] ) && ( 'top-nav' == $woo_options['woo_mini_cart_location'] ) ) {
			$output .= '#top .cart-contents::before {font-family: \'FontAwesome\';display: inline-block;font-size: 100%;margin-right: .618em;font-weight: normal;line-height: 1em;width: 1em;content: "\f07a";}' ."\n";
			$output .= '#top .cart{ margin-right:0px !important;}';
		}

		// Output the CSS to the woo_head function
		if ( '' != $output ) {
			echo "\n" . '<!-- PAWS Advanced Canvas CSS Styling -->' . "\n";
			echo '<style type="text/css">' . "\n";
			echo $output;
			echo '</style>' . "\n";
		}

	} // End canvas_custom_styling()

	/**
	 * Integrate Setting into WooFramework
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */	

	public function woo_canvas_options_extended_add() {

		function woo_options_add($options) {

		 	$shortname = 'woo';

		    // Full Width Header Options
		    $options[] = array( 'name' => __( 'PAWS Settings', 'canvas-advanced-addons-extended' ),
								'icon' => 'misc',
							    'type' => 'heading');    

			// Canvas Readme 
			$options[] = array( 'name' => __( 'Please read me!', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

            $options[] = array( "name" => __( 'Canvas PAWS Child-Theme Options', 'canvas-advanced-addons-extended' ),
					            "desc" => __( '', 'canvas-advanced-addons-extended' ),
					            "id" => $shortname."_paws_notice_one",
					            "std" => __( 'This Canvas Child Theme is based on the originally by Stuart Duff developed class <br><strong>"canvas-advanced-addons"</strong> and add\'s further useful and most requested<br> functions and elements to your Canvas Theme.<br><br>This Child Theme has been developed for the website of <strong>PAWS International</strong> the new non-profit umbrella organization, which is aiming to unite pet & animal welfare activist, shelters & rescues all over the world under one roof, to streamline activities and jointly work to stop cruelty to animals, back-yard breeding, supporting shelters and special needs animals.<br><br>Wordpress and PHP Experts volunteered and devoted their time free of charge to develop the Canvas PAWS Child-Theme, aiming to request you to become a donor in return for using this Child-Theme partially and/or complete.<br><br>Check it out in action <a href="http://www.paws-int.com" target="_blank">PAWS International</a> <br><br>Thank you in advance for your donation.', 'canvas-advanced-addons-extended' ),
					            "type" => "info");

			// Canvas Header Options
			$options[] = array( 'name' => __( 'Header Settings', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Add Social Icons to:', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'Use these settings to adjust the location of the social icons.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_top_nav_social_icons",							
								"type" => "select2",
								"options" => array( "false" => __( 'Disabled', 'canvas-advanced-addons-extended' ), "header" => __( 'Header Section', 'canvas-advanced-addons-extended' ), "navigation" => __( 'Navigation Section', 'canvas-advanced-addons-extended' ) ) );									

			$options[] = array( "name" => __( 'Adjust Social Icons Position', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'Use these settings to adjust the alignment of the items.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_social_align",							
								"type" => "select2",
								"options" => array( "false" => __( 'Disabled', 'canvas-advanced-addons-extended' ),  "left" => __( 'Align Left', 'canvas-advanced-addons-extended' ), "right" => __( 'Align Right', 'canvas-advanced-addons-extended' ) ) );

			$options[] = array( "name" => __( 'Use Twitter Bootstrap Icons instead of Classic Icons', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting replaces the colored classic social icons with the corresponding Bootstrap icons.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_social_icon_type",
								"std" => "false",
								"type" => "checkbox" );

			// Canvas Navigation Options
			$options[] = array( 'name' => __( 'Navigation Settings', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Add Search Form To Main Navigation', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will add the search form to the main navigation of your canvas theme.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_search_location",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Move the Logo To Main Navigation', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will add the search form to the main navigation of your canvas theme.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_logo_location",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Adjust Top Navigation Menu Position', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'Use these settings to adjust the alignment of the items within your Top Navigation Menu area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_top_nav_align",							
								"type" => "select2",
								"options" => array( "false" => __( 'Align Left', 'canvas-advanced-addons-extended' ), "centre" => __( 'Align Centre', 'canvas-advanced-addons-extended' ), "right" => __( 'Align Right', 'canvas-advanced-addons-extended' ) ) );									

			$options[] = array( "name" => __( 'Adjust Primary Navigation Menu Position', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'Use these settings to adjust the alignment of the items within your Primary Navigation Menu area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_primary_nav_align",							
								"type" => "select2",
								"options" => array( "false" => __( 'Align Left', 'canvas-advanced-addons-extended' ), "centre" => __( 'Align Centre', 'canvas-advanced-addons-extended' ), "right" => __( 'Align Right', 'canvas-advanced-addons-extended' ) ) );										

			$options[] = array( "name" => __( 'Activate Login/Logout in Menu', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will activate the display of Login/Logout within the menu.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_show_login",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Add Login/Logout to:', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'Use these settings to adjust the location of the Login/Logout menu.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_show_login_location",							
								"type" => "select2",
								"options" => array( "false" => __( 'Disabled', 'canvas-advanced-addons-extended' ), "top-menu" => __( 'Header Section', 'canvas-advanced-addons-extended' ), "primary-menu" => __( 'Navigation Section', 'canvas-advanced-addons-extended' ) ) );									

			$options[] = array( "name" => __( 'Activate Additional Main Menu', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will activate the display of an additional menu below the main navigation.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_load_secondary_menu",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Activate Footer Menu', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will activate the display of an additional menu in the footer area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_nav_load_footer_menu",
								"std" => "false",
								"type" => "checkbox" );

			// Canvas Homepage Options
			$options[] = array( 'name' => __( 'Homepage Settings', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Add Business Slider To The Homepage', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will add the business slider to the homepage of your canvas theme.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_biz_slider_homepage",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Add Magazine Slider To The Homepage', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will add the magazine slider to the homepage of your canvas theme.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_magazine_slider_homepage",
								"std" => "false",
								"type" => "checkbox" );

			// Canvas Magazine Template Options
			$options[] = array( 'name' => __( 'Magazine Template Settings', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');	

			$options[] = array( "name" => __( 'Display Page Content Below The Magazine Slider', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will display the page content below the magazine slider on the magazine page template.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_magazine_page_content",
								"std" => "false",
								"type" => "checkbox" );		

            //Check to see If WPML is present
            if(defined('ICL_SITEPRESS_VERSION')) {
			// WPML Options
			$options[] = array( 'name' => __( 'WPML Support', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Activate WPML Language Flag', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will activate the display of WPML active language flags.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_wpml_flags",
								"std" => "false",
								"type" => "checkbox" );

            } // END if defined('ICL_SITEPRESS_VERSION')

			// Widget Options
			$options[] = array( 'name' => __( 'Widget Options', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Add Shortcode Support to widgets', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable Shortcodes in Widgets.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_add_shortcodes",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Enable individual Tagcloud Widget Font Size', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable individual Font sizes for the Tagcloud Widget.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_tagcloud_args",
								"std" => "false",
								"type" => "checkbox" );

            $options[] = array( "name" => __( 'Tagcloud Widget Font Size', 'canvas-advanced-addons-extended' ),
			    		            "desc" => __( 'Enter an integer value i.e. 12 and 18 for for the desired min and max font size. Further define the maximum number of tags i.e. 25 to be displayed', 'canvas-advanced-addons-extended' ),
					            "id" => $shortname."_misc_tagcloud_min_max",
					            "std" => "",
					            "type" => array(
									        array(  'id' => $shortname. '_misc_tagcloud_min',
											        'type' => 'text',
											        'std' => '12',
											        'meta' => __( 'Min.', 'canvas-advanced-addons-extended' ) ),
									        array(  'id' => $shortname. '_misc_tagcloud_max',
											        'type' => 'text',
											        'std' => '18',
											        'meta' => __( 'Max.', 'canvas-advanced-addons-extended' ) ),
									        array(  'id' => $shortname. '_misc_tagcloud_no',
											        'type' => 'text',
											        'std' => '25',
											        'meta' => __( 'No.', 'canvas-advanced-addons-extended' ) )
								));

			$options[] = array( "name" => __( 'Add an additional Widget Area in the Left Header', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable another Widget Area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_header_add_widget_i",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Add an additional Widget Area in the Right Header', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable another Widget Area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_header_add_widget_ii",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Remove Canvas Top-Ad Header Area', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will remove the Canvas Top-Ad Area.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_header_remove_woo_top_ad",
								"std" => "false",
								"type" => "checkbox" );

			// Responsive Options
			$options[] = array( 'name' => __( 'Responsive Options', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Activate OpenGraph', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable your website to be easily recognizable by Social Networks. <br>In order to make your blog fully compliant with the Open Graph protocol, we have <br><strong>added the required function in the header.php file </strong>to the <strong>HTML Tag</strong>:<br><br>
<strong>prefix="og: http://ogp.me/ns#</strong><br><br><strong>Test your site at: <a href="http://ogp.spypixel.com/Pogo/checker/" target="_blank">ogp.spypixel.com</a></strong>.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_activate_opengraph",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Load Responsive Meta Tags', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable loading of responsive Meta Tags.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_load_responsive_meta_tags",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Activate Viewport Meta Tags', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will enable loading of Vieport Meta Tags for Mobile Devices.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_load_viewport_meta_tags",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( 'name' => __( 'Misc. Options', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading');

			$options[] = array( "name" => __( 'Parse unended and half open Strings', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will remove thoose strings.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_parse_shortcode_content",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Add List of all options to ->Settings->All Settings in Admin Area', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will display all options from wp_options.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_admin_show_options",
								"std" => "false",
								"type" => "checkbox" );

			$options[] = array( "name" => __( 'Activate BBPress Fix', 'canvas-advanced-addons-extended' ),
								"desc" => __( 'This setting will fix issues with BBPress. Only required if you use BBPress.', 'canvas-advanced-addons-extended' ),
								"id" => $shortname."_misc_admin_bbpress_fix",
								"std" => "false",
								"type" => "checkbox" );

			// Check To See If WooCommerce Is Activated Before Showing The Settings
			if ( is_woocommerce_activated() ) {				

			// Canvas WooCommerce Options
			$options[] = array( 'name' => __( 'WooCommerce Settings', 'canvas-advanced-addons-extended' ),
								'type' => 'subheading' );

			$options[] = array( 'name' => __( 'Mini Cart Location', 'canvas-advanced-addons-extended' ),
								'desc' => __( 'Location where the mini cart is displayed, by default this is in the main navigation.', 'canvas-advanced-addons-extended' ),
								'id' => $shortname . '_mini_cart_location',
								'type' => 'select2',
								'options' => array( 'main-nav' => __( 'Main Navigation', 'canvas-advanced-addons-extended' ), 'top-nav' => __( 'Top Navigation', 'canvas-advanced-addons-extended' ) ),
								'std' => 'main-nav' );

			} // END is_woocommerce_activated()

			return $options;

		}	

	}

} // END CLASS SECTION	

// FUNCTIONS SECTION -------------------------------------------------------------------------------------------------

/**
* Move the Logo to the main navigation
* @access public
* @since 1.0.1
* @return void
**/

function woo_custom_move_logo () {
    global $woo_options;
    remove_action( 'woo_header_inside', 'woo_logo', 10 );
    add_action( 'woo_nav_inside','woo_logo', 10 );
} // End woo_custom_move_logo()

/**
* Move the Search Form to the main navigation
* @access public
* @since 1.0.1
* @return void
**/

function woo_custom_add_searchform () {

    global $woo_options;

    if ( isset( $woo_options['woo_nav_search_location'] ) && ( 'true' == $woo_options['woo_nav_search_location'] ) ) {
        echo '<div id="nav-search" class="nav-search fr">' . "
        ";
        get_template_part( 'search', 'form' );
        echo '</div><!--/#nav-search .nav-search fr-->' . "
        ";
    }
} // End woo_custom_add_searchform()

add_action( 'woo_nav_inside', 'woo_custom_add_searchform', 10, 2 );


/**
* Activate WPML flag only display in top-menu
* @access public
* @since 1.0.1
* @return void
**/

if(defined('ICL_SITEPRESS_VERSION')) {

function language_selector_flags() {

    global $woo_options;

    if ( isset( $woo_options['woo_wpml_flags'] ) && ( 'true' == $woo_options['woo_wpml_flags'] ) ) {
	    $languages = icl_get_languages('skip_missing=0&orderby=code');
		    if(!empty($languages)){
        	    foreach($languages as $l){
            	    if(!$l['active']) echo '<a href="'.$l['url'].'">';
            	    echo '<img src="'.$l['country_flag_url'].'" height="12" alt="'.$l['language_code'].'" width="18" />';
            	    if(!$l['active']) echo '</a>';
   			}
            }
    } //END isset $woo_options
} // END function language_selector_flags()

if ( ! function_exists( 'woo_top_navigation' ) ) {
function woo_top_navigation() {
    if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'top-menu' ) ) {
    ?>
	    <div id="top">
		    <div class="col-full"><div id="flags_language_selector">&nbsp;&nbsp;<?php language_selector_flags(); ?></div>
		    <?php
		    echo '<h3 class="top-menu">' . woo_get_menu_name( 'top-menu' ) . '</h3>';
		    wp_nav_menu( array( 'depth' => 6, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'top-nav', 'menu_class' => 'nav top-navigation fl', 'theme_location' => 'top-menu' ) );
		    ?>
		    </div>
        </div><!-- /#top -->
    <?php
    }
} // END woo_top_navigation()
} // END (if ! function_exists())
} // END if defined 'ICL_SITEPRESS_VERSION')

/**
* Add Login/Logout Link in top-menu or primary-menu
* @access public
* @since 1.0.1
* @return void
**/

function add_loginout_link( $items, $args ) {

    global $woo_options;

    if ( isset( $woo_options['woo_misc_show_login_location'] ) && ( 'top-menu' == $woo_options['woo_misc_show_login_location'] ) ) {
        $login_menu_location = $woo_options['woo_misc_show_login_location'];
    }

    if ( isset( $woo_options['woo_misc_show_login_location'] ) && ( 'primary-menu' == $woo_options['woo_misc_show_login_location'] ) ) {
        $login_menu_location = $woo_options['woo_misc_show_login_location'];
    }
        
        if (is_user_logged_in() && $args->theme_location == $login_menu_location ) {
            $items .= '<li><a href="'. wp_logout_url() .'">Logout</a></li>';
        }
        elseif (!is_user_logged_in() && $args->theme_location == $login_menu_location ) {
            $items .= '<li><a href="'. site_url('wp-login.php') .'">Login</a></li>';
        }
    return $items;
} // To display in topmenu change primary-menu to top-menu and vice versa

/**
* Tag Cloud Widget Font Size
* @access public
* @since 1.0.1
* @return 
**/

function tagcloud_settings($args){

    global $woo_options;

    $min = $woo_options['woo_misc_tagcloud_min'];
    $max = $woo_options['woo_misc_tagcloud_max'];
    $no = $woo_options['woo_misc_tagcloud_no'];

	//$args = array('smallest' => $min, 'largest' => $max, 'number' => $no, 'unit' => 'px');
	$args = array('smallest' => $min, 'largest' => $max, 'number' => $no );
	return $args;
}

/**
* LOAD RESPONSIVE META TAGS
* @access public
* @since 1.0.1
* @return 
**/

function woo_load_responsive_meta_tags () {

    global $woo_options;

    if ( isset( $woo_options['woo_misc_load_responsive_meta_tags'] ) && ( 'true' == $woo_options['woo_misc_load_responsive_meta_tags'] ) ) {

	$html = '';

	$html .= "\n" . '<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->' . "\n";
	$html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />' . "\n";

	/* Remove this if not responsive design */
	$html .= "\n" . '<!--  Mobile viewport scale | Disable user zooming as the layout is optimised -->' . "\n";
	$html .= '<meta content="initial-scale=1.0; maximum-scale=4.0; user-scalable=yes" name="viewport"/>' . "\n";

	echo $html;
    } // END isset()
} // END FUNCTION woo_load_responsive_meta_tags()


/**
* Viewport Meta Tag for Mobile Browsers
*
* @author Boris Uhlig for PAWS International
* @link http://www.paws-int.com
*/
function paws_viewport_meta_tag() {

    $html = '';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
    echo $html;
}

/**
* ACTIVATE OPEN GRAPH TO MAKE SITE EASY RECOGNIZABLE BY SOCIAL NETWORKS
* @access public
* @since 1.0.1
* @return 
**/

function wp_put_opengraph_for_posts() {
    if ( is_singular() ) {
        global $post;
        setup_postdata( $post );
            $og_type = '<meta property="og:type" content="article" />' . "\n";
            $og_title = '<meta property="og:title" content="' . esc_attr( get_the_title() ) . '" />' . "\n";
            $og_url = '<meta property="og:url" content="' . get_permalink() . '" />' . "\n";
            $og_description = '<meta property="og:description" content="' . esc_attr( get_the_excerpt() ) . '" />' . "\n";
                if ( has_post_thumbnail() ) {
                    $imgsrc = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium' );
                    $og_image = '<meta property="og:image" content="' . $imgsrc[0] . '" />' . "\n";
                }
            echo $og_type . $og_title . $og_url . $og_description . $og_image;
    } //END is_singular()
} //END FUNCTION wp_put_opengraph_for_posts()

/**
* FUNCTION TO RETRIEVE POST AND PAGE OPTIONS 
* @access public
* @since 1.0.1
* @return 
**/

function getPawsOptions($id = 0){
    if ($id == 0) :
        global $wp_query;
        $content_array = $wp_query->get_queried_object();
		if(isset($content_array->ID)){
        	$id = $content_array->ID;
		}
    endif;   

    $first_array = get_post_custom_keys($id);

	if(isset($first_array)){
		foreach ($first_array as $key => $value) :
			   $second_array[$value] =  get_post_meta($id, $value, FALSE);
				foreach($second_array as $second_key => $second_value) :
						   $result[$second_key] = $second_value[0];
				endforeach;
		 endforeach;
	 } //END if(isset()

	if(isset($result)){
    	return $result;
	} //END if(isset()
} //END FUNCTION 

// TO USE IT
//$result = all_my_customs();
//echo $result['my_meta_key'];


/**
* FUNCTION TO LOAD JQUERY IN THE FOOTER 
* @access public
* @since 1.0.1
* @return 
**/

function wp_print_jquery_in_footer( &$scripts) {
	if ( ! is_admin() )
		$scripts->add_data( 'jquery', 'group', 1 );
}

/**
* FUNCTION TO ADD ADDTIONAL NAVIGATION MENU(S) 
* @access public
* @since 1.0.1
* @return 
**/

function woo_custom_add_secondary_menu_location() {
    $menus = array(
                'secondary-menu' => __( 'Additional Navigation Menu', 'woothemes' )
            );
      register_nav_menus( $menus );
} // End woo_custom_add_menu_locations()

function woo_custom_add_footer_menu_location() {
    $menus = array(
                'footer-menu' => __( 'Footer Menu', 'woothemes' )
            );
      register_nav_menus( $menus );
} // End woo_custom_add_menu_locations()

 function woo_custom_add_secondary_menu() {
     $menu_location = 'secondary-menu';
     if ( has_nav_menu( $menu_location ) ) {
        echo '
     <div id="' . $menu_location . '-container" class="col-full custom-menu-location">' . "
     ";
     wp_nav_menu( array( 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => $menu_location, 'menu_class' => 'nav fl', 'theme_location' => $menu_location ) );
        echo '</div>
     <!--/#' . $menu_location . '-container .col-full-->' . "
     ";
  }
 } // End woo_custom_add_secondary_menu()
 
function woo_custom_add_footer_menu() {
    $menu_location = 'footer-menu';
    if ( has_nav_menu( $menu_location ) ) {
        echo '
        <div id="' . $menu_location . '-container" class="col-full custom-menu-location">' . "
        ";
    wp_nav_menu( array( 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => $menu_location, 'menu_class' => 'nav fl', 'theme_location' => $menu_location ) );
    echo '</div>
    <!--/#' . $menu_location . '-container .col-full-->' . "
    ";
    }
} // End woo_custom_add_footer_menu()

/**
* REGISTER NEW HEADER WIDGETS LEFT-RIGHT
* @access public
* @since 1.0.1
* @return 
**/

function custom_widgets_init_i() {
    register_sidebar( array( 'name' => __( 'Header - Left', 'woothemes' ), 'id' => 'header_l', 'description' => __( 'A widgetized area in your header area', 'woothemes' ), 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h5>', 'after_title' => '</h5>' ) );
}

function custom_widgets_init_ii() {
    register_sidebar( array( 'name' => __( 'Header - Right', 'woothemes' ), 'id' => 'header_r', 'description' => __( 'A widgetized area in your header area', 'woothemes' ), 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h5>', 'after_title' => '</h5>' ) );
}


// Add the code inside the header area
function woo_header_widgetized_i() {
    if ( woo_active_sidebar( 'header_l' ) ) {
    ?>
    <div class="header-widget-l">
        <?php woo_sidebar( 'header_l' ) ?>
    </div>
    <?php    
   }
}

// Add the code inside the header area
function woo_header_widgetized_ii() {
    if ( woo_active_sidebar( 'header_r' ) ) {
    ?>
    <div class="header-widget-r">
        <?php woo_sidebar( 'header_r' ) ?>
    </div>
    <?php    
   }
}

/**
* PARSE UNENDEDHALF OPEN TAGS
* @access public
* @since 1.0.1
* @woo_options woo_misc_parse_shortcode_content 
* @return 
**/

function parse_shortcode_content( $content ) { 
    /* Remove '</p> or <br>' from the start of the string. */ 
	if ( substr( $content, 0, 6 ) == '<br />' ) 
	    $content = substr( $content, 6 ); 

    if ( substr( $content, 0, 4 ) == '</p>' ) 
	    $content = substr( $content, 4 ); 

    /* Remove '<p> or <br>' from the end of the string. */ 
	if ( substr( $content, -3, 3 ) == '<p>' ) 
	    $content = substr( $content, 0, -3 ); 

    if ( substr( $content, -6, 6 ) == '<br />' ) 
        $content = substr( $content, 0, -6 ); 

    return $content; 
}

/**
* CUSTOM ADMIN MENU LINK FOR ALL SETTINGS
* @access public
* @since 1.0.1
* @woo_options  
* @return 
**/

function all_settings_link() {
    add_options_page(__('All Settings'), __('All Settings'), 'administrator', 'options.php');
}

/**
* BBPRESS FIX
* @access public
* @since 1.0.1
* @woo_options  
* @return 
**/

function woo_custom_maybe_load_bbpress_tpl ( $tpl ) {
    if ( function_exists( 'is_bbpress' ) && is_bbpress() ) {
        $tpl = locate_template( 'bbpress.php' );
    }
return $tpl;
} // End woo_custom_maybe_load_bbpress_tpl()
 
function woo_custom_deregister_bbpress_template_stack ( $stack ) {
    if ( 0 < count( $stack ) ) {
        $stylesheet_dir = get_stylesheet_directory();
        $template_dir = get_template_directory();
            foreach ( $stack as $k => $v ) {
                if ( $stylesheet_dir == $v || $template_dir == $v ) {
                unset( $stack[$k] );
                }
            }
    }
return $stack;
} // End woo_custom_deregister_bbpress_template_stack()

//END FUNCTIONS SECTION

?>