<?php

function get_pct_block( $atts ) {

    extract(shortcode_atts(array(
	"id" 		=> '',
	), $atts));

	if ( ! $id ) return 'NO HAY ID';

	$transient_key = 'pictau_block_' . intval( $id );
	$cached        = get_transient( $transient_key );

	if ( $cached !== false ) {
		return $cached;
	}

	$post = get_post( $id );

	if ( ! $post ) return 'No hay post content';

	// Si el contenido tiene shortcodes, no cacheamos: pueden ser dinámicos.
	$has_shortcodes = (bool) preg_match( '/' . get_shortcode_regex() . '/s', $post->post_content );

	// do_blocks() applies wpautop internally to core/shortcode block innerHTML,
	// wrapping shortcodes in <p>. shortcode_unautop() strips those before expansion.
	$output = shortcode_unautop( do_blocks( $post->post_content ) );
	$output = apply_shortcodes( $output );
	// remove gutenberg comments
	$buffer = preg_replace( '/<!--(.|s)*?-->/', '', $output );
	// remove empty <p> tags left by empty paragraph blocks, but preserve <p role="status"> (used by CF7 screen reader response)
	$buffer = preg_replace( '/<p(?![^>]*role=["\']status["\'])[^>]*>[\s\n\r]*<\/p>/i', '', $buffer );
	// inline SVGs
	$buffer = wp_svg_inline_filter( $buffer );

	if ( ! $has_shortcodes ) {
		set_transient( $transient_key, $buffer, 12 * HOUR_IN_SECONDS );
	}

	return $buffer;
}

add_shortcode('pictau-blocks', 'get_pct_block');

// Invalida el caché del bloque al guardar el CPT
add_action( 'save_post_pictau_blocks', function( $post_id ) {
	delete_transient( 'pictau_block_' . $post_id );
} );