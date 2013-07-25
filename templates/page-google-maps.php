<?php
/*
Template Name: PAWS Page with Rev-Slider/Google-Maps
*/
	$pawscustoms = getPawsOptions();
    $template_uri = get_stylesheet_directory_uri();

    //Page Slider
	if(isset($pawscustoms["paws_activate_slider"])&&$pawscustoms["paws_activate_slider"]=="on") {
		$paws_slider = $pawscustoms["paws_header_slider"];
	}else{
		$paws_slider ="";
	}

	//Google Data
	$gmapaddress = $pawscustoms["paws_gmapadress"];
	$gmapzoom = empty($pawscustoms["paws_gmapzoom"]) ? 14 : $pawscustoms["paws_gmapzoom"];
	$gmapinfo = empty($pawscustoms["paws_gmapinfo"]) ? "" : $pawscustoms["paws_gmapinfo"];
	

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
	<?php if($gmapaddress!=""){ ?>
		<article class="gmap"><div id="gmap_inner"></div></article>
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<script type="text/javascript" src="<?php echo $template_uri;?>/js/jquery.gmap.js"></script>
		<script>
			  jQuery(window).load(function(){
				  //set google map with marker
				  jQuery("#gmap_inner").gMap({
					  markers: [{
						  address: '<?php echo $gmapaddress; ?>'<?php if($gmapinfo!="") {?>,
						  html: '<?php echo $gmapinfo; ?>' <?php } ?>
						}],
					  zoom: <?php echo $gmapzoom;?>
				  });
			  });
		</script>
	<?php } ?>
    
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