<?php

abstract class GO_Zip
{
    /**
     * Generate plugin zip file
     * according to plugin configuration
     * 
     * @param array $plugins
     * @return void
     */
    function generate( $plugins ) {
        foreach ($plugins as $plugin => $settings) {

            $target_file = "/{$plugin}.zip";
            $folder_path = GROWTH_OPTIMIZER_PLUGINS_REPO;
            $zipFile     = GROWTH_OPTIMIZER_PLUGINS_REPO . $target_file;

            # If folder repo not exist, create
            if (!file_exists($folder_path))
                mkdir($folder_path, 0777, true);

            # If file exist no need to generate
            if (file_exists($zipFile)) return;

            # Create a new ZipArchive instance
            $zip = new ZipArchive();

            # Open the zip file for writing
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                
                # Create a recursive directory iterator
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folder_path),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                # Loop through the directory and add files to the zip
                foreach ($iterator as $file) {
                    if (!$file->isDir()) {
                        # Get the relative path of the file inside the zip
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($folder_path) + 1); # Removing the folder path

                        # Add the file to the zip
                        $zip->addFile($filePath, $relativePath);
                    }
                }

                # Close the zip file
                $zip->close();
            }    

        }
    }
}
