<?php

abstract class GO_Zip
{

    # Must be implemented by the subclass
    abstract public function start();

    /**
     * Generate plugin zip file
     * according to plugin configuration
     * 
     * @param array $plugins
     * @return void
     */
    public function generate( $plugins ) {        
        
        # Folder path
        $folder_path = GROWTH_OPTIMIZER_PLUGINS_REPO;

        # If folder repo not exist, create
        if (!file_exists($folder_path))
        mkdir($folder_path, 0777, true);

        # Loop plugins
        foreach ($plugins as $plugin => $settings) {
            
            # Target zip file
            $zip_file = "{$folder_path}/{$plugin}.zip";            
                
            # If file exist no need to generate
            if (file_exists($zip_file)) continue;
            
            # Create a new ZipArchive instance
            $zip = new ZipArchive();

            # Open the zip file for writing
            if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                
                # Plugin folder path
                $plugin_folder_path = ABSPATH . "wp-content/plugins/{$plugin}";

                # Create a recursive directory iterator
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($plugin_folder_path),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                # Loop through the directory and add files to the zip
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        # Get the relative path of the file inside the zip
                        $file_path = $file->getRealPath();
                        $relative_path = substr($file_path, strlen($plugin_folder_path) + 1); # Removing the folder path

                        # Add the file to the zip
                        $zip->addFile($file_path, $relative_path);
                    }
                }

                # Close the zip file
                $zip->close();
            }    

        }
    }
}
