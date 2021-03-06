<?php
/**
 * This file contains the Arconix_Portfolio class.
 *
 * This class handles the creation of the "Portfolio" post type, and creates a
 * UI to display the Portfolio-specific data on the admin screens.
 */

class Arconix_Portfolio {

    /**
     * Construct Method
     */
    function __construct() {

        /** Post Type and Taxonomy creation */
	add_action( 'init', array( $this, 'create_post_type' ) );
	add_action( 'init', array( $this, 'create_taxonomy' ) );
   

        /** Post Thumbnail Support */
        add_action( 'after_setup_theme', array( $this, 'add_post_thumbnail_support' ), '9999' );
	add_image_size( 'portfolio-mini', 125, 125, TRUE );
	add_image_size( 'portfolio-thumb', 275, 200, TRUE );
	add_image_size( 'portfolio-large', 620, 9999 );

        /** Modify the Post Type Admin Screen */
        add_action( 'admin_head', array( $this, 'admin_style' ) );
	add_filter( 'manage_edit-portfolio_columns', array( $this, 'columns_filter' ) );
	add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );
	add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

        /** Add our Scripts */
	add_action( 'init', array( $this , 'register_script' ) );
	add_action( 'wp_footer', array( $this , 'print_script' ) );
	add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_css' ) );

        /** Create/Modify Dashboard Widgets */
	add_action( 'right_now_content_table_end', array( $this, 'right_now' ) );
	add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );

        /** Add Shortcode */
	add_shortcode( 'dal_portfolio', array( $this, 'portfolio_shortcode' ) );
    add_filter( 'widget_text', 'do_shortcode' );

      if (function_exists('mfields_set_default_object_terms')) {
            add_action( 'save_post', 'mfields_set_default_object_terms', 100, 2 );
        }

    }

    /**
     * This var is used in the shortcode to flag the loading of javascript
     * @var type boolean
     */
    static $load_js;


    /**
     * Create Portfolio Post Type
     *
     * @since 0.9
     */
    function create_post_type() {

	$args = apply_filters( 'arconix_portfolio_post_type_args',
	    array(
		'labels' => array(
		    'name' => __( 'Aplicaciones participantes', 'acp' ),
		    'singular_name' => __( 'Aplicación', 'acp' ),
		    'add_new' => __( 'Add New', 'acp' ),
		    'add_new_item' => __( 'Add New Aplicación', 'acp' ),
		    'edit' => __( 'Edit', 'acp' ),
		    'edit_item' => __( 'Edit Aplicación', 'acp' ),
		    'new_item' => __( 'New Aplicación', 'acp' ),
		    'view' => __( 'View Aplicaciones', 'acp' ),
		    'view_item' => __( 'View Aplicación', 'acp' ),
		    'search_items' => __( 'Search Aplicaciones', 'acp' ),
		    'not_found' => __( 'No Aplicaciones found', 'acp' ),
		    'not_found_in_trash' => __( 'No Aplicaciones found in Trash', 'acp' ),

		),
		'public' => true,
		'query_var' => true,
		'menu_position' => 20,
		'menu_icon' => ACP_URL . 'images/portfolio-icon-16x16.png',
		'has_archive' => true,
		'supports' => array( 'title', 'thumbnail' ),
		'rewrite' => array( 'slug' => 'portfolio', 'with_front' => false ),
        //'taxonomies' => array('post_tag')
	    )
	);

	register_post_type( 'portfolio' , $args);
    }

    /**
     * Create the Custom Taxonomy
     *
     * @since 0.9
     */
    function create_taxonomy() {

	$args = apply_filters( 'arconix_portfolio_taxonomy_args',
	    array(
		'labels' => array(
		    'name' => __( 'Premiado', 'acp' ),
		    'singular_name' => __( 'premio', 'acp' ),
		    'search_items' =>  __( 'Search premiados', 'acp' ),
		    'popular_items' => __( 'ganadores popular', 'acp' ),
		    'all_items' => __( 'All Features', 'acp' ),
		    'parent_item' => null,
		    'parent_item_colon' => null,
		    'edit_item' => __( 'Edit Premiado' , 'acp' ),
		    'update_item' => __( 'Update Premiado', 'acp' ),
		    'add_new_item' => __( 'Add New Premiado', 'acp' ),
		    'new_item_name' => __( 'New Premiado', 'acp' ),
		    'separate_items_with_commas' => __( 'Separate Premiado with commas', 'acp' ),
		    'add_or_remove_items' => __( 'Add or remove Premiado', 'acp' ),
		    'choose_from_most_used' => __( 'Choose from the most used Premiado', 'acp' ),
		    'menu_name' => __( 'Premiado', 'acp' ),
		),
		'hierarchical' => true,
		'show_ui' => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var' => true,
		'rewrite' => array( 'slug' => 'Premiados' )
	    )
	);
  
    if (!taxonomy_exists('feature')) {
    	register_taxonomy( 'feature', 'portfolio', $args );
        wp_insert_term('empty', 'feature');
    };

    if (!taxonomy_exists('apps_tags')) {
       
        register_taxonomy( 'apps_tags', 'portfolio', array( 'hierarchical' => false, 'label' => __('apps_tags'), 'query_var' => 'apps_tags', 'rewrite' => array( 'slug' => 'apps_tags' ) ) );
    };

    $labels = array(
        'name' => _x( 'Tracks', 'taxonomy general name' ),
        'singular_name' => _x( 'track', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search tracks' ),
        'all_items' => __( 'All tracks' ),
        'parent_item' => __( 'Parent track' ),
        'parent_item_colon' => __( 'Parent track:' ),
        'edit_item' => __( 'Edit track' ), 
        'update_item' => __( 'Update track' ),
        'add_new_item' => __( 'Add New track' ),
        'new_item_name' => __( 'New track' ),
        'menu_name' => __( 'Competition Tracks' ),
      );    

    if (!taxonomy_exists('apps_tracks')) {

        register_taxonomy('apps_tracks', array('portfolio', 'dal_country'), array(
            'hierarchical' => True,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => 'track', 
            'rewrite' => array( 'slug' => 'track' ) )
        );
    };

     if (!taxonomy_exists('apppais')) {
        register_taxonomy( 'apppais', 'portfolio', array( 'hierarchical' => false, 'label' => __('país de la app'), 'query_var' => 'apppais', 'rewrite' => array( 'slug' => 'apppais' ) ) );
        
      wp_insert_term('Argentina', 'apppais');
      wp_insert_term('Bolivia', 'apppais');
      wp_insert_term('Brasil', 'apppais');
      wp_insert_term('Chile', 'apppais');
      wp_insert_term('Colombia', 'apppais');
      wp_insert_term('Costa Rica', 'apppais');
      wp_insert_term('Cuba', 'apppais');
      wp_insert_term('Ecuador', 'apppais');
      wp_insert_term('El Salvador', 'apppais');
      wp_insert_term('Guatemala', 'apppais');
      wp_insert_term('Haití', 'apppais');
      wp_insert_term('Honduras', 'apppais');
      wp_insert_term('México', 'apppais');
      wp_insert_term('Nicaragua', 'apppais');
      wp_insert_term('Panamá', 'apppais');
      wp_insert_term('Paraguay', 'apppais');
      wp_insert_term('Perú', 'apppais');
      wp_insert_term('República Dominicana', 'apppais');
      wp_insert_term('Uruguay', 'apppais');
      wp_insert_term('Venezuela', 'apppais');
        };

    }



    /**
     * Correct messages when Portfolio post type is saved
     *
     * @global type $post
     * @global type $post_ID
     * @param type $messages
     * @return type
     * @since 0.9
     */
    function updated_messages( $messages ) {
	global $post, $post_ID;

	$messages['portfolio'] = array(
	    0 => '', // Unused. Messages start at index 1.
	    1 => sprintf( __('DAL Portfolio Item updated. <a href="%s">View app</a>'), esc_url( get_permalink($post_ID) ) ),
	    2 => __('Custom field updated.'),
	    3 => __('Custom field deleted.'),
	    4 => __('DAL Portfolio item updated.'),
	    /* translators: %s: date and time of the revision */
	    5 => isset($_GET['revision']) ? sprintf( __('DAL Portfolio item restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
	    6 => sprintf( __('DAL Portfolio item published. <a href="%s">View app </a>'), esc_url( get_permalink($post_ID) ) ),
	    7 => __('DAL Portfolio item saved.'),
	    8 => sprintf( __('DAL Portfolio item submitted. <a target="_blank" href="%s">Preview portfolio item</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	    9 => sprintf( __('DAL Portfolio item scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview DAL portfolio item</a>'),
	      // translators: Publish box date format, see http://php.net/date
	      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
	    10 => sprintf( __('DAL Portfolio item draft updated. <a target="_blank" href="%s">Preview app</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
	);

      return $messages;
    }

    /**
     * Filter the columns on the admin screen and define our own
     *
     * @param type $columns
     * @return string
     * @since 0.9
     */
    function columns_filter ( $columns ) {

	$columns = array(
	    'cb' => '<input type="checkbox" />',
	    'portfolio_thumbnail' => __( 'Image', 'acp' ),
	    'title' => __( 'Title', 'acp' ),
	    'portfolio_description' => __( 'Description', 'acp' ),
	    'portfolio_features' => __( 'Features', 'acp' )
	);

	return $columns;
    }

    /**
     * Filter the data that shows up in the columns we defined above
     *
     * @global type $post
     * @param type $column
     * @since 0.9
     */
    function columns_data( $column ) {

	global $post;

	switch( $column ) {
	    case "portfolio_thumbnail":
		printf( '<p>%s</p>', the_post_thumbnail('portfolio-mini' ) );
		break;
	    case "portfolio_description":
		the_excerpt();
		break;
	    case "portfolio_features":
		echo get_the_term_list( $post->ID, 'feature', '', ', ', '' );
		break;
	}
    }

    /**
     * Check for post-thumbnails and add portfolio post type to it
     *
     * @global type $_wp_theme_features
     * @since 0.9
     */
    function add_post_thumbnail_support() {

	global $_wp_theme_features;

	if( !isset( $_wp_theme_features['post-thumbnails'] ) ) {

	    $_wp_theme_features['post-thumbnails'] = array( array( 'portfolio' ) );
	}

	elseif( is_array( $_wp_theme_features['post-thumbnails'] ) ) {

	    $_wp_theme_features['post-thumbnails'][0][] = 'portfolio';
	}
    }

    /**
     * Portfolio Shortcode
     *
     * @param type $atts
     * @param type $content
     * @since 0.9
     * @version 1.1
     */
    function portfolio_shortcode( $atts, $content = null ) {
        
	/*
	Supported Attributes
	    link =>  'page', image
	    thumb => any built-in image size
	    full => any built-in image size (this setting is ignored of 'link' is set to 'page')
            title => above, below or 'blank' ("yes" is converted to "above" for backwards compatibility)
	    display => content, excerpt (leave blank for nothing)
            heading => When displaying the 'feature' items in a row above the portfolio items, define the heading text for that section.
            orderby => date or any other orderby param available. http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
            order => ASC (ascending), DESC (descending)
            terms => a 'feature' tag you want to filter on
            operator => 'IN', 'NOT IN' filter for the term tag above

	*/

	/**
	 * Currently 'image' is the only supported link option right now
	 *
	 * While 'page' is an available option, it can potentially require a lot of work on the part of the
	 * end user since the plugin can't possibly know what theme it's being used with and create the necessary
	 * page structure to properly integrate into the theme. Selecting page is only advised for advanced users.
	 */

	/** Load the javascript */
	self::$load_js = true;
	/** Shortcode defaults */
	$defaults = apply_filters( 'arconix_portfolio_shortcode_args',
	    array(
		'link' => 'page',
		'thumb' => 'portfolio-thumb',
		'full' => 'portfolio-large',
        'title' => 'above',
		'display' => '',
        'heading' => 'Display',
		'orderby' => 'date',
		'order' => 'desc',
        'terms' => '',
        'operator' => 'IN',
        'apppais'=>$apppais,
	    )
	);

	extract( shortcode_atts( $defaults, $atts ) );
        
        if( $title == "yes" ) $title == "above"; // For backwards compatibility

	/** Default Query arguments -- can be overridden by filter */
	$args = apply_filters( 'arconix_portfolio_shortcode_query_args',
	    array(
		'post_type' => 'portfolio',
		'posts_per_page' => -1, // show all
        'meta_key' => '_thumbnail_id', // Should pull only items with featured images
		'orderby' => $orderby,
		'order' => $order,

	    )
	);

        /** If the user has defined any tax (feature) terms, then we create our tax_query and merge to our main query  */
        //si tiene un lugar hace esto
        if( $apppais ) {
            
            $tax_query_args = array(
                'tax_query' => array(
                   /*array(
                        'taxonomy' =>'feature',
                        'field' => 'slug',
                        'terms' => $terms,
                        'operator' => $operator  

                      ),*/
                    array(
                        'taxonomy' => 'apppais',
                        'terms' => $apppais,
                        'field' => 'slug',
                    )         
                     
                )            
            );
            
            /** Join the tax array to the general query */
            $args = array_merge( $args, $tax_query_args );
        }
        //si tiene un pais agrega esto
    /*if ($apppais){
        $tax_query_args2= array(
                'tax_query' => array(
                    array(
                        'taxonomy' => 'apppais',
                        'field' => 'slug',
                        'terms' => $apppais,
                        'operator' => $operator  

                      )                    
                )            
            );
        $args = array_merge( $args, $tax_query_args2 );
    }*/

	$return = '';

        /** Create a new query based on our own arguments */
	$portfolio_query = new WP_Query( $args );

        if( $portfolio_query->have_posts() ) {
            $a ='';

            
            if( $terms ) {
                
                /** Change the get_terms argument based on the shortcode $operator */
                switch( $operator) {
                    case "IN":
                        $a = array( 'include' => $terms );
                        break;
                
                    case "NOT IN":
                        $a = array( 'exclude' => $terms );
                        break;
                
                    default:
                        break;
                }
                
            }


            /** We're simply recycling the variable at this point */
            $terms = get_terms( 'feature', $a );
            
            /** If there are multiple terms in use, then run through our display list */
            if( count( $terms ) > 1 )  {
                $return .= '<ul class="arconix-portfolio-features"><li class="arconix-portfolio-category-title">';
                $return .= $heading;
                $return .= '</li><li class="active"><a href="javascript:void(0)" class="all">all</a></li>';

                $term_list = '';

                /** break each of the items into individual elements and modify its output */
                foreach( $terms as $term ) {

                    $term_list .= '<li><a href="javascript:void(0)" class="' . $term->slug . '">' . $term->name . '</a></li>';
                }

                /** Return our modified list */
                $return .= $term_list . '</ul>';
            }

            $return .= '<ul class="arconix-portfolio-grid">';

            while( $portfolio_query->have_posts() ) : $portfolio_query->the_post();

                /** Get the terms list */
                $terms = get_the_terms( get_the_ID(), 'feature' );
                

                /** Add each term for a given portfolio item as a data type so it can be filtered by Quicksand */
                $return .= '<li data-id="id-' . get_the_ID() . '" data-type="';
                foreach ( $terms as $term ) {
                    $return .= $term->slug . ' ';
                }
                $return .= '">';

                /** Above image Title output */
                if( $title == "above" ) $return .= '<div class="arconix-portfolio-title">' . get_the_title() . '</div>';

                /** Handle the image link */
                switch( $link ) {
                    case "page" :
                        $return .= '<a href="' . get_permalink() . '" rel="bookmark">';
                        
			$return .= get_the_post_thumbnail( get_the_ID(), $thumb );
			$return .= '</a>';
                        break;

                    case "image" :
                        $_portfolio_img_url = wp_get_attachment_image_src( get_post_thumbnail_id(), $full );

                        $return .= '<a href="' . $_portfolio_img_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '" >';
                        $return .= get_the_post_thumbnail( get_the_ID(), $thumb );
                        $return .= '</a>';
                        break;

                    default : // If it's anything else, return nothing.
                        break;
                }

		/** Below image Title output */
                if( $title == "below" ) $return .= '<div class="arconix-portfolio-title">' . get_the_title() . '</div>';

                /** Display the content */
                switch( $display ) {
                    case "content" :
                        $return .= '<div class="arconix-portfolio-text">' . get_the_content() . '</div>';
                        break;

                    case "excerpt" :
                        $return .= '<div class="arconix-portfolio-text">' . get_the_excerpt() . '</div>';
                        break;

                    default : // If it's anything else, return nothing.
                        break;
                }

                $return .= '</li>';

            endwhile;

            $return .= '</ul>';
        }

	return $return;
    }


    /**
     * Add the Portfolio Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     * @since 0.9
     */
    function right_now() {
	include_once( dirname( __FILE__ ) . '/views/right-now.php' );
    }


    /**
     * Style the portfolio icon on the admin screen
     *
     * @since 0.9
     */
    function admin_style() {
	printf( '<style type="text/css" media="screen">.icon32-posts-portfolio { background: transparent url(%s) no-repeat !important; }</style>', ACP_URL . 'images/portfolio-icon-32x32.png' );
    }


    /**
     * Register the necessary javascript, which can be overriden by creating your own file and
     * placing it in the root of your theme's folder
     *
     * @since 1.0
     * @version 1.1.0
     */
    function register_script() {

        wp_register_script( 'jquery-quicksand', ACP_URL . 'includes/js/jquery.quicksand.js', array( 'jquery' ), '1.2.2', true );
        wp_register_script( 'jquery-easing', ACP_URL . 'includes/js/jquery.easing.1.3.js', array( 'jquery' ), '1.3', true );

	if( file_exists( get_stylesheet_directory() . "/arconix-portfolio.js" ) ) {
	    wp_register_script( 'arconix-portfolio-js', get_stylesheet_directory_uri() . '/arconix-portfolio.js', array( 'jquery-quicksand', 'jquery-easing' ), ACP_VERSION, true );
	}
	elseif( file_exists( get_template_directory() . "/arconix-portfolio.js" ) ) {
	    wp_register_script( 'arconix-portfolio-js', get_template_directory_uri() . '/arconix-portfolio.js', array( 'jquery-quicksand', 'jquery-easing' ), ACP_VERSION, true );
	}
	else {
            wp_register_script( 'arconix-portfolio-js', ACP_URL . 'includes/js/portfolio.js', array( 'jquery-quicksand', 'jquery-easing' ), ACP_VERSION, true );
	}
    }


    /**
     * Check the state of the variable. If true, load the registered javascript
     *
     * @since 1.0
     */
    function print_script() {

	if( ! self::$load_js )
	    return;

	wp_print_scripts( 'arconix-portfolio-js' );
    }


    /**
     * Load the plugin css. If the css file is present in the theme directory, it will be loaded instead,
     * allowing for an easy way to override the default template
     *
     * @since 0.9
     * @version 1.0
     */
    function enqueue_css() {

	if( file_exists( get_stylesheet_directory() . "/arconix-portfolio.css" ) ) {
	    wp_enqueue_style( 'arconix-portfolio', get_stylesheet_directory_uri() . '/arconix-portfolio.css', array(), ACP_VERSION );
	}
	elseif( file_exists( get_template_directory() . "/arconix-portfolio.css" ) ) {
	    wp_enqueue_style( 'arconix-portfolio', get_template_directory_uri() . '/arconix-portfolio.css', array(), ACP_VERSION );
	}
	else {
	    wp_enqueue_style( 'arconix-portfolio', plugins_url( '/portfolio.css', __FILE__), array(), ACP_VERSION );
	}
    }


    /**
     * Adds a widget to the dashboard.
     *
     * @since 0.9.1
     */
    function register_dashboard_widget() {
        wp_add_dashboard_widget( 'ac-portfolio', 'Arconix Portfolio', array( $this, 'dashboard_widget_output' ) );
    }


    /**
     * Output for the dashboard widget
     *
     * @since 0.9.1
     * @version 1.0
     */
    function dashboard_widget_output() {

        echo '<div class="rss-widget">';

        wp_widget_rss_output( array(
            'url' => 'http://arconixpc.com/tag/arconix-portfolio/feed', // feed url
            'title' => 'Arconix Portfolio Posts', // feed title
            'items' => 3, //how many posts to show
            'show_summary' => 1, // display excerpt
            'show_author' => 0, // display author
            'show_date' => 1 // display post date
        ) );

        echo '<div class="acp-widget-bottom"><ul>'; ?>
            <li><a href="http://arcnx.co/apwiki"><img src="<?php echo ACP_URL . 'images/page-16x16.png'?>">Wiki Page</a></li>
            <li><a href="http://arcnx.co/aphelp"><img src="<?php echo ACP_URL . 'images/help-16x16.png'?>">Support Forum</a></li>
            <li><a href="http://arcnx.co/aptrello"><img src="<?php echo ACP_URL . 'images/trello-16x16.png'?>">Dev Board</a></li>
        <?php echo '</ul></div>';
        echo "</div>";

        // handle the styling
        echo '<style type="text/css">
            #ac-portfolio .rsssummary { display: block; }
            #ac-portfolio .acp-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
            #ac-portfolio .acp-widget-bottom ul { list-style: none; }
            #ac-portfolio .acp-widget-bottom ul li { display: inline; padding-right: 9%; }
            #ac-portfolio .acp-widget-bottom img { padding-right: 3px; vertical-align: top; }
        </style>';
    }
/**
 * Define default terms for custom taxonomies in WordPress 3.0.1
 *
 * @author    Michael Fields     http://wordpress.mfields.org/
 * @props     John P. Bloch      http://www.johnpbloch.com/
 *
 * @since     2010-09-13
 * @alter     2010-09-14
 *
 * @license   GPLv2
 */
function mfields_set_default_object_terms( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
            'feature' => array( 'empty' ),
            );
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}




}




?>