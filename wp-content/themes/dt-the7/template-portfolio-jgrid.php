<?php
/* Template Name: Portfolio - justified grid */

/**
 * Portfolio justified grid layout.
 *
 * @package presscore
 * @since presscore 2.2
 */

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $post;
$config = Presscore_Config::get_instance();
$config->set('template', 'portfolio');
$config->set('justified_grid', true);
$config->base_init();

// override some settings
$config->set('description', 'on_hoover');
$config->set('all_the_same_width', true);
$config->set('columns', -1);
$config->set( 'layout', 'grid' );

add_action('presscore_before_main_container', 'presscore_page_content_controller', 15);

get_header(); ?>

		<?php if ( presscore_is_content_visible() ): ?>

			<!-- Content -->
			<div id="content" class="content" role="main">

				<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); // main loop ?>

					<?php
					do_action( 'presscore_before_loop' );

					$full_width = $config->get('full_width');
					$item_padding = $config->get('item_padding');
					$target_height = $config->get('target_height');

					$ppp = $config->get('posts_per_page');
					$order = $config->get('order');
					$orderby = $config->get('orderby');
					$display = $config->get('display');
					$request_display = $config->get('request_display');
					// $layout = $config->get('layout');

					$all_terms = get_categories( array(
						'type'          => 'dt_portfolio',
						'hide_empty'    => 1,
						'hierarchical'  => 0,
						'taxonomy'      => 'dt_portfolio_category',
						'pad_counts'    => false
					) );

					$all_terms_array = array();
					foreach ( $all_terms as $term ) {
						$all_terms_array[] = $term->term_id;
					}

					$page_args = array(
						'post_type'	=> 'dt_portfolio',
						'status'	=> 'publish' ,
						'paged'		=> dt_get_paged_var(),
						'order'		=> $order,
						'orderby'	=> 'name' == $orderby ? 'title' : $orderby,
					);

					if ( $ppp ) {
						$page_args['posts_per_page'] = intval($ppp);
					}

					if ( 'all' != $display['select'] && !empty($display['terms_ids']) ) {

						$page_args['tax_query'] = array( array(
							'taxonomy'	=> 'dt_portfolio_category',
							'field'		=> 'id',
							'terms'		=> array_values($display['terms_ids']),
							'operator'	=> 'IN',
						) );

						if ( 'except' == $display['select'] ) {
							$terms_arr = array_diff( $all_terms_array, $display['terms_ids'] );
							sort( $terms_arr );

							if ( $terms_arr ) {
								$page_args['tax_query']['relation'] = 'OR';
								$page_args['tax_query'][1] = $page_args['tax_query'][0];
								$page_args['tax_query'][0]['terms'] = $terms_arr;
								$page_args['tax_query'][1]['operator'] = 'NOT IN';
							}

							add_filter( 'posts_clauses', 'dt_core_join_left_filter' );
						}

					}

					// filter
					if ( $request_display ) {

						// except
						if ( 0 == current($request_display['terms_ids']) ) {
							// ninjaaaa
							$request_display['terms_ids'] = $all_terms_array;
						}

						$page_args['tax_query'] = array( array(
							'taxonomy'	=> 'dt_portfolio_category',
							'field'		=> 'id',
							'terms'		=> array_values($request_display['terms_ids']),
							'operator'	=> 'IN',
						) );

						if ( 'except' == $request_display['select'] ) {
							$page_args['tax_query'][0]['operator'] = 'NOT IN';
						}
					}

					$page_query = new WP_Query($page_args);
					remove_filter( 'posts_clauses', 'dt_core_join_left_filter' );

					?>

					<?php
					// categorizer
					$filter_args = array();

					if ( !$config->get('show_ordering') ) {
						remove_filter( 'presscore_get_category_list', 'presscore_add_sorting_for_category_list', 15 );
					}

					if ( $config->get('show_filter') ) {

						$select = $display['select'];
						$terms_ids = isset($display['terms_ids']) ? $display['terms_ids'] : array();

						// categorizer args
						$filter_args = array(
							'taxonomy'	=> 'dt_portfolio_category',
							'post_type'	=> 'dt_portfolio',
							'select'	=> $select,
							'terms'		=> $terms_ids,
						);
					}

					// display categorizer
					presscore_get_category_list( array(
						// function located in /in/extensions/core-functions.php
						'data'	=> dt_prepare_categorizer_data( $filter_args ),
						'class'	=> 'filter without-isotope',
					) );
					?>

					<?php
					// masonry layout classes
					$masonry_container_classes = array( 'wf-container', 'portfolio-grid', 'jg-container' );
					$masonry_container_classes = implode(' ', $masonry_container_classes);

					$masonry_container_data_attr = array(
						'data-padding="' . intval($item_padding) . 'px"',
						'data-target-height="' . intval($target_height) . 'px"'
					);

					if ( $config->get('hide_last_row') ) {
						$masonry_container_data_attr[] = 'data-part-row="false"';
					}

					// ninjaaaa!
					$masonry_container_data_attr = ' ' . implode(' ', $masonry_container_data_attr);
					?>

					<?php if ( $full_width ) : ?>

				<div class="full-width-wrap">

					<?php endif; ?>

					<div class="<?php echo esc_attr($masonry_container_classes); ?>"<?php echo $masonry_container_data_attr; ?>>

					<?php if ( $page_query->have_posts() ): while( $page_query->have_posts() ): $page_query->the_post(); ?>

						<?php dt_get_template_part('portfolio-masonry-content'); ?>

					<?php endwhile; wp_reset_postdata(); endif; ?>

					</div>

					<?php if ( $full_width ) : ?>

				</div>

					<?php endif; ?>

					<?php dt_paginator($page_query); ?>

					<?php do_action( 'presscore_after_loop' ); ?>

					<?php endwhile; ?>

				<?php endif; ?>

			</div><!-- #content -->

			<?php do_action('presscore_after_content'); ?>

		<?php endif; // if content visible ?>

<?php get_footer(); ?>