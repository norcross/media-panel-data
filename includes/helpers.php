<?php
/**
 * Bundle in some helpers.
 *
 * @package MediaPanelData
 */

// Declare our namespace.
namespace Norcross\MediaPanelData\Helpers;

// Set our aliases.
use Norcross\MediaPanelData as Core;

/**
 * Set the default allowed types, with a filter.
 *
 * @return array
 */
function get_allowed_media_types() {
	return apply_filters( Core\HOOK_PREFIX . 'allowed_media_types', [ 'image', 'video' ] );
}

/**
 * Determine what type of media an item is.
 *
 * @param  integer $media_id   The ID of the media.
 * @param  boolean $full_mime  Whether we want the entire array.
 *
 * @return string / array
 */
function parse_attachment_media_type( $media_id = 0, $full_mime = false ) {

	// Attempt to get our mime type.
	$check_mime = get_post_mime_type( absint( $media_id ) );

	// Anything without one will fail.
	if ( empty( $check_mime ) ) {
		return false;
	}

	// Now split it apart.
	$type_array = explode( '/', $check_mime );

	// If we only want the main part, return that.
	if ( false === $full_mime ) {
		return $type_array[0];
	}

	// Add the full one back on the end.
	$type_array[2] = $check_mime;

	// Return our first part or all of it.
	return $type_array;
}

/**
 * Handle some extra media calculations.
 *
 * @param  integer $media_id  The media ID in question.
 *
 * @return array
 */
function calculate_media_view_data( $media_id = 0 ) {

	// Bail without the proper ID.
	if ( empty( $media_id ) || 'attachment' !== get_post_type( $media_id ) ) {
		return [];
	}

	// Get my media data.
	$get_media_data = wp_get_attachment_metadata( $media_id );

	// Bail without a height or width.
	if ( empty( $get_media_data['height'] ) || empty( $get_media_data['width'] ) ) {
		return [];
	}

	// Set each variable as expected.
	$media_width	= absint( $get_media_data['width'] );
	$media_height   = absint( $get_media_data['height'] );

	// Fetch the aspect ratio.
	$media_asprat   = format_aspect_ratio( $media_width, $media_height );

	// Define the orientation. Kinda basic.
	$orientation    = ( '1:1' !== $media_asprat ? ( $media_width > $media_height ? __( 'Landscape', 'media-panel-data' ) : __( 'Portrait', 'media-panel-data' ) ) : __( 'Square', 'media-panel-data' ) );

	// define the resulting data.
	$set_media_args = [
		'orientation'  => $orientation,
		'aspect-ratio' => $media_asprat,
	];

	// Now send back the data with a filter.
	return apply_filters( Core\HOOK_PREFIX . 'media_view_data', $set_media_args, $media_id, $get_media_data );
}

/**
 * Calculate and format a nice aspect ratio.
 *
 * Cribbed from here: https://stackoverflow.com/a/60710418
 *
 * @param  integer $width   The media width.
 * @param  integer $height  The media height.
 *
 * @return string
 */
function format_aspect_ratio( $width = 0, $height = 0 ) {

	// First set some empty variables.
	$ratios_set = [];
	$ratio_args = [];

	// Set my fuller ratio.
	$set_ratio  = ( $width * 100 ) / ( $height * 100 );

	// Start doing the base 16 check on width.
	for ( $w = 1; $w < 17; $w++ ) {

		// Start doing the base 16 check on height.
		for ( $h = 1; $h < 17; $h++ ) {

			// Determine the X value.
			$ratioX = strval( ( $w * 100 ) / ( $h * 100 ) );

			// Add the ratio set to the array.
			if ( ! array_key_exists( $ratioX, $ratios_set ) ) {
				$ratios_set[ $ratioX ] = true;
				$ratio_args[ $w . ':' . $h ] = $ratioX;
			}
		}
	}

	// Now set an empty.
	$set_match  = null;

	// Start looping the ratios we have.
	foreach ( $ratio_args as $key => $value ) {

		// If we don't have a match, and it matches, set it.
		if ( ! $set_match || abs( $set_ratio - $value ) < abs( $set_ratio - $ratio_args[ $set_match ] ) ) {
			$set_match  = $key;
		}
	}

	// Return the match.
	return $set_match;
}
