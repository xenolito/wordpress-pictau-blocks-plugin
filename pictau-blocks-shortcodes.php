<?php

function get_pct_block( $atts ) {

    extract(shortcode_atts(array(
	"id" 		=> '',
	), $atts));

	if($id){
		$post = get_post( $id );

		if($post){
			$output = apply_shortcodes($post->post_content);
			// remove gutenberg comments
			$buffer = preg_replace('/<!--(.|s)*?-->/', '', $output);
			return $buffer;
		}
		else {
			return 'No hay post content';
		}
	}

	return 'NO HAY ID';
}

add_shortcode('pictau-blocks', 'get_pct_block');