<?php

wpdutil::loadClass('PHP_Debug.Debug.Renderer.Common');

/**
 * A loader class for the renderers.
 *
 * @package PHP_Debug
 * @category PHP
 * @author Loic Vernet <qrf_coil at yahoo dot fr>
 * @since V2.0.0 - 10 Apr 2006
 * 
 * @package PHP_Debug
 * @filesource
 * @version    CVS: $Id:$
 */

class PHP_Debug_Renderer
{

    /**
     * Attempt to return a concrete Debug_Renderer instance.
     *
     * @param string $mode Name of the renderer.
     * @param array $options Parameters for the rendering.
     * @access public
     */
    public static function factory($debugObject, $options)
    {
        $className = 'PHP_Debug_Renderer_'. $options['render_type']. 
            '_'. $options['render_mode'];
        $classPath = 'PHP/Debug/Renderer/'. $options['render_type']. 
            '/'. $options['render_mode']. '.php';


        wpdutil::loadClass('PHP_Debug.Debug.Renderer.'.$options['render_type'].'.'.$options['render_mode']);

        if (class_exists($className)) {
            $obj = new $className($debugObject, $options);
        } else {
 			throw new Exception("Class not loaded");
        }
        return $obj;
    }
}