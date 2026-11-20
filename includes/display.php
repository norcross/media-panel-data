<?php
/**
 * Handle the items used on the actual display.
 *
 * @package MediaPanelData
 */

// Declare our namespace.
namespace Norcross\MediaPanelData\Display;

// Set our aliases.
use Norcross\MediaPanelData\Helpers as Helpers;

// And any functions or constants on their own.
use const Norcross\MediaPanelData\VERS;
use const Norcross\MediaPanelData\ASSETS_URL;
use const Norcross\MediaPanelData\HOOK_PREFIX;

/**
 * Start our engines.
 */
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\load_media_view_assets' );
add_action( 'attachment_submitbox_misc_actions', __NAMESPACE__ . '\display_media_view_data', 99 );
add_action( 'print_media_templates', __NAMESPACE__ . '\load_media_backbone_templates' );

/**
 * Enqueue our scripts related to the media panel.
 *
 * @param  string $hook_suffix The current admin page.
 *
 * @return void
 */
function load_media_view_assets( $hook_suffix ) {

	// Set my handle.
	$handle = 'media-panel-data';

	// Set a file suffix structure based on whether or not we want a minified version.
	$file   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? $handle : $handle . '.min';

	// Set a version for whether or not we're debugging.
	$vers   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : VERS;

	// Load our JS file.
	wp_enqueue_script( $handle, esc_url( ASSETS_URL . '/js/' . $file . '.js' ), [ 'jquery' ], $vers, true );

	// Include some localized labeling.
	wp_localize_script( $handle, 'mediaPanelDataArgs',
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
 * @return string
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
			echo sprintf( esc_html__( 'Orientation: %s', 'media-panel-data' ), '<strong>' . esc_html( $media_data['orientation'] ) . '</strong>' );
		echo '</div>';
	}

	// Now show the aspect ratio.
	if ( ! empty( $media_data['aspect-ratio'] ) ) {
		echo '<div class="misc-pub-section misc-pub-aspect-ratio">';
			echo sprintf( esc_html__( 'Aspect Radio: %s', 'media-panel-data' ), '<strong>' . esc_html( $media_data['aspect-ratio'] ) . '</strong>' );
		echo '</div>';
	}

	// Now include a hook so others can add it.
	do_action( HOOK_PREFIX . 'single_edit_advanced_view', $media_data, $media_type, $post );
}

/**
 * Display the data we're surfacing for media on the backbone modal.
 *
 * @return string
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

						// Grab my width and height to use as variables.
						var mediaW  = this.model.attributes.width;
						var mediaH  = this.model.attributes.height;

						// Now add the aspect ratio and orientation label to the display model.
						this.model.attributes.aspect_ratio = nearestNormalAspectRatio( mediaW, mediaH );
						this.model.attributes.orientation  = mediaItemOrientation( mediaW, mediaH );
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

						// Grab my width and height to use as variables.
						var mediaW  = this.model.attributes.width;
						var mediaH  = this.model.attributes.height;

						// Now add the aspect ratio and orientation label to the display model.
						this.model.attributes.aspect_ratio = nearestNormalAspectRatio( mediaW, mediaH );
						this.model.attributes.orientation  = mediaItemOrientation( mediaW, mediaH );
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
