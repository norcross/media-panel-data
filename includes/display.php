<?php
/**
 * Handle the items used on the actual display.
 *
 * @package MediaPanelData
 */

// Declare our namespace.
namespace Norcross\MediaPanelData\Display;

// Set our aliases.
use Norcross\MediaPanelData as Core;
use Norcross\MediaPanelData\Helpers as Helpers;

/**
 * Start our engines.
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\load_media_view_assets' );
add_action( 'attachment_submitbox_misc_actions', __NAMESPACE__ . '\display_media_view_data', 99 );
add_action( 'print_media_templates', __NAMESPACE__ . '\load_media_backbone_templates' );


/**
 * Enqueue media scripts and some CSS.
 *
 * @param  string $hook_suffix The current admin page.
 *
 * @return void
 */
function load_media_view_assets( $hook_suffix ) {

	// And load the file.
	wp_enqueue_script( 'media-panel-data', esc_url( Core\ASSETS_URL . '/js/media-panel-data.js' ), [ 'jquery' ], Core\VERS, true );
	wp_localize_script( 'media-panel-data', 'medaPanelDataArgs',
		[
			'landscape' => __( 'Landscape', 'media-panel-data' ),
			'portrait'  => __( 'Portrait', 'media-panel-data' ),
			'square'    => __( 'Square', 'media-panel-data' ),
		]
	);
}

/**
 * Display the data we're surfacing for media on the non-backbone view.
 *
 * @param  WP_Post $post  The entire WP_Post object.
 *
 * @return HTML
 */
function display_media_view_data( $post ) {

	// Determine the media type and our allowed.
	$media_type = Helpers\parse_attachment_media_type( $post->ID );

	// Don't render these fields unless it is an allowed type.
	if ( ! in_array( $media_type, Helpers\get_allowed_media_types(), true ) ) {
		return [];
	}

	// Get our media data.
	$media_data = Helpers\calculate_media_view_data( $post->ID );

	// Now show the image orientation.
	if ( ! empty( $media_data['orientation'] ) ) {
		echo '<div class="misc-pub-section misc-pub-img-orientation">';
			echo sprintf( __( 'Orientation: %s', 'media-panel-data' ), '<strong>' . esc_html( $media_data['orientation'] ) . '</strong>' ); // phpcs:ignore -- this is escaped just fine.
		echo '</div>';
	}

	// Now show the aspect ratio.
	if ( ! empty( $media_data['aspect-ratio'] ) ) {
		echo '<div class="misc-pub-section misc-pub-aspect-ratio">';
			echo sprintf( __( 'Aspect Radio: %s', 'media-panel-data' ), '<strong>' . esc_html( $media_data['aspect-ratio'] ) . '</strong>' ); // phpcs:ignore -- this is escaped just fine.
		echo '</div>';
	}

	// Now include a hook so others can add it.
	do_action( Core\HOOK_PREFIX . 'media_php_view', $media_data, $post );
}

/**
 * Display the data we're surfacing for media on the media modal.
 *
 * @return HTML
 */
function load_media_backbone_templates() {
	?>
	<!-- Custom template part for single view -->
	<script type="text/html" id="tmpl-media-panel-data-compressed">
		<div class="orientation-aspect-ratio">
			{{ data.orientation }} / {{ data.aspect_ratio }}
		</div>
	</script>

	<!-- Custom template part for double view -->
	<script type="text/html" id="tmpl-media-panel-data-single-lines">
		<div class="orientation">
			<strong><?php esc_html_e( 'Orientation:', 'media-panel-data' ); ?></strong> {{ data.orientation }}
		</div>
		<div class="aspect-ratio">
			<strong><?php esc_html_e( 'Aspect Ratio:', 'media-panel-data' ); ?></strong></strong> {{ data.aspect_ratio }}
		</div>
	</script>

	<!-- Extend the Attachment Details View -->
	<script>
	jQuery(document).ready( function($) {

		// Confirm this part of the chain exists before attempting to extend it.
		if ( wp.media.view.Attachment.Details ) {

			// Extend the details when a user is selecting media in a block.
			wp.media.view.Attachment.Details = wp.media.view.Attachment.Details.extend({

				// Handle adjusting our rendered items.
				render: function() {

					// Ensure that the main attachment fields are rendered.
					wp.media.view.Attachment.prototype.render.apply( this, arguments );

					// This only runs on images and video.
					if ( 'image' === this.model.attributes.type || 'video' === this.model.attributes.type ) {

						// Calculate my aspect ratio.
						var mediaW  = this.model.attributes.width;
						var mediaH  = this.model.attributes.height;

						// Add it to the model.
						this.model.attributes.aspect_ratio = nearestNormalAspectRatio( mediaW, mediaH );

						// Change the orientation back to sqaure if we're almost there.
						if ( '1:1' === this.model.attributes.aspect_ratio ) {
							this.model.attributes.orientation = medaPanelDataArgs.square;
						}

						// If we don't have orientation, go get it.
						if ( ! this.model.attributes.orientation ) {
							this.model.attributes.orientation = mediaItemOrientation( mediaW, mediaH );
						}
					}

					// Detach the views.
					this.views.detach();

					// Append our custom data for images.
					if ( 'image' === this.model.attributes.type ) {
						this.$el.find( 'div.dimensions' ).after(
							wp.media.template( 'media-panel-data-compressed' )( this.model.toJSON() )
						);
					}

					// Append our custom data for video.
					if ( 'video' === this.model.attributes.type ) {
						this.$el.find( 'div.file-length' ).after(
							wp.media.template( 'media-panel-data-compressed' )( this.model.toJSON() )
						);
					}

					// Make sure that our data is fully updated.
					this.model.fetch();

					// And re-render the updated view.
					this.views.render();

					// This is the preferred convention for all render functions.
					return this;
				},

				// Nothing left inside the non-library setup.
			});

			// Finish the check for the primary details part existing.
		}

		// Confirm this part of the chain exists before attempting to extend it.
		if ( wp.media.view.Attachment.Details.TwoColumn ) {

			// Extend the details when a user is viewing media in the library.
			wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend({

				// Handle adjusting our rendered items.
				render: function() {

					// Ensure that the main attachment fields are rendered.
					wp.media.view.Attachment.prototype.render.apply( this, arguments );

					// This only runs on images and video.
					if ( 'image' === this.model.attributes.type || 'video' === this.model.attributes.type ) {

						// Calculate my aspect ratio.
						var mediaW  = this.model.attributes.width;
						var mediaH  = this.model.attributes.height;

						// Add it to the model.
						this.model.attributes.aspect_ratio = nearestNormalAspectRatio( mediaW, mediaH );

						// Change the orientation back to sqaure if we're almost there.
						if ( '1:1' === this.model.attributes.aspect_ratio ) {
							this.model.attributes.orientation = medaPanelDataArgs.square;
						}

						// If we don't have orientation, go get it.
						if ( ! this.model.attributes.orientation ) {
							this.model.attributes.orientation = mediaItemOrientation( mediaW, mediaH );
						}
					}

					// Detach the views.
					this.views.detach();

					// Append our custom data for images.
					if ( 'image' === this.model.attributes.type ) {
						this.$el.find( 'div.dimensions' ).after(
							wp.media.template( 'media-panel-data-single-lines' )( this.model.toJSON() )
						);
					}

					// Append our custom data for video.
					if ( 'video' === this.model.attributes.type ) {
						this.$el.find( 'div.file-length' ).after(
							wp.media.template( 'media-panel-data-single-lines' )( this.model.toJSON() )
						);
					}

					// Make sure that our data is fully updated.
					this.model.fetch();

					// And re-render the updated view.
					this.views.render();

					// This is the preferred convention for all render functions.
					return this;
				},

				// Nothing left inside the library setup.
			});

			// Finish the check for the two-column existing.
		}

		// No remaining backbone changes.
	});
	</script>
	<?php
}
