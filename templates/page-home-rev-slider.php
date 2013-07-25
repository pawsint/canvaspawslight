<?php

/**
 * Template Name: PAWS Homepage with Rev-Slider
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a page ('page' post_type) unless another page template overrules this one.
 * @link http://codex.wordpress.org/Pages
 *
 * @package WooFramework
 * @subpackage Template
 */

	$pawscustoms = getPawsOptions();

	//Page Slider
	if(isset($pawscustoms["paws_activate_slider"])&&$pawscustoms["paws_activate_slider"]=="on") {
		$paws_slider = $pawscustoms["paws_header_slider"];
	}else{
		$paws_slider ="";
	}

	get_header();
?>

<?php if($paws_slider!="" ){ ?>
	<div class="homesliderwrapper">
		<div class="row homeslider">
			<?php echo do_shortcode('[rev_slider '.$paws_slider.']'); ?>
		</div>
	</div>
    <div class="row firstdivider"></div>
<?php } ?>
       
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main">                     
<?php
	woo_loop_before();
	
	if (have_posts()) { $count = 0;
		while (have_posts()) { the_post(); $count++;
			woo_get_template_part( 'content', 'page-home' ); // Get the page content template file, contextually.
		}
	}
	
	woo_loop_after();
?>     
            </section><!-- /#main -->
            <?php woo_main_after(); ?>
    
            <?php get_sidebar(); ?>

		</div><!-- /#main-sidebar-container -->         

		<?php get_sidebar( 'alt' ); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>