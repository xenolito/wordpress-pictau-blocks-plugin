<?php
/**

 * Plugin Name: PICTAU BLOCKS GUTENBERG
 * Plugin URI: https://github.com/xenolito/wordpress-pictau-blocks-plugin
 * Description: This plugin adds and manage Blocks via shortcodes
 * Version: 3.0.0
 * Author: Oscar Rey Tajes (@xenolito)
 * Author URI: https://twitter.com/xenolito
 * License: GPL2

**/





function my_plugin_blocks_settings__links( $links ) {
	$links = array_merge( array(
		'<a href="' . esc_url( admin_url( '/options-general.php?page=PICTAU-Blocks-setting-admin' ) ) . '">' . __( 'Settings') . '</a>'
	), $links );
	return $links;
}

add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'my_plugin_blocks_settings__links' );



// add_action('wp_enqueue_scripts', 'pct_myBlocks', 10);

function pct_myBlocks() {

//  wp_enqueue_style( 'pct-myblocks-style', plugin_dir_url( __FILE__ ) . 'css/pct-myblocks-style.css');
  /*wp_enqueue_script( 'jquery-easing', plugin_dir_url( __FILE__ ) . 'js/jquery.easing.min.js',array( 'jquery' ),'1.0',true);*/
//  wp_register_script( 'jquery2.1.1', plugin_dir_url( __FILE__ ) . 'js/jquery-2.1.1.js',null,'2.1.1',true );
/*  wp_register_script( 'modernizr', plugin_dir_url( __FILE__ ) . 'js/modernizr.js',array('jquery'),null,true );
  wp_register_script( 'velocity', plugin_dir_url( __FILE__ ) . 'js/velocity.min.js',array('modernizr'),null,true );
*/
//  wp_register_script( 'pct-myblocks-scripts', plugin_dir_url( __FILE__ ) . 'js/pct-myblocks.min.js',null,true );

  // Pasamos las variables de la página admin al js del plugin via wp_localize_script
  //wp_localize_script( 'animated-modal-fullscreen-pictau', 'amfp_object', $translation_array );

//  wp_enqueue_script( 'pct-myblocks-scripts');
	return;
}



/*------------------------------------------------------------------------------------------------------*\

						CUSTOM POST TYPES "PICTAU BLOCKS"

\*------------------------------------------------------------------------------------------------------*/
add_action( 'init', 'custom_post_type_pictau_blocks', 0 );

function custom_post_type_pictau_blocks() {

// Set UI labels for Custom Post Type
	$labels = array(
		'name'                => _x( 'pictau_blocks', 'Post Type General Name', 'Pictau Blocks' ),
		'singular_name'       => _x( 'pictau_blocks', 'Post Type Singular Name', 'Pictau Blocks' ),
		'menu_name'           => __( 'Pictau Blocks', 'Pictau Blocks' ),
		'parent_item_colon'   => __( 'Parent Page', 'Pictau Blocks' ),
		'all_items'           => __( 'Todos los pictau_blocks', 'Pictau Blocks' ),
		'view_item'           => __( 'Ver pictau_blocks', 'Pictau Blocks' ),
		'add_new_item'        => __( 'Añadir nuevo pictau_blocks', 'Pictau Blocks' ),
		'add_new'             => __( 'Añadir nuevo', 'Pictau Blocks' ),
		'edit_item'           => __( 'Editar pictau_blocks', 'Pictau Blocks' ),
		'update_item'         => __( 'Actualizar pictau_blocks', 'Pictau Blocks' ),
		'search_items'        => __( 'Buscar pictau_blocks', 'Pictau Blocks' ),
		'not_found'           => __( 'No encontrado', 'Pictau Blocks' ),
		'not_found_in_trash'  => __( 'No encontrado en papelera', 'Pictau Blocks' ),
	);

// Set other options for Custom Post Type

	$args = array(
		'label'               => __( 'pictau_blocks', 'Pictau Blocks' ),
		'description'         => __( 'Bloques estáticos reutilizables', 'Pictau Blocks' ),
		'labels'              => $labels,
		// Features this CPT supports in Post Editor
		'supports'            => array('title', 'editor', 'revisions', 'page-attributes'),
		'show_in_rest'		  => true, // for allowing GUTENBERG editor
		// You can associate this CPT with a taxonomy or custom taxonomy.
		//'taxonomies'          => array('Asesores','category'), --> CPT with Categories
		//'taxonomies'          => array('Asesores','post_tag'), --> CPT with Tags
		'taxonomies'          => array('pictau_blocks'),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'query_var'           => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           =>'dashicons-tagcloud',
		'can_export'          => true,
		'has_archive'         => false, /* true: has an archive page, false: not archive page*/
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);

	// Registering your Custom Post Type
	register_post_type( 'pictau_blocks', $args );


}


/*------------------------------------------------------------------------------------------------------*\

						ADD CUSTOM COLUMN FOR CPT ADMIN LIST PAGE

\*------------------------------------------------------------------------------------------------------*/

add_filter( 'manage_pictau_blocks_posts_columns', 'set_custom_edit_pictau_blocks_columns' );

function set_custom_edit_pictau_blocks_columns($columns) {
    unset( $columns['author'] );
    $columns['shortcode'] = __( 'Shortcode', 'Pictau Blocks' );


    return $columns;
}

// Add the data to the custom columns for the pictau_blocks post type:
add_action( 'manage_pictau_blocks_posts_custom_column' , 'custom_pictau_blocks_column', 10, 2 );

function custom_pictau_blocks_column( $column, $post_id ) {
    switch ( $column ) {

        case 'shortcode' :

        	echo '[pictau-blocks id="'. $post_id .'"]';
            /*$terms = get_the_term_list( $post_id , 'book_author' , '' , ',' , '' );
            if ( is_string( $terms ) )
                echo $terms;
            else
                _e( 'Unable to get author(s)', 'your_text_domain' );
            */
			break;

/*
        case 'publisher' :
            echo get_post_meta( $post_id , 'publisher' , true );
            break;
*/
    }
}






/*------------------------------------------------------------------------------------------------------*\

						ADMIN PAGE CONFIG.

\*------------------------------------------------------------------------------------------------------*/


class pct_blocks_settingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        // add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
        add_options_page(
            'PICTAU-BLOCKS Settings ADMIN',
            'PICTAU-BLOCKS Settings',
            'manage_options',
            'PICTAU-BLOCKS-setting-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'pictau_blocks' );
        ?>
        <style>
	        #footer {
		        display: block;
		        background-color: #148e65;
		        padding: 30px 20px;
		        color:#00de94;
	        }

	        #footer a {
		        color: #FFF;
		        text-decoration: none;
		        padding: 5px 5px;
	        }

	        #footer .dashicons {
		        margin-right: 5px;
	        }

			#pct-plugin-admin-content {
				background-color: #FFF;
				border-radius: 5px;
				padding: 2rem;
			}

			#pct-plugin-admin-content code {
				padding: 1rem;
			}

			#pct-plugin-admin-content ul {
				list-style: disc;
				padding-left: 2rem;
			}

	    </style>
        <div class="wrap">
	        <div>
		        <a href="https://www.pictau.com" target="_blank"><img style="float: left;margin-right: 10px;" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/pictau-icon.png' ?>"/></a><h1 style="vertical-align: middle;display: inline-block;padding-bottom: 20px;	"> PICTAU BLOCKS MANAGER</h1>
	        </div>
	        <div id="pct-plugin-admin-content">
		        <h1>Using the PICTAU BLOCKS plugin</h1>
		        <p>This plugin needs the following html elements:</p>
		        <p>&nbsp;</p>
		        <p>
			        1) A link button to launch the modal, of type <code>&lt;a href="#my-modal-id" class="afmp" [data-modal-color="#FC1A57"] [data-modal-shape="square"]&gt;my modal opener&lt;/a&gt;</code></pre>
			    </p>
			    <p>
				    Where:
				    	<ul>
					    	<li><strong>#my-modal-id</strong>: Is the modal unique id that we want to show, including the hashtag</li>
					    	<li> Class <strong>afmp</strong>: To identify the modal launcher for the hook plugin system</li>
					    	<li>Attribute <strong>data-modal-color="hexadecimal"</strong>: Optional. Is the modal background color, in hexadecimal. If omitted, default background color es <strong>"#34383C"</strong>. You can customize a different modal background color for each link launcher.</li>
					    	<li>Attribute <strong>data-modal-shape="string"</strong>: Optional. You can set the shape of the modal background when is animating to "circle" or "square". If omitted, default is "circle".</li>

					    </ul>
			    </p>
		        <p>&nbsp;</p>
		        <p>
			        2) A modal content to show <code>&lt;div id="<strong>my-modal</strong>" class="<strong>afmp-content</strong>"&gt; MODAL CONTENT &lt;/div&gt;</code></pre>
			    </p>
			    <p>
				    Where:
				    	<ul>
					    	<li><strong>my-modal</strong>: Is the modal unique id that will be referenced by the "href" attribute on our link launcher.</li>
					    	<li> Class <strong>afmp-content</strong>: Class name to identify the modal content.</li>
					    </ul>
			    </p>
		        <p>&nbsp;</p>
			    <p>
				    <strong>Additional notes:</strong><br/>
				    You can call and show the same modal content element with different links/buttons, just use the same href="#modal-unique-id" on your link launcher, and customize your background color an background shape.


			    </p>
	        </div>

        <div id="footer">
	        Please, <a href="mailto:contacto@pictau.com"><i class="dashicons dashicons-email-alt"></i>Contact us </a> and let us know how can we improve this plugin, or visit:  <a target="_blank" href="https://www.pictau.com"><i class="dashicons dashicons-admin-site"></i>www.pictau.com</a>
        </div>

        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'pictau_blocks_option_group', // Option group
            'pictau_blocks', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Customize your settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

/*        add_settings_field(
            'afmp_phone_number', // ID
            'Phone Number', // Title
            array( $this, 'phone_number_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'afmp_avoidFooter', // ID
            'Avoid Footer', // Title
            array( $this, 'avoidFooter_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'afmp_button_link_title',
            'Link Title',
            array( $this, 'title_callback' ),
            'my-setting-admin',
            'setting_section_id'
        );
*/
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['afmp_phone_number'] ) )
            $new_input['afmp_phone_number'] = sanitize_text_field( $input['afmp_phone_number'] );
        if( isset( $input['fbpp_avoidFooter'] ) )
            $new_input['afmp_avoidFooter'] = sanitize_text_field( $input['afmp_avoidFooter'] );

        if( isset( $input['afmp_button_link_title'] ) )
            $new_input['afmp_button_link_title'] = sanitize_text_field( $input['afmp_button_link_title'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Customize your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */

    /*
    public function phone_number_callback()
    {
        printf(
            '<input type="text" id="afmp_phone_number" name="afmp[afmp_phone_number]" value="%s" />',
            isset( $this->options['afmp_phone_number'] ) ? esc_attr( $this->options['afmp_phone_number']) : ''
        );
    }

    public function avoidFooter_callback()
    {
        printf(
            '<input type="text" id="afmp_avoidFooter" name="afmp[afmp_avoidFooter]" value="%s" /> Leave empty for "disabled". Enter the footer classname [.footer_classname] or id [#footer_id]. This will make the floating button to be placed at the top of your selected footer so it doesn\'t cover your content. This could be quite usefull specially on mobile devices.',
            isset( $this->options['afmp_avoidFooter'] ) ? esc_attr( $this->options['afmp_avoidFooter']) : ''
        );
    }


    public function title_callback()
    {
        printf(
            '<input type="text" id="afmp_button_link_title" name="afmp[afmp_button_link_title]" value="%s" />',
            isset( $this->options['afmp_button_link_title'] ) ? esc_attr( $this->options['afmp_button_link_title']) : ''
        );
    }
    */
}


/*------------------------------------------------------------------------------------------------------*\

						ADD THE WIDGET

\*------------------------------------------------------------------------------------------------------*/

// Register the widget
add_action( 'widgets_init', function(){
	register_widget( 'pictau_blocks' );
});


class pictau_blocks extends WP_Widget {
	// class constructor
	public function __construct() {

		$widget_ops = array(
				'classname' => 'pictau_block',
				'description' => 'Banner de Anuncios personalizados',
			);
		parent::__construct( 'pictau_block', 'Pictau Blocks', $widget_ops );

	}

	// output the widget content on the front-end
	public function widget( $args, $instance ) {

		echo '<div id="pictau_block-3" class="widget pictau_widget pictau_block">';

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		//echo '<h1><pre>'. print_r($instance) .'</pre></h1>';
		/*echo '<pre>';
		print_r($wp_query);
		echo '</pre>';*/




		if( ! empty( $instance['block_select'] ) ){

			$post = get_post($instance['block_select']);

//				echo '<h1><pre>'. print_r($post) .'</pre></h1>';

			if($post){
				echo do_shortcode($post->post_content);

			}
		}
		else {
			echo esc_html__( 'No posts selected!', 'text_domain' );
		}

		echo $args['after_widget'];


	}

	// output the option form field in admin Widgets screen
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(
			'block_select' => '',
		) );

		$block_select = !empty( $instance['block_select'] ) ? $instance['block_select'] : '';








		// Título del widget
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" style="display: block;">
		<?php esc_attr_e( 'Title:'); ?>
		</label>

		<input
			class="widefat"
			id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
			name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			type="text"
			value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
		// END Título del widget



		$posts = get_posts( array(
				'posts_per_page' => 30,
				'offset' => 0,
				'post_type'        => 'pictau_blocks',

			) );

		$block_selected = ! empty( $instance['block_selected'] ) ? $instance['block_selected'] : '';
		?>


		<div style="max-height: 120px; overflow: auto;margin-bottom: 2rem;">

		<?php

		echo '<label for="' . $this->get_field_id( 'block_select' ) . '" class="block_select_label" style="display: block;">' . __( 'Selecciona tu anuncio:', 'Pictau Blocks' ) . '</label>';
		echo '	<select id="' . $this->get_field_id( 'block_select' ) . '" name="' . $this->get_field_name( 'block_select' ) . '" style="width:calc(100% - 2px);">';

		foreach ( $posts as $post ) {
			echo '<option value="'. $post->ID .'" ' . selected( $block_select, $post->ID, false ) . '> ' . esc_attr( get_the_title( $post->ID ) ) . '</option>';
		}


		echo '	</select>';
		?>
		</div>






		<?php
	}

	// save options
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = !empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['block_select'] = !empty( $new_instance['block_select'] ) ? strip_tags( $new_instance['block_select'] ) : '';

		return $instance;


	}
}



/*------------------------------------------------------------------------------------------------------*\

						SETTING UP SHORTCODES

\*------------------------------------------------------------------------------------------------------*/

include_once( plugin_dir_path( __FILE__ ) . '/pictau-blocks-shortcodes.php' );





if( is_admin() )
    $my_settings_page = new pct_blocks_settingsPage();




