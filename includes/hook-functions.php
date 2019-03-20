<?php

function wc_slider_get_categories_ajax_callback() {
	$selection_type      = empty( $_REQUEST['selection_type'] ) ? 'all' : sanitize_key( $_REQUEST['selection_type'] );
	$selected_categories = empty( $_REQUEST['selected_categories'] ) ? [] : wp_parse_id_list( $_REQUEST['selected_categories'] );
	$include_child       = empty( $_REQUEST['include_child'] ) || 'on' !== $_REQUEST['include_child'] ? false : true;
	$hide_empty          = empty( $_REQUEST['hide_empty'] ) || 'on' !== $_REQUEST['hide_empty'] ? false : true;
	$number              = empty( $_REQUEST['number'] ) ? 10 : intval( $_REQUEST['number'] );
	$orderby             = empty( $_REQUEST['orderby'] ) ? 'name' : sanitize_key( $_REQUEST['orderby'] );
	$order               = empty( $_REQUEST['order'] ) ? 'ASC' : sanitize_key( $_REQUEST['order'] );
	$slider_id           = empty( $_REQUEST['slider_id'] ) ? null : sanitize_key( $_REQUEST['slider_id'] );
	if($selection_type == 'all'){
		$selected_categories = [];
	}

	$categories = wc_category_slider_get_categories( array(
		'number'     => $number,
		'orderby'    => $orderby,
		'order'      => $order,
		'hide_empty' => $hide_empty,
		'include'    => $selected_categories,
		'exclude'    => array(),
		'child_of'   => 0,
		'post_id'    => $slider_id,
	) );
	wp_send_json_success( $categories );
}

add_action( 'wp_ajax_wc_slider_get_categories', 'wc_slider_get_categories_ajax_callback' );

function wc_category_slider_print_js_template() {
	global $current_screen;
	if ( empty( $current_screen->id ) || ( 'wc_category_slider' !== $current_screen->id ) ) {
		return;
	}
	?>
	<script type="text/html" id="tmpl-wc-category-slide">
		{{console.log(data)}}
		<div class="ever-col-6">
			<div class="ever-slide">
				<div class="ever-slide-header">
					<div class="ever-slide-headerleft">{{data.name}}</div>
				</div>
				<div class="ever-slide-main">
					<div class="ever-slide-thumbnail">
						<img src="{{data.image}}" alt="">
						<input type="hidden" name="{{data.term_id}}[image]" class="wccs-slider">
						<div class="ever-slide-thumbnail-tools">
							<a href="#" class="edit-image"><span class="dashicons dashicons-edit"></span></a>
							<a href="#" class="delete-image"><span class="dashicons dashicons-trash"></span></a>
						</div>
					</div>
					<div class="ever-slide-inner">
						<!--title-->
						<div class="ever-slide-title">
							<input class="ever-slide-url-inputbox regular-text" name="{{data.term_id}}[name]" placeholder="{{data.name}}" type="url" disabled="disabled">
						</div><!--/title-->

						<!--description-->
						<div class="ever-slide-captionarea">
							<textarea name="{{data.term_id}}[description]" id="caption-{{data.term_id}}" class="ever-slide-captionarea-textfield" data-gramm_editor="false" placeholder="Description" disabled="disabled">{{data.description}}</textarea>
						</div><!--/description-->

						<!--icon-->
						<div class="ever-slide-icon">
							<select name="{{data.term_id}}[icon]" id="{{data.term_id}}[icon]" class="select-2">
								<option value="">No Icon</option>
								<?php
								$icons = wc_category_slider_get_icon_list();
								foreach ($icons as $key => $value){
									echo "<option value='$key'>&#x{$value}; {$key}</option>";
								}
								?>
								<option value="">demo-fontawesome-icon (Demo Icon)</option>
							</select>
						</div><!--/icon-->

						<!--url-->
						<div class="ever-slide-url">
							<input name="{{data.term_id}}[url]" class="ever-slide-url-inputbox regular-text" placeholder="{{data.url}}" type="url" disabled="disabled">
							<div class="ever-slide-url-checkbox">
								<input name="{{data.term_id}}[new_tab]" class="ever-slide-captionarea-checkbox" type="checkbox" disabled="disabled">
								<label class="ever-slide-captionarea-label">Open In a new Tab</label>
							</div>
						</div><!--/url-->

					</div>
				</div>
			</div>
		</div>
	</script>
	<?php
}

add_action( 'admin_footer', 'wc_category_slider_print_js_template' );
