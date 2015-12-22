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
