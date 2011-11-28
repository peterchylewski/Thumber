# Thumber.php

Thumber is a lightweight and easy to use thumbnail image creation tool written in PHP.

- - - - - - - - - -

## Installation & configuration

### thumbs folder
Make sure there is a 'thumbs' directory on your server, and that it has the proper permissions, fully writeable: (chmod 777 thumbs).

### other stuff
(should there be something about "thumbnail is streamed right through the php connection itself (for performance), so your suggestion only makes sense if you switch back to re-directs (see outcommented code in the lower part). maybe we should make this configurable as well, using something like a 'USE_REDIRECT' constant.")


- - - - - - - - - -

## Usage example

In your HTML code, you can ask Thumber to generate an image at the appropriate size and it will return the URL of that image.

	<img src="thumber.php?img=myphoto.jpg&w=540">

	<img src="thumber.php?img=images/myphoto.jpg&w=540">

	<img src="thumber.php?img=../images/myphoto.jpg&w=540">


This will generate a a thumbnail at the appropriate size, and store it in the /thumbs folder. It will return the URL of the thumb to the HTML.


- - - - - - - - - -

## Parameters

Thumber.php will never return a distorted image.  
(If you want an image to be a precise dimension, use the 'crop' parameter.)

Thumber.php will never upsample/upscale/upres an image.  
(Returned images may be smaller than requested if the source images do not have the necessary resolution.)


### Width

	<img src="thumber.php?img=myphoto.jpg&w=540">

Requests a thumbnail with a given width. The height is calculated based on the image aspect ratio.


### Height

	<img src="thumber.php?img=myphoto.jpg&h=400">

Requests a thumbnail with a given height. The width is calculated based on the image aspect ratio.


### Constrained

There are a few ways to request an image which fits in a given space.

Again, thumber.php will not distort the image, so one of these dimensions may be smaller depending on the aspect ratio of the source image relative to the requested aspect ratio.

#### Width & height

	<img src="thumber.php?img=myphoto.jpg&w=360&h=240">

This gives both the with and the height parameters, and the generated thumbnail will not exceed either the given width or height. 

#### Box

	<img src="thumber.php?img=myphoto.jpg&box=360x240">

	<img src="thumber.php?img=myphoto.jpg&box=220">

This is similar to the width + height request, but shorter. The 'box' parameter is followed by with and height numbers joined by an 'x'. 

If only one number is given, the box is assumed to be a square.

#### Crop

	<img src="thumber.php?img=myphoto.jpg&crop=220x140">

	<img src="thumber.php?img=myphoto.jpg&crop=220x140&origin=top-left">

This requests an image with exact dimensions, the original image will be cropped to fit.

By default, Thumber.php will crop from the center of the image. Accepted cropping orgins are:

	origin=center
	origin=top-left
	origin=top-right
	origin=bottom-left
	origin=bottom-right
	origin=left
	origin=right
	origin=top
	origin=bottom

Top, bottom, left, and right origins are from the middle/center of the respective edge.


### Area

	<img src="thumber.php?img=myphoto.jpg&a=307200">

Requests an image with a given area, expressed as the number of pixels. Width and height are calculated to create an image with as close an area as possible to the requested number. In the above example, a 4:3 ratio input image would return a 640x480 pixel image.


- - - - - - - - - -

## Customization

If you would like to prefix a given string of text to the file names of the generated images (for instance, the name of your site), you can use the *custom prefix* function:

	define('CUSTOM_PREFIX', 'YourName-websitedotcom_');

This will return files named as:

	YourName-websitedotcom_myphoto_540x360.jpg

Note that thumber.php also adds the image dimensions to the file name, this is so that the script can generate multiple thumbs of a given image at different sizes and cache them in the thumbs folder.




- - - - - - - - - -

history:

0.5.4 
- much faster image output via fpasstru instead of a redirect

0.5.5 
- parameters 'w' and 'h' - if both set - define a 'box' - the output of distorted images is no longer possible
- substituted an '_' with an 'x' in the thumb filename that makes more sense, e.g. 'cross_red_10x10.png' instead of 'cross_red_10_10.png'
- added alpha channel support for pngs and gifs

0.5.6
- cleaned up the code, improved comments
- force the creation of a new thumbnail if the creation date of the cached one is older than the orginal’s modification date
- better error handling

to to:
- cache purging
- implement / finalize proper error handling
- auto detect presence of an alpha channel in the image

nice to have (maybe)
- 'hot linking' of original files (through CURL or so)

adrien suggestions:
- width&height, crop, and box parameters for constraining size.
- check if the area function is working properly (or maybe i'm just bad a math).