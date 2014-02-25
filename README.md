#Thumber

*Thumber* is a small and simple to use PHP 5 class that automatically generates thumbnails of any size from PNG, JPEG, and GIF images.


##How to install and use Thumber

- Download the latest version [https://github.com/peterchylewski/Thumber/tree/v-0.5.7 here].

- Upload it to your web server (PHP 5, GD library required).

- Create a directory for the thumbnails cache (see below).

##Usage

To generate a thumbnail image, simply type a URL like this into your browser:

`http://yourserver.org/thumber.php?img=images/abc.png&h=200`

Thumber has four parameters:

- **img**: the path to the original image
- **w**: the desired width of the thumbnail image
- **h**: the desired height of the thumbnail image
- **a**: the desired area of the thumbnail image
- **sq**: the desired side length of a square thumbnail image*
 
*image will be centered

You can either specify the width (w), the height (h), or both. If you omit one value, it will automatically be calculated for you.

Alternatetively, you can specify the desired area of the thumbnail: use e.g. 'a= 100000' to generate a thumbnail that covers an area of 100000 pixels (please note that the effective pixel amount of the created thumbnail is an approximation, depending on the original’s dimensions); this makes sense if you have a lot of images with different aspect ratios - it 'evens out' their visual impacts.


##Caching

Thumber stores each freshly created thumbnail image file in a directory - the default path to it being ./_thumbs/', but you can easily change it within the code:

`define('PATH_TO_THUMBS', './thumbs/');`

Quite obviously, this directory needs to be writeable.

The next time you call (or anybody else calls) thumber.php, it will automagically serve the cached thumbnail, saving precious computing time.

The thumbnail images are aptly named: The calculated dimensions are added to the original’s file name, e. g. if the original is named 'house.jpg', the thumbnail image will be e.g. named 'house_255x100.jpg', reflecting its size in pixels.

*Note:* ~~Once you've replaced an original image with another one of the same name, you'll have to manually delete the cached thumbnail as well, otherwise you'll be stuck with an old, wrong thumbnail - future releases might add this functionality from within PHP. ~~  (see: 'New in version 0.5.6')

###New in version 0.5.4

- cleaned up code
- much faster caching (no more bad re-directing, but swift streaming through the connection)
- 'area mode' added
- added slight sharpening (thumbnails now appear about as 'sharp' as the input image)

###New in version 0.5.6

- forces the creation of a new thumbnail if the creation date of the cached one is older than the orginal’s modification date (sort of experimental - please report errors or simply comment it out)
- added alpha channel support for PNGs and GIFs (no sharpening there because it creates ugly borders)
- parameters 'w' and 'h' - if both set - define a 'box' into which the thumbnail fits; the output of distorted images is no longer possible
- substituted an underscore with an 'x' in the thumbnail filename for better readability, e.g. 'example_160x257.png' instead of 'example_160_257.png'
- better error handling
- cleaned up the code, improved comments

###New in version 0.5.7

- better log function
- USE_STREAM_CONNECTION option
- new 'sq' parameter to produce square thumbnails
- new optional 'sharpen' parameter allows to switch off sharpening for individual thumbnails (default is 'true')


###To do

- path to thumbnail cache directory selectable through  GET parameter
- cache directory purging through GET parameter
- define JPEG output quality parameter through GET parameter
- implement / finalize proper error handling
- cropping (?)

##Thumber in the wild

http://www.marietaillefer.fr/

http://www.myriamtirler.com/

Have fun!

Peter Chylewski

----

#Version History

##0.5.4 
- much faster caching (no more bad re-directing, but swift streaming via fpasstru)
- added slight sharpening (thumbnails now appear about as 'sharp' as the input image)
- 'area mode' added
- cleaned up code

##0.5.5 
- parameters 'w' and 'h' - if both set - define a 'box' - the output of distorted images is no longer possible
- substituted an '_' with an 'x' in the thumb filename that makes more sense, e.g. 'cross_red_10x10.png' instead of 'cross_red_10_10.png'
- added alpha channel support for pngs and gifs (no sharpening there because it creates ugly borders)

##0.5.6
- force the creation of a new thumbnail if the creation date of the cached one is older than the orginal’s modification date
- better error handling
- cleaned up the code, improved comments

##0.5.7
- better log function
- USE_STREAM_CONNECTION option
- new 'sq' parameter to produce square thumbnails
- new optional 'sharpen' parameter allows to switch off sharpening for individual thumbnails (default is 'true')

## To do:
- cache purging
- implement / finalize proper error handling
- auto detect presence of an alpha channel in the image
- Nice to have (maybe)
	- 'hot linking' of original files (through CURL or so)