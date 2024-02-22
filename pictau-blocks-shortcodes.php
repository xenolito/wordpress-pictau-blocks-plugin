<?php

function get_pct_block( $atts ) {

    extract(shortcode_atts(array(
	"id" 		=> '',
	), $atts));

	if($id){
		$post = get_post( $id );

		if($post){
			$output = apply_shortcodes($post->post_content);
			// ! echo first instead of 'return $output' for removing empty <p></p> tags...
			echo $output;
			return;
		}
		else {
			return 'No hay post content';
		}
	}

	return 'NO HAY ID';
}

add_shortcode('pictau-blocks', 'get_pct_block');

// Remove html comments <!-- --> coming from gutenberg
function callback($buffer) {
    $buffer = preg_replace('/<!--(.|s)*?-->/', '', $buffer);
    return $buffer;
}
function buffer_start() {
    ob_start("callback");
}
function buffer_end() {
    ob_end_flush();
}


add_action('get_header', 'buffer_start');
add_action('wp_footer', 'buffer_end');