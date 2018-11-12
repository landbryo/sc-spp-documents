<?php
/*
Plugin Name: Simple Password Protected Documents
Plugin URI: https://scree.it
Description: Displays a list of documents behind a password protected page using a shortcode.
Version: 0.1.0
Author: Landon Otis
Author URI: https://scree.it
Text Domain: sc-spp-documents
*/

///////////////////
// ADD TERM PAGE //
///////////////////
function sc_add_taxonomy_meta_field() { ?>

	<div class="form-field">
		<label for="term_meta[taxonomy_sort_order]"><?php _e( 'Sort Order', 'pippin' ); ?></label>
        <select name="term_meta[taxonomy_sort_order]" id="term_meta[taxonomy_sort_order]">
        	<option value="title">Alphabetical</option>
            <option value="date">By Posting Date</option>
            <option value="menu_order">By Custom Menu Order</option>
        </select>
		<p class="description"><?php _e( 'Select a sort order for this term.','pippin' ); ?></p>
	</div>
    
	<div class="form-field">
		<label for="term_meta[taxonomy_sort_direction]"><?php _e( 'Sort Direction', 'pippin' ); ?></label>
        <select name="term_meta[taxonomy_sort_direction]" id="term_meta[taxonomy_sort_direction]">
        	<option value="ASC">Ascending</option>
            <option value="DESC">Descending</option>
        </select>
		<p class="description"><?php _e( 'Select a sort direction for this term.','pippin' ); ?></p>
	</div>

<?php }
add_action( 'document-category_add_form_fields', 'sc_add_taxonomy_meta_field', 10, 2 );

////////////////////
// EDIT TERM PAGE //
////////////////////
function sc_edit_taxonomy_meta_field($term) {
 
	// put the term ID into a variable
	$t_id = $term->term_id;
 
	// retrieve the existing value(s) for this meta field. This returns an array
	$term_meta = get_option( "taxonomy_$t_id" ); ?>

	<tr class="form-field">
		<th scope="row" valign="top"><label for="term_meta[taxonomy_sort_order]"><?php _e( 'Sort Order', 'pippin' ); ?></label></th>
		<td>            
            <select name="term_meta[taxonomy_sort_order]" id="term_meta[taxonomy_sort_order]">
                <option <?php if ( $term_meta['taxonomy_sort_order'] == "title" ) : echo "selected ";  else : echo ''; endif; ?> value="title">Alphabetical</option>
                <option <?php if ( $term_meta['taxonomy_sort_order'] == "date" ) : echo "selected "; else : echo ''; endif; ?> value="date">By Posting Date</option>
                <option <?php if ( $term_meta['taxonomy_sort_order'] == "menu_order" ) : echo "selected ";  else : echo ''; endif; ?> value="menu_order">By Custom Menu Order</option>
            </select>
            
			<p class="description"><?php _e( 'Select a sort order for this term.','pippin' ); ?></p>
		</td>
	</tr>
    
    <tr class="form-field">
    	<th scope="row" valign="top"><label for="term_meta[taxonomy_sort_direction]"><?php _e( 'Sort Direction', 'pippin' ); ?></label></th>
        <td>            
            <select name="term_meta[taxonomy_sort_direction]" id="term_meta[taxonomy_sort_direction]">
                <option <?php if ( $term_meta['taxonomy_sort_direction'] == "ASC" ) : echo "selected ";  else : echo ''; endif; ?> value="ASC">Ascending</option>
                <option <?php if ( $term_meta['taxonomy_sort_direction'] == "DESC" ) : echo "selected "; else : echo ''; endif; ?> value="DESC">Descending</option>
            </select>
            
            <p class="description"><?php _e( 'Select a sort direction for this term.','pippin' ); ?></p>
        </td>
    </tr>
<?php
}
add_action( 'document-category_edit_form_fields', 'sc_edit_taxonomy_meta_field', 10, 2 );

////////////////////////////////////
// SAVE EXTRA TAX FIELDS CALLBACK //
////////////////////////////////////
function sc_save_taxonomy_custom_meta( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ) {
			if ( isset ( $_POST['term_meta'][$key] ) ) {
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		// Save the option array.
		update_option( "taxonomy_$t_id", $term_meta );
	}
}  
add_action( 'edited_document-category', 'sc_save_taxonomy_custom_meta', 10, 2 );  
add_action( 'create_document-category', 'sc_save_taxonomy_custom_meta', 10, 2 );

///////////////////////
// SORT ORDER STRING //
///////////////////////
function sc_get_sort_order( $t_id ) {

	$term_meta = get_option( "taxonomy_$t_id" );
	
	if ( !$term_meta['taxonomy_sort_order'] ) { 
		return 'title'; 
	} else {
		return $term_meta['taxonomy_sort_order'];
	}
}

///////////////////////////
// SORT DIRECTION STRING //
///////////////////////////
function sc_get_sort_direction( $t_id ) {

	$term_meta = get_option( "taxonomy_$t_id" );
	
	if ( !$term_meta['taxonomy_sort_direction'] ) { 
		return 'ASC'; 
	} else {
		return $term_meta['taxonomy_sort_direction'];
	}
}

/////////////////////////
// DOCUMENTS SHORTCODE //
/////////////////////////
function sc_list_documents() {
	
	$args = array(
		'type'=> 'sc_document',
		'parent'=> 0,
		'child_of'=>0,
		'orderby'=> 'name',
		'order'=> 'ASC',
		'hide_empty'=> 1,
		'taxonomy'=> 'document-category'
	);
	
	$document_categories = get_categories( $args );
	
	$output = '<div class="documents-output">';
	
	foreach ( $document_categories as $document_category ) {
	
		$output .= '<div class="parent documents"><h3>' . $document_category->name . '</h3>';
	
		$sort_order = sc_get_sort_order( $document_category->term_id );
		$sort_direction = sc_get_sort_direction( $document_category->term_id );
	
		query_posts(
			array( 
				'post_type' => 'sc_document',
				'parent'=> 0,
				'child_of'=>0,
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'include_children' => false,
						'taxonomy' => 'document-category',
						'terms' => $document_category->term_id,
						'field' => 'term_id'
					)
				),
				'orderby' => $sort_order,
				'order' => $sort_direction 
			)
		);
	
		if( have_posts() ) :
			$output .= '<div class="documents-list">';
			while ( have_posts() ) : the_post();
	
				$output .= '<div class="item">';
	
					$output .= '<a target="_blank" href="'.get_post_meta(get_the_ID(), 'wpcf-document-file', true).'">'.get_the_title().'</a>'; 
	
				$output .= '</div>';
	
			endwhile;
			$output .= '</div>';
		endif;
		
		wp_reset_query();
		
		$subargs = array(
			'type'=> 'sc_document',
			'child_of'=> $document_category->term_id, 
			'orderby'=> 'id', 'order'=> 'ASC', 
			'hide_empty'=> 0, 
			'taxonomy'=> 'document-category'
		);
		
		$subcategories = get_categories( $subargs );
		
		if( $subcategories ) :
	
			foreach ( $subcategories as $subcategory ) {
	
				$output .= '<div class="child documents"><h3>' . $subcategory->name . '</h3>';
				
				$sort_order = get_sort_order( $subcategory->term_id );
				$sort_direction = get_sort_direction( $subcategory->term_id );
	
				query_posts(
					array( 
						'post_type' => 'sc_document',
						'posts_per_page' => -1,
						'tax_query' => array(
							array(
								'taxonomy' => 'document-category',
								'terms' => $subcategory->term_id,
								'field' => 'term_id'
							)
						),
						'orderby' => $sort_order,
						'order' => $sort_direction 
					)
				); 
	
					$output .= '<div class="documents-list">';
					while ( have_posts() ) : the_post();
					
						$output .= '<div class="item">';
	
							$output .= '<a target="_blank" href="'.get_post_meta(get_the_ID(), 'wpcf-document-file', true).'">'.get_the_title().'</a>';
	
						$output .= '</div>';
					
					endwhile;
					$output .= '</div><!--document-list-->';
	
				wp_reset_query();
	
				$output .= '</div><!--child-documents-->';
			}   
	
		endif;
	
		$output .= '</div><!--parent-documents-->';
	
	}
	
	$output .= '</div><!--documents-output-->';
	return $output;
	
}
add_shortcode('documents', 'sc_list_documents'); 

/////////////////////////////////
// REGISTER DOCUMENT POST TYPE //
/////////////////////////////////
function sc_reg_documents() {

	$labels = array(
		'name'                  => 'Documents',
		'singular_name'         => 'Document',
		'menu_name'             => 'Document Types',
		'name_admin_bar'        => 'Document Type',
		'archives'              => 'Document Archives',
		'attributes'            => 'Document Attributes',
		'parent_item_colon'     => 'Parent Document:',
		'all_items'             => 'All Documents',
		'add_new_item'          => 'Add New Document',
		'add_new'               => 'Add New',
		'new_item'              => 'New Document',
		'edit_item'             => 'Edit Document',
		'update_item'           => 'Update Document',
		'view_item'             => 'View Document',
		'view_items'            => 'View Documents',
		'search_items'          => 'Search Document',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Insert into document',
		'uploaded_to_this_item' => 'Uploaded to this document',
		'items_list'            => 'Documents list',
		'items_list_navigation' => 'Documents list navigation',
		'filter_items_list'     => 'Filter documents list',
	);
	$args = array(
		'label'                 => 'Document',
		'description'           => 'Password protected documents.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor' ),
		'taxonomies'            => array( 'document-category' ),
		'hierarchical'          => true,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'sc_document', $args );

}
add_action( 'init', 'sc_reg_documents', 0 );

//////////////////
// REGISTER TAX //
//////////////////
function sc_reg_doc_tax() {

	$labels = array(
		'name'                       => 'Document Category',
		'singular_name'              => 'Document Categories',
		'menu_name'                  => 'Document Categories',
		'all_items'                  => 'All Categories',
		'parent_item'                => 'Parent Category',
		'parent_item_colon'          => 'Parent Category:',
		'new_item_name'              => 'New Category Name',
		'add_new_item'               => 'Add New Category',
		'edit_item'                  => 'Edit Category',
		'update_item'                => 'Update Category',
		'view_item'                  => 'View Category',
		'separate_items_with_commas' => 'Separate categories with commas',
		'add_or_remove_items'        => 'Add or remove categories',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Categories',
		'search_items'               => 'Search Categories',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No categories',
		'items_list'                 => 'Items category',
		'items_list_navigation'      => 'Category list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => false,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'document-category', array( 'sc_document' ), $args );

}
add_action( 'init', 'sc_reg_doc_tax', 0 );

?>