<?php
/*
Plugin Name: Xpand Gallery for Wordpress (Lite Version)
Plugin URI: https://wordpress.org/plugins/xpand-image-gallery/
Description: Dynamic Image Gallery
Author: Hey Development
Version: 1.0.1
Author URI: http://dev.hey.uy/
*/

//-- MENU OPCIONES --
/** LLamar funciones */
add_action( 'admin_menu', 'xpand_gallery_menu' );
add_action('admin_init', 'registrar_opciones');

/** Step 1. */
function xpand_gallery_menu() {
    add_options_page( 'Xpand Gallery Options', 'Xpand Gallery', 'manage_options', 'xpand-gallery-options', 'xpand_gallery_options' );
}

function registrar_opciones(){
    register_setting('xpgal_options','xpgalPreviewHeight');
    register_setting('xpgal_options','xpgalAnimSpeed');
    register_setting('xpgal_options','xpgalLinkLove');
}

/** Step 3. */
function xpand_gallery_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    /*-- BEGIN SETTINGS FORM (HTML) --*/
    ?>

    <div class="wrap">
        <h1>Xpand Gallery Options (Lite)</h1>
        <div style="width: 50%; float: left; padding: 15px; border-radius: 15px; background: #f9f9f9;">
            <form method="post" action="options.php">

                <?php settings_fields('xpgal_options');
                do_settings_sections( 'xpgal_options' ); ?>

                <input type="checkbox" name="xpgalLinkLove" value="1" <?php echo checked(1 == get_option('xpgalLinkLove', 0)); ?>>Add our link to support the lite version!<br>
                <br>
                <!-- Option -- Gallery Preview Height -->
                <span>Set preview height (in pixels).</span><br><input type="text" name="xpgalPreviewHeight" value="<?php echo get_option('xpgalPreviewHeight', '550'); ?>"><br>
                <br>
                <!-- Option -- Gallery Animation Speed -->
                <span>Set animation speed.</span><br><input type="text" name="xpgalAnimSpeed" value="<?php echo get_option('xpgalAnimSpeed', '350'); ?>">

                <h3>Advanced options*</h3>
                <hr>
                <div style="color: #999;">
                <input type="checkbox" name="xpgalWidgetBar" value="1" disabled>
                <span><strong>Enable widgetized sidebar on preview.</strong></span><br>
                <p style="font-size:11px; margin:0 5px; padding: 0 15% 0 18px; color:#999;">Having this option checked shows an area to the right of the image which can contain the title and description as well as any widgets you wish to display from the widgets menu.</p>
                <br>
                <hr>
                <span>Select background color.</span><br>
                <input type="text" name="xpgalBgColor" value="#222" class="xpg-color-picker" disabled><br>
                <br>
                <span>Select preview text color.</span><br>
                <input type="text" name="xpgalTxtColor" value="#999" class="xpg-color-picker" disabled><br>
                <br>
                <input type="checkbox" name="xpgalShowTitle" value="1" disabled>
                <span>Show image title on preview.</span><br>
                <br>
                <input type="checkbox" name="xpgalShowDesc" value="1" disabled>
                <span>Show image description on preview.</span><br>
                <br>
                <p>Select widget area position</p>
                <input type="radio" name="xpgalWidgetPos" value="top" disabled />
                <span>Widgets on top.</span><br />
                <input type="radio" name="xpgalWidgetPos" value="bottom" disabled />
                <span>Widgets at the bottom.</span><br />
                <br>
                <input type="checkbox" name="xpgalDefaultOpen" value="1" disabled>
                <span>Start with first image preview open.</span><br>
                <p style="font-size:11px; margin:0 5px; padding: 0 15% 0 18px; color:#888;">This option leaves the first image in the gallery open by default when the page is loaded.</p>
                <br>
                </div>
                <p style="text-align: right;">*Advanced options are only available in the PRO version.</p>
                <hr>
                <?php submit_button(); ?>
            </form>
            <p style="text-align: right; font-size:11px; margin:0; color:#888;">Learn more about our plugins at: <a href="http://dev.hey.uy">dev.hey.uy</a>!</p>
        </div>
        <div style="width: 250px; float: left; padding: 0 15px 15px;">
            <a href="http://dev.hey.uy" title="Hey! Plugin Development"><img src="<?php echo plugins_url('/img/heybanner.jpg',__FILE__); ?>" alt="Hey! Plugins" style="border-radius: 7%;"></a>
            <a href="http://dev.hey.uy/xpand-gallery-for-wordpress" title="Xpand Gallery PRO - Plugin"><img src="<?php echo plugins_url('/img/xpgallerybanner.jpg',__FILE__); ?>" alt="Xpand Gallery PRO" style="border-radius: 7%; margin-top: 15px;"></a>
        </div>
    </div>

<?php } /*-- END --*/

// Add settings link on plugin page
function xpand_gallery_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=xpand-gallery-options.php">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'xpand_gallery_settings_link' );

function carga_scripts() {
	wp_enqueue_style( 'xpgal_style', plugins_url('/css/style.css',__FILE__) );
	wp_enqueue_script('Modernizr', plugins_url('/js/modernizr.custom.js',__FILE__));
    wp_enqueue_script('grid', plugins_url('/js/grid.js',__FILE__), array( 'jquery', 'Modernizr' ), '', false );
	}

add_action('wp_enqueue_scripts', 'carga_scripts');

remove_shortcode('gallery', 'gallery_shortcode');
add_shortcode('gallery', 'custom_gallery');

function custom_gallery($attr) {
	$post = get_post();

    //Cargo las Opciones del Plugin
    $xpgalPreviewHeight = get_option('xpgalPreviewHeight', '550');
    $xpgalAnimSpeed = get_option('xpgalAnimSpeed', '350');

    if(get_option('xpgalLinkLove', 0) == 1){ $xpgalLinkLove = true; }
    else {$xpgalLinkLove = false;}

	static $instance = 0;
	$instance++;

	if ( ! empty( $attr['ids'] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise.
		if ( empty( $attr['orderby'] ) )
			$attr['orderby'] = 'post__in';
		$attr['include'] = $attr['ids'];
	}

	// Allow plugins/themes to override the default gallery template.
	$output = apply_filters('post_gallery', '', $attr);
	if ( $output != '' )
		return $output;

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'li',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	), $attr));

	$id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	if ( !empty($include) ) {
		$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( !empty($exclude) ) {
		$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	} else {
		$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
	}

	if ( empty($attachments) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment )
			$output .= wp_get_attachment_link_dani($att_id, $size, true) . "\n";
		return $output;
	}

	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$icontag = tag_escape($icontag);
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) )
		$itemtag = 'dl';
	if ( ! isset( $valid_tags[ $captiontag ] ) )
		$captiontag = 'dd';
	if ( ! isset( $valid_tags[ $icontag ] ) )
		$icontag = 'dt';

	$columns = intval($columns);
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";

	$gallery_style = $gallery_div = '';
	if ( apply_filters( 'use_default_gallery_style', true ) )
		$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				float: {$float};
				margin-top: 10px;
				text-align: center;
				width: {$itemwidth}%;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
		</style>
		<!-- see gallery_shortcode() in wp-includes/media.php -->";
	$size_class = sanitize_html_class( $size );
	$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";
	$output = "<ul id='og-grid' class='og-grid'>";

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		//$link = isset($attr['link']) && 'file' == $attr['link'] ? wp_get_attachment_link_dani($id, $size, false, false) : wp_get_attachment_link_dani($id, $size, true, false, false, true);
        $link = wp_get_attachment_link_dani($id, $size, true, false, false, true);

		$output .= "<{$itemtag} class='gallery-item'>";
		
		$output .= "$link";
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
		$output .= "</{$itemtag}>";
	}

	$output .= "
			
		</ul>\n";
		
		$output .= "
		<script type='text/javascript'>
		jQuery(window).load(function(){
				Grid.init($xpgalPreviewHeight, $xpgalAnimSpeed, '$xpgalLinkLove');
				jQuery('#og-grid').parents().css('position', 'static');
		});
		</script>\n";

        $output .='<div class="og-expander hide">
      <div tabindex="0" class="og-expander-inner">
        <span class="og-close"></span><div class="og-fullimg">
    <div class="og-loading" style="display: none;"></div>
    <img src="" style="display: inline-block;"></div>
    <div class="separacion"></div>
    <div class="og-details">
        <p>Imagen: 3</p><p></p>
      <div class="lista">
        <li><a href="" class="forceDownload">Descargar Imagen</a></li>
        <li><a href="http://dev.hey.uy/gallery-v1-0/attachment/3/" class="descargas">Comentar Imagen</a></li>
        <li class="prevLnk">« Anterior</li><li class="nextLnk">Siguiente »</li>
    </div></div></div></div>';

	return $output;
}

function wp_get_attachment_link_dani( $id = 0, $size = 'thumbnail', $permalink = false, $icon = false, $text = false, $extra = false  ) {
	$id = intval( $id );
	$_post = get_post( $id );

	if ( empty( $_post ) || ( 'attachment' != $_post->post_type ) || ! $url = wp_get_attachment_url( $_post->ID ) )
		return __( 'Missing Attachment' );

	if ( $permalink )
		$url = get_attachment_link( $_post->ID );

	$post_title = esc_attr( $_post->post_title );

	if ( $text )
		$link_text = $text;
	elseif ( $size && 'none' != $size )
		$link_text = wp_get_attachment_image( $id, $size, $icon );
	else
		$link_text = '';

	if ( trim( $link_text ) == '' )
		$link_text = $_post->post_title;
		
		
		//dani
		if ( $extra )
		$dani = wp_get_attachment_url($id);
		
		$attachment_title = get_the_title($id);
		$parent_title = get_the_title($_post->post_parent);
		$full_parent_permalink = post_permalink ($_post->post_parent);
		
		$parent_permalink = basename($full_parent_permalink);
	
	return apply_filters( 'wp_get_attachment_link_dani', "<a href='$url' data-postname='$parent_title' title='$post_title' data-permalink='$parent_permalink' data-largesrc='$dani' data-href='$url' data-title='Imagen: $attachment_title'>$link_text</a>", $id, $size, $permalink, $icon, $text );
}