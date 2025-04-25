<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Sify_Brand_Central
 */

get_header();
?>

	<div id="primary" class="container site-main" role="main">
        <div class="row">
            <div class="col-sm-9">
                <div class="post-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:20px;">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        echo '<div class="post-card" style="border:1px solid #ddd;padding:20px;">';
                        echo '<a href="' . get_permalink() . '"><h3>' . get_the_title() . '</h3></a>';
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'medium', [ 'style' => 'max-width:100%;height:auto;' ] );
                        }
                        echo '</div>';
                    endwhile; // End of the loop.
                    ?>
                    <!-- <div class="post-card" style="border:1px solid #ddd;padding:20px;">
                        <h3>Download Link</h3>
                        <a href="<?php //echo get_post_meta( get_the_ID(), 'sdm_download_link', true ); ?>" target="_blank">Download</a>
                    </div> -->
                </div>
            </div>
            <div class="col-sm-3">
                <div class="sidebar">
                    <h3>Categories</h3>
                    <?php
                    // Get current post terms in 'sdm_categories'
                    $terms = get_the_terms( get_the_ID(), 'sdm_categories' );

                    if ( $terms && ! is_wp_error( $terms ) ) {
                        // Loop through terms to find the parent
                        foreach ( $terms as $term ) {
                            if ( $term->parent != 0 ) {
                                // Get the parent term
                                $parent_term = get_term( $term->parent, 'sdm_categories' );
                                break;
                            } else {
                                // If it's already a parent term
                                $parent_term = $term;
                                break;
                            }
                        }

                        // If parent term found, get its children (subcategories)
                        if ( $parent_term ) {
                            $subcategories = get_terms( array(
                                'taxonomy'   => 'sdm_categories',
                                'hide_empty' => false,
                                'parent'     => $parent_term->term_id,
                            ) );

                            if ( ! empty( $subcategories ) && ! is_wp_error( $subcategories ) ) {
                                echo '<aside class="category-sidebar">';
                                foreach ( $subcategories as $subcategory ) {
                                    $link = get_term_link( $subcategory );
                                    echo '<li><a href="' . esc_url( $link ) . '">' . esc_html( $subcategory->name ) . '</a></li>';
                                }
                                echo '</ul></aside>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div><!-- .row -->
    </div><!-- #main -->

<?php
get_footer();
