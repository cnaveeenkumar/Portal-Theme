<?php get_header(); ?>
<style>.term-card a {
    text-decoration: none;
    color: #000;
    line-height: 3;
}
</style>
<div class="container" style="padding: 40px;">
    <div class="row">
        <div class="col-sm-12">
            <div class="breadcrumbs" style="margin-bottom: 20px;">
                <?php if( function_exists( 'bcn_display' ) ) bcn_display(); ?>
            </div>
        </div>
    </div>
    <?php
    $term = get_queried_object();

    // Get direct child terms of current term
    $child_terms = get_terms([
        'taxonomy'   => 'sdm_categories',
        'parent'     => $term->term_id,
        'hide_empty' => false,
    ]);

    if (!empty($child_terms)) {
        echo '<h2 class="mb-4">' . esc_html($term->name) . '</h2>';
        echo '<div class="term-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(400px, 1fr));gap:20px;">';
            foreach ($child_terms as $child) {
                echo '<div class="term-card" style="border:1px solid #ccc;text-align:center;">';
                    echo '<a href="' . esc_url(get_term_link($child)) . '">';
                    // Display taxonomy image if available
                    if (function_exists('z_taxonomy_image_url')) {
                        $image_url = z_taxonomy_image_url($child->term_id);
                        if ($image_url) {
                            echo '<div class="category-image"><img src="' . esc_url($image_url) . '" alt="' . esc_attr($child->name) . '"></div>';
                        }
                    }
                    echo esc_html($child->name);
                    echo '</a>';
                echo '</div>';
            }
        echo '</div>';

    } else {
        echo "<div class='container'>";
            echo "<div class='row'>";
                echo "<div class='col-sm-9'>";
                // No child terms — show posts under this taxonomy term
                echo '<h2 class="mb-4">' . esc_html($term->name) . '</h2>';

                $query = new WP_Query([
                    'post_type'      => 'sdm_downloads',
                    'posts_per_page' => -1,
                    'tax_query'      => [[
                        'taxonomy' => 'sdm_categories',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ]],
                ]);

                if ($query->have_posts()) {
                    echo '<div class="post-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:20px;">';

                    while ($query->have_posts()) {
                        $query->the_post();
                        echo '<div class="post-card" style="border:1px solid #ddd;padding:20px;">';
                        echo '<a href="' . get_permalink() . '"><h3>' . get_the_title() . '</h3></a>';
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium', ['style' => 'max-width:100%;height:auto;']);
                        }
                        echo '</div>';
                    }

                    echo '</div>';
                    wp_reset_postdata();
                } else {
                    echo '<p>Coming soon.</p>';
                }
                echo "</div>";
                echo "<div class='col-sm-3'>";
                    echo "<h3>Categories</h3>";
                    $terms = get_the_terms( get_the_ID(), 'sdm_categories' );
                    $parent_term = get_term( $term->parent, 'sdm_categories' );
                    //if ( $terms && ! is_wp_error( $terms ) ) {
                        // Loop through terms to find the parent
                        $parent_term = get_term( $term->parent, 'sdm_categories' );
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
                    //}
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }
    ?>
</div>

<?php get_footer(); ?>
