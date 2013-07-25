<?php
// Array that holds all Page Options
// class is used to trigger some jQuery action

$custom_page_meta_fields = array(
		array(
			'label'	=> 'Use Slider',
			'text' => 'On/Off',
			'desc'	=> 'Use a slider at the top of this page',
			'id'	=> $prefix.'activate_slider',
			'type'	=> 'checkbox',
			'default' => 'checked',
			'class' => 'tp_options home_page content_page default clients'
		),
		array(
			'label'	=> 'Select Slider',
			'desc'	=> 'Choose the Slider to this Page',
			'id'	=>  $prefix.'header_slider',
			'default' => '',
			'type'	=> 'slider_list',
			'class' => 'tp_options default'
		),
		array(
			'label'	=> 'Google Map Address',
			'desc'	=> 'Insert Data that works best with maps.google.com',
			'id'	=> $prefix.'gmapadress',
			'type'	=> 'text',
			'class' => 'tp_options default contact'
		),
		array(
			'label'	=> 'Google Map Zoom',
			'desc'	=> 'Insert Zoom that works best with maps.google.com',
			'id'	=> $prefix.'gmapzoom',
			'type'	=> 'slider',
			'class' => 'tp_options default contact'
		),
		array(
			'label'	=> 'Google Map Info Text',
			'desc'	=> 'The info text displayed when clicking the location',
			'id'	=> $prefix.'gmapinfo',
			'type'	=> 'text',
			'class' => 'tp_options default contact'
		)
);


?>