# Media Panel Data
A small plugin to add additional information to the media panel view, giving users context when selecting an image or video to display.

* Calculates aspect ratio and orientation
* Extends the core backbone templates to include the data


## Functionality
Just activate it. That's it. The data will begin showing for images and videos in the appropriate views. 


### FAQ
#### Can I Add More Media Types?
I guess, but not sure what else besides those two would have an aspect ratio and orientation to display. Like, PDFs don't have that. But you can using the `media_panel_data_allowed_media_types` filter.

#### Can I Add More Data?
I've added a few hooks and filters to do so, but I would suggest reviewing the code and seeing how the JS and PHP interact. It may be easier to just roll your own solution.
