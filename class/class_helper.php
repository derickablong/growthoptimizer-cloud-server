<?php

namespace GO_Cloud;

trait Go_Helper
{    

    /**
     * Load template
     * 
     * @param string $file
     * @param array $variables
     * @return void
     */
    public function template($file, $variables = array())
    {
        if (!empty($variables) && is_array($variables)) {
            extract($variables);
        }
        include $this->cloud_dir . "{$file}.php";
    }

    
    /**
     * CSS file url
     * 
     * @param string $file
     * @return string
     */
    public function css($file)
    {
        return $this->cloud_url . "{$file}.css";        
    }


    /**
     * JS file url
     * 
     * @param string $file
     * @return string
     */
    public function script($file)
    {
        return $this->cloud_url . "{$file}.js";        
    }

}