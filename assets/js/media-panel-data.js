
/**
 * Determine the nearest "normal" aspect ratio.
 *
 * Cribbed from here: https://gist.github.com/jonathantneal/d3a259ebeb46de7ab0de
 *
 * @param  integer width   The media width.
 * @param  integer height  The media height.
 *
 * @return string
 */
function nearestNormalAspectRatio( width, height ) {

	// Begin setting all the variables we use.
	var
	ratio = (width * 100) / (height * 100),
	maxW = 3 in arguments ? arguments[2] : 16,
	maxH = 4 in arguments ? arguments[3] : 16,
	ratiosW = new Array(maxW).join(',').split(','),
	ratiosH = new Array(maxH).join(',').split(','),
	ratiosT = {},
	ratios = {},
	match,
	key;

	// Start looping through and finding one we like.
	ratiosW.forEach( function ( empty, ratioW ) {
		++ratioW;

		ratiosH.forEach( function ( empty, ratioH ) {
			++ratioH;

			ratioX = ( ratioW * 100 ) / ( ratioH * 100 );

			if ( ! ratiosT[ ratioX ] ) {
				ratiosT[ ratioX ] = true;

				ratios[ ratioW + ':' + ratioH ] = ratioX;
			}
		});
	});

	// Find the matching ratio to return.
	for ( key in ratios ) {
		if ( ! match || (
			Math.abs( ratio - ratios[ key ] ) < Math.abs( ratio - ratios[ match ] )
		)) {
			match = key;
		}
	}

	// Return the resulting match.
	return match;
}

/**
 * Determine the orientation of the item.
 *
 * @param  integer width   The media width.
 * @param  integer height  The media height.
 *
 * @return string
 */
function mediaItemOrientation( width, height ) {

	// If the height and width are equal, just send back square.
	if ( width === height ) {
		return medaPanelDataArgs.square;
	}

	// Calculate the raw aspect ratio.
	var
	rawAspectRatio = width / height,
	itemOrientation;

	// Do some if/then to determine the related string.
	if ( rawAspectRatio > 1 ) { // Media is landscape.
		itemOrientation = medaPanelDataArgs.landscape;
	} else if ( rawAspectRatio < 1 ) { // Media is portrait.
		itemOrientation = medaPanelDataArgs.portrait;
	} else { // Media is square.
		itemOrientation = medaPanelDataArgs.square;
	}

	// Return what we have.
	return itemOrientation;
}
