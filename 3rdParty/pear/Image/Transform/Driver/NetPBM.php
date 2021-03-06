<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Peter Bowyer <peter@mapledesign.co.uk>                      |
// +----------------------------------------------------------------------+
//
// $Id: NetPBM.php,v 1.1.1.1 2003/07/14 12:57:24 ozan Exp $
//
// Image Transformation interface using command line NetPBM

require_once "Image/Transform.php";

Class Image_Transform_Driver_NetPBM extends Image_Transform
{

    /**
     * associative array commands to be executed
     * @var array
     */
    var $command = array();

    /**
     * Class Constructor
     */
    function Image_Transform_Driver_NetPBM()
    {
        if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
            include_once 'System/Command.php';
            $path = str_replace('pnmscale','',
                         escapeshellcmd(System_Command::which('pnmscale')));
            define('IMAGE_TRANSFORM_LIB_PATH', $path);
        }
        return true;
    } // End function Image_NetPBM

    /**
     * Load image
     *
     * @param string filename
     *
     * @return mixed none or a PEAR error object on error
     * @see PEAR::isError()
     */
    function load($image)
    {
        $this->image = $image;
        $this->_get_image_details($image);
    } // End load

    /**
     * Resizes the image
     *
     * @return none
     * @see PEAR::isError()
     */
    function _resize($new_x, $new_y)
    {
        // there's no technical reason why resize can't be called multiple
        // times...it's just silly to do so

        $this->command[] = IMAGE_TRANSFORM_LIB_PATH .
                           "pnmscale -width $new_x -height $new_y";

        $this->_set_new_x($new_x);
        $this->_set_new_y($new_y);
    } // End resize

    /**
     * Rotates the image
     *
     * @param int $angle The angle to rotate the image through
     */
    function rotate($angle)
    {
        if ('-' == $angle{0}) {
    		$angle = 360 - substr($angle, 1);
    	}
        $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "pnmrotate $angle";
    } // End rotate

    /**
     * Adjust the image gamma
     *
     * @param float $outputgamma
     *
     * @return none
     */
    function gamma($outputgamma = 1.0) {
        $this->command[13] = IMAGE_TRANSFORM_LIB_PATH . "pnmgamma $outputgamma";
    }

    /**
     * adds text to an image
     *
     * @param   array   options     Array contains options
     *             array(
     *                  'text'          // The string to draw
     *                  'x'             // Horizontal position
     *                  'y'             // Vertical Position
     *                  'Color'         // Font color
     *                  'font'          // Font to be used
     *                  'size'          // Size of the fonts in pixel
     *                  'resize_first'  // Tell if the image has to be resized
     *                                  // before drawing the text
     *                   )
     *
     * @return none
     */
    function addText($params)
    {
        $default_params = array('text' => 'This is Text',
                                'x' => 10,
                                'y' => 20,
                                'color' => 'red',
                                'font' => 'Arial.ttf',
								'size' => '12',
								'angle' => 0,
                                'resize_first' => false);
        // we ignore 'resize_first' since the more logical approach would be
        // for the user to just call $this->_resize() _first_ ;)
        extract(array_merge($default_params, $params));
        $this->command[] = "ppmlabel -angle $angle -colour $color -size "
                           ."$size -x $x -y ".$y+$size." -text \"$text\"";
    } // End addText

    function _postProcess($type, $quality)
    {
        $type = is_null($type) ? $this->type : $type;
        array_unshift($this->command, IMAGE_TRANSFORM_LIB_PATH
                      . $type.'topnm '. $this->image);
        $arg = '';
        switch(strtolower($type)){
        	case 'gif':
                $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "ppmquant 256";
                $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "ppmto$type";
                break;
        	case 'jpg':
        	case 'jpeg':
                $arg = "--quality=$quality";
        	default:
                $this->command[] = IMAGE_TRANSFORM_LIB_PATH . "pnmto$type $arg";
                break;
        } // switch
        return implode('|', $this->command);
    } 

    /**
     * Save the image file
     *
     * @param $filename string the name of the file to write to
     * @param string $type (jpeg,png...);
     * @param int $quality 75
     * @return none
     */
    function save($filename, $type=null, $quality = 75)
    {
        $cmd = $this->_postProcess($type, $quality) . ">$filename";
        exec($cmd);
        $this->command = array();
    } // End save

    /**
     * Display image without saving and lose changes
     *
     * @param string $type (jpeg,png...);
     * @param int $quality 75
     * @return none
     */
    function display($type = null, $quality = 75)
    {
        header('Content-type: image/' . $type);
        $cmd = $this->_postProcess($type, $quality);
        passthru($cmd);
        $this->command = array();
    }

    /**
     * Destroy image handle
     *
     * @return none
     */
    function free()
    {
        // there is no image handle here
        return true;
    }


} // End class ImageIM
?>
