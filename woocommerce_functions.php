// Display 24 products per page. Goes in functions.php
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 18;' ), 20 );

// Woocommerce Support
add_theme_support( 'genesis-connect-woocommerce' );

// Change number or products per row to 3
add_filter('loop_shop_columns', 'loop_columns');
if (!function_exists('loop_columns')) {
	function loop_columns() {
		return 3; // 3 products per row
	}
}

// Display Woocommerce Short Product description on Catalog
add_action('woocommerce_after_shop_loop_item_title','woocommerce_template_single_excerpt', 0);
	function woocommerce_template_single_excerpt() { 
		echo '<span class="title-description">';
		echo substr(get_the_excerpt(), 0);
		echo '</span><br />';
}

// Replaces product short description with product content
function woocommerce_template_product_description() {

	woocommerce_get_template( 'single-product/tabs/description.php' );
	
}
add_action( 'woocommerce_single_product_summary', 'the_content', 20 );

// Removes the description and additional information tabs
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {

    unset( $tabs['description'] );      	// Remove the description tab
    // unset( $tabs['reviews'] ); 			// Remove the reviews tab
    unset( $tabs['additional_information'] );  	// Remove the additional information tab

    return $tabs;

}

//* Change number of related products output
function woo_related_products_limit() {
  global $product;
	
	$args['posts_per_page'] = 3;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'jk_related_products_args' );
  function jk_related_products_args( $args ) {
	$args['posts_per_page'] = 3; // 2 related products
	$args['columns'] = 3; // arranged in 2 columns
	return $args;
}

//* Display Woocommerce category on archive page
function wc_category_title_archive_products(){

    $product_cats = wp_get_post_terms( get_the_ID(), 'product_cat' );
    if ( $product_cats && ! is_wp_error ( $product_cats ) ){
        $single_cat = array_shift( $product_cats );
        
        echo '<span class="atmosphere-large-text">';
        echo $single_cat->name;
        echo '</span>';
	}
}
add_action( 'woocommerce_before_shop_loop_item_title', 'wc_category_title_archive_products', 10 );

// Change Out of stock to Sold
add_filter('woocommerce_get_availability', 'availability_filter_func');

function availability_filter_func($availability)
{
	$availability['availability'] = str_ireplace('Out of stock', 'Sold', $availability['availability']);
	return $availability;
}

// Remove Pricing for category
add_action('woocommerce_get_price_html','remove_pricing_from_category');

function remove_pricing_from_category($price){

	global $post, $product;
	$terms = get_the_terms( $product->ID, 'product_cat' );
	foreach ( $terms as $term ) $categories[] = $term->slug;	

		if ( in_array( 'sold', $categories ) ) {
			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10, 2);
			remove_action( 'woocommerce_before_add_to_cart_form', 'woocommerce_template_single_product_add_to_cart', 10, 2);
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			remove_action( 'init', 'woocommerce_add_to_cart_action', 10);			

			return 'Item has been sold';
		}

		else {
			add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10, 2);
			add_action( 'init', 'woocommerce_add_to_cart_action', 10);
			add_action( 'init', 'woocommerce_checkout_action', 10 );
			return $price;
		}

}

// Missing product redirect to product category
add_filter( 'rewrite_rules_array', function( $rules )
{
    $new_rules = array(
        'shop/([^/]*?)/page/([0-9]{1,})/?$' => 'index.php?product_cat=$matches[1]&paged=$matches[2]',
        'shop/([^/]*?)/?$' => 'index.php?product_cat=$matches[1]',
    );
    return $new_rules + $rules;
} );

// Exclude categories from my Google Product Feed
function lw_gpf_exclude_product($excluded, $product_id, $feed_format) {
    // Return TRUE to exclude a product, FALSE to include it, $excluded to use the default behaviour.
    $cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
    if ( in_array( 60, $cats ) ) {
        return TRUE;
    }
    if ( in_array( 63, $cats ) ) {
        return TRUE;
    }
    if ( in_array( 88, $cats ) ) {
        return TRUE;
    }
    if ( in_array( 89, $cats ) ) {
        return TRUE;
    }
    return $excluded;
}
add_filter( 'woocommerce_gpf_exclude_product', 'lw_gpf_exclude_product', 11, 3);

// Remove add to cart button on shop page
// remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');

// Change add to cart button text
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woo_custom_cart_button_text' );    // 2.1 +
 
function woo_custom_cart_button_text() {
 
        return __( 'Download', 'woocommerce' );
 
}

// Change the add to cart text on product archives by product types
add_filter( 'woocommerce_product_add_to_cart_text' , 'custom_woocommerce_product_add_to_cart_text' );
/**
 * custom_woocommerce_template_loop_add_to_cart
*/
function custom_woocommerce_product_add_to_cart_text() {
	global $product;
	
	$product_type = $product->product_type;
	
	switch ( $product_type ) {
		case 'external':
			return __( 'Buy product', 'woocommerce' );
		break;
		case 'grouped':
			return __( 'View products', 'woocommerce' );
		break;
		case 'simple':
			return __( 'Download', 'woocommerce' );
		break;
		case 'variable':
			return __( 'Select options', 'woocommerce' );
		break;
		default:
			return __( 'Read more', 'woocommerce' );
	}
	
}

// Remove unwanted checkout fields if virtual product
add_filter( 'woocommerce_checkout_fields' , 'woo_remove_billing_checkout_fields' );

/**
* Remove unwanted checkout fields
*
* @return $fields array
*/
function woo_remove_billing_checkout_fields( $fields ) {

// check if the cart needs shipping
if ( false == WC()->cart->needs_shipping() ) {

// hide the billing fields
unset($fields['billing']['billing_company']);
unset($fields['billing']['billing_address_1']);
unset($fields['billing']['billing_address_2']);
unset($fields['billing']['billing_city']);
unset($fields['billing']['billing_postcode']);
unset($fields['billing']['billing_country']);
unset($fields['billing']['billing_state']);
unset($fields['billing']['billing_phone']);

// hide the additional information section
add_filter('woocommerce_enable_order_notes_field', '__return_false');
}

return $fields;
}
