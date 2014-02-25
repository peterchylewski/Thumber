<?php

date_default_timezone_set('Europe/Berlin');

define('CR', PHP_EOL);
define('TAB', chr(9));

// --------------------------------------------------------------------------
// define paths
// --------------------------------------------------------------------------

define('PATH_TO_THUMBS', isset($_GET['path_to_thumbs']) ? $_GET['path_to_thumbs'] : './_thumbs/');
define('PATH_TO_LOGS', './');

// --------------------------------------------------------------------------
// define whether the thumbnail should be streamed through the open php
// connection (usually faster)
// --------------------------------------------------------------------------

define('USE_STREAM_CONNECTION', true);

// --------------------------------------------------------------------------
// activate error handling
// --------------------------------------------------------------------------

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('error_log', 'thumber_errors.log');
ini_set('log_errors', 1);
function myErrorHandler($errno, $errstr, $errfile, $errline) {
	Thumber::error('line ' . $errline . ': ' . $errstr);
	return true;
}
set_error_handler('myErrorHandler');

// --------------------------------------------------------------------------
// set memory limit if necessary
// --------------------------------------------------------------------------

ini_set('memory_limit', '50M');

// if the above doesn’t work:
// add/ modify the .htaccess file (in the same directory as this file):
// php_value memory_limit 50M

// --------------------------------------------------------------------------
// instantiate the Thumber class
// --------------------------------------------------------------------------

$thumber = new Thumber();

/**
* Thumber
*
* please drop me a note if you like it, have comments/suggestions/wishes,
* found a bug, or just to say hello.
*
* @copyright	Copyright (c) 2008, 2009, 2010, 2011, 2012, 2013, 2014 Peter Chylewski
*               released under the gnu license v3 <http://www.gnu.org/licenses/gpl.html>
* @author	    Peter Chylewski <peter@boring.ch>
* @version	    0.5.7
*
* history:
*
* 0.5.4 
*	- much faster image output via fpasstru instead of a redirect
*
* 0.5.5 
*	- parameters 'w' and 'h' - if both set - define a 'box' - the output of distorted images is no longer possible
*   - substituted an '_' with an 'x' in the thumb filename that makes more sense,
*     e.g. 'cross_red_10x10.png' instead of 'cross_red_10_10.png'
*   - added alpha channel support for pngs and gifs
*
* 0.5.6
* - cleaned up the code, improved comments
* - force the creation of a new thumbnail if the creation date of the cached one is older
*   than the orginal’s modification date
* - better error handling
*
* 0.5.7
* - better log function
* - USE_STREAM_CONNECTION option
* - new 'sq' parameter to produce square thumbnails
* - new optional 'sharpen' parameter allows to switch off sharpening for individual thumbnails (default is 'true')
*
* to do:
* - cache purging
* - implement / finalize proper error handling
* - auto detect presence of an alpha channel in the image
*
* nice to have (maybe)
*
* - 'hot linking' of original files (through CURL or so)
*
*/

class Thumber {

protected $startTime;
protected $pathToImage, $pathToThumb;
protected $imageType;

protected $imageWidth, $imageHeight;
protected $thumbArea;
protected $thumbWidth, $thumbHeight;
protected $square;

protected $sharpen;

public function __construct() {
	$this->startTime = microtime(true);
	$this->_logic();
}

protected function _logic() {
	
	// --------------------------------------------------------------------------
	// what this program is supposed to do
	// --------------------------------------------------------------------------
		
	$this->pathToImage = isset($_GET['img']) ? $_GET['img'] : '';
	if (!file_exists($this->pathToImage)) {
		self::error('input image not found  at "'. $this->pathToImage . '"');
	}
	
	if (is_dir(PATH_TO_THUMBS) !== true) { mkdir(PATH_TO_THUMBS, 0777); }
	
	self::log($this->pathToImage);
		
	$this->thumbArea   = isset($_GET['a'])  ? $_GET['a']  : null;
	$this->thumbWidth  = isset($_GET['w'])  ? $_GET['w']  : null;
	$this->thumbHeight = isset($_GET['h'])  ? $_GET['h']  : null;
	$this->square      = isset($_GET['sq']) ? $_GET['sq'] : null;
	
	$this->sharpen     = isset($_GET['sharpen']) ? $_GET['sharpen'] : 2;

	$this->_gatherInfo();
	$this->_calculateThumbDimensions();
	$this->_serveThumb();
	
}

protected function _gatherInfo() {
		
	// --------------------------------------------------------------------------
	// determine the file type and the dimensions of the original image
	// --------------------------------------------------------------------------
	
	// right now, only 'gif', 'jpg' and 'png' files work as input,
	// but future versions of the GD library might understand more formats
	
	$types = array (
	        1 =>  'gif',
	        2 =>  'jpg',
	        3 =>  'png',
	        4 =>  'swf',
	        5 =>  'psd',
	        6 =>  'bmp',
	        7 =>  'tiff(intel byte order)',
	        8 =>  'tiff(motorola byte order)',
	        9 =>  'jpc',
	        10 => 'jp2',
	        11 => 'jpx',
	        12 => 'jb2',
	        13 => 'swc',
	        14 => 'iff',
	        15 => 'wbmp',
	        16 => 'xbm'
	);
	
	$info = getimagesize($this->pathToImage);
	$this->imageWidth  = $info[0];
	$this->imageHeight = $info[1];
	$this->imageType   = $types[$info[2]];

}

protected function _calculateThumbDimensions() {
	
	if (isset($this->square)) {
		
		$this->thumbWidth = $this->square;
		$this->thumbHeight = $this->square;
		
	} else if (isset($this->thumbArea)) {
		
		// --------------------------------------------------------------------------
		// if the 'a' (for area) parameter has been set, calculate the thumb 
		// dimensions so that their product will approximate the required area 
		// (given in square pixels)
		// --------------------------------------------------------------------------
		
		$imageArea = $this->imageWidth * $this->imageHeight;
		$sizeRatio = $this->thumbArea / $imageArea;
		
		$this->thumbWidth  = ceil($this->thumbArea / $this->imageHeight);
		$this->thumbHeight = ceil($this->thumbArea / $this->imageWidth);
	
	} else if (isset($this->thumbWidth) && isset($this->thumbHeight)) {

		// --------------------------------------------------------------------------
		// if both the width and the height have been given, calculate a bounding box
		// --------------------------------------------------------------------------
	
		if ($this->imageWidth < $this->imageHeight) {
			$sizeRatio = $this->imageHeight / $this->thumbHeight;
		} else {
			$sizeRatio = $this->imageWidth / $this->thumbWidth;
		}
		$this->thumbWidth = ceil($this->imageWidth / $sizeRatio);
		$this->thumbHeight = ceil($this->imageHeight / $sizeRatio);
		
	} else {
	
		// --------------------------------------------------------------------------
		// if the width has not been given, calculate it from the height
		// if the height has not been given, calculate it from the width
		// --------------------------------------------------------------------------
		
		if (!isset($this->thumbWidth)) {
			$sizeRatio = $this->imageHeight / $this->thumbHeight;
			$this->thumbWidth = ceil($this->imageWidth / $sizeRatio);
		} else if (!isset($this->thumbHeight)) {
			$sizeRatio = $this->imageWidth / $this->thumbWidth;
			$this->thumbHeight = ceil($this->imageHeight / $sizeRatio);
		}
		
	}
		
	// --------------------------------------------------------------------------
	// make sure the thumbnail isn’t bigger than the original image (debatable)
	// --------------------------------------------------------------------------
	
	if ($this->thumbWidth > $this->imageWidth || $this->thumbHeight > $this->imageHeight) {
		$this->thumbWidth = $this->imageWidth;
		$this->thumbHeight = $this->imageHeight;
	}
		
	// --------------------------------------------------------------------------
	// now that we know the definitive dimensions of our thumbnail (as integers),
	// why not use those to label the file properly?
	// --------------------------------------------------------------------------
		
	$pathParts = pathinfo($this->pathToImage);
	
	$this->pathToThumb = PATH_TO_THUMBS 
					   . $pathParts['filename'] 
					   . '_' . $this->thumbWidth 
					   . 'x' . $this->thumbHeight 
					   . '.' . $pathParts['extension'];
					
}

protected function _serveThumb() {
	
	// --------------------------------------------------------------------------
	// if the thumbnail image already exists, serve it; 
	// otherwise generate one
	// --------------------------------------------------------------------------
	
	#$this->_generateThumb(); return; // force the generation of a new thumbnail (for testing)
	
	if (file_exists($this->pathToThumb)) {
				
		// force the creation of a new thumbnail if the modification date of the cached one is older than the orginal’s	
		if (filemtime($this->pathToImage) > filemtime($this->pathToThumb)) {
			$this->_generateThumb(); return;
		}
		
		if (USE_STREAM_CONNECTION === true) {
			
			//self::log('streaming...');
			
			// new, much faster

			// open the file in binary mode
			$fp = fopen($this->pathToThumb, 'rb');

			// send the right headers
			header('Content-Type: image/' . $this->imageType == 'jpg' ? 'jpeg' : $this->imageType);
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($this->pathToThumb));
			header('Cache-Control: ');          	// leave blank to avoid IE errors
			header('Pragma: ');                 	// leave blank to avoid IE errors
			header('Server: NoneOfYourBusiness');   // hide environment information if possible
			header('X-Powered-By: Thumber');    	// hide environment information if possible
			header('Content-Disposition: inline; filename="'. urlencode(basename($this->pathToThumb)) . '"');

			// stream it through
			fpassthru($fp);
			fclose($fp);
		
		} else {
			
			// old, slow (maybe not so slow after all...)
			
			$uri = 'http://' . $_SERVER['SERVER_NAME'] . rtrim(dirname($_SERVER['PHP_SELF']), '/') . ltrim($this->pathToThumb, '.');
			header('Location: ' .  $uri);
		}
				
	} else {
		if (file_exists($this->pathToImage)) {
			$this->_generateThumb();
		}
	}
	
	exit;
}

protected function _generateThumb() {
	
	// --------------------------------------------------------------------------
	// create an image from the input image file
	// --------------------------------------------------------------------------
		
	switch($this->imageType) {
		case 'jpg':
			$image = @imagecreatefromjpeg($this->pathToImage);
		break;
		case 'gif':
			$image = @imagecreatefromgif($this->pathToImage);
		break;
		case 'png':
			$image = @imagecreatefrompng($this->pathToImage);
		break;
	}
		
	if ($image === false) {
		self::log('    ' . $this->pathToImage . ': ERROR: image could not be created');
		exit;
	}
		
	// create empty thumbnail image
	$thumbImage = @ImageCreateTrueColor($this->thumbWidth, $this->thumbHeight);
	
	// preserve alpha channel
	if ($this->imageType == 'png' || $this->imageType == 'gif') {
		imagealphablending($thumbImage, false);
	}
		
	$srcX = 0;
	$srcY = 0;
	
	$destX = 0;
	$destY = 0;
	
	$srcW = $this->imageWidth;
	$srcH = $this->imageHeight;
	
	$destW = $this->thumbWidth;
	$destH = $this->thumbHeight;
	
	if (isset($this->square)) {		
		// enlarge thumb contents slightly to cut off possible frame borders
		$destW = $this->square * 1.1;
		$destH = $this->square * 1.1;
		$ratio = $this->imageHeight / $this->imageWidth;		
		if ($srcW > $srcH) {
			$destW /= $ratio;
		} else {
			$destH *= $ratio;
		}
		// center pixels
		$destX = floor(($destW - $this->square) / -2);
		$destY = floor(($destH - $this->square) / -2);			
	}
	
	// paste the original into the thumb in its new dimensions
	ImageCopyResampled($thumbImage, $image, $destX, $destY, $srcX, $srcY, $destW, $destH, $srcW, $srcH);
	 	
	switch ($this->imageType) {
		case 'png':
		case 'gif':
			
			ImageSaveAlpha($thumbImage, true);

			// we don’t sharpen thumbs that might contain alpha channels, because it produces nasty borders
			// - to do: detect alpha channel in the original image
			
		break;	
		default:
				
			// --------------------------------------------------------------------------
			// sharpen it a little
			// --------------------------------------------------------------------------
			
			if ($this->sharpen > 0) {
				// these are just arbitrary numbers, chosen for simplicity's sake
				// feel free to experiment!
				$centerValues = array(1 => 25, 2 => 17, 3 => 12);
				if (function_exists('imageconvolution')) {
					$sharpen = array(array( -1, -1, -1 ),
						             array( -1, $centerValues[$this->sharpen], -1 ),
						             array( -1, -1, -1 )
					);
					$divisor = array_sum(array_map('array_sum', $sharpen));
					imageconvolution($thumbImage, $sharpen, $divisor, 0);
				}
			}

		break;
	}


	// --------------------------------------------------------------------------
	// spit it out
	// --------------------------------------------------------------------------
		
	switch($this->imageType) {
		case 'jpg':
			// save it first
			imagejpeg($thumbImage, $this->pathToThumb, 80);
			header('Content-type: image/jpeg'); 
			imagejpeg($thumbImage, NULL, 80);	
		break;
		case 'gif':
			// save it first
			imagegif($thumbImage, $this->pathToThumb);
			header('Content-type: image/gif'); 
			imagegif($thumbImage, NULL);
		break;
		case 'png':
			// save it first
			imagepng($thumbImage, $this->pathToThumb);
			header('Content-type: image/png');
			imagepng($thumbImage, NULL);
		break;
	}
	
	imagedestroy($image);
	imagedestroy($thumbImage);
	
	exit;
		
}

public static function error($msg) {
	#ob_end_clean();
	self::log('ERROR: ' . $msg);
	exit;
}

public static function log($text) {
	$pathToLog = PATH_TO_LOGS . 'thumber.log';
	@chmod(PATH_TO_LOGS, 0777);
	file_put_contents($pathToLog, @date('Y-m-d\TH:i:s') . TAB . $text . CR,  FILE_APPEND | LOCK_EX);
}

} // class Thumber

