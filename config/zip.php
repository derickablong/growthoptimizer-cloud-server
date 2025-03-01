<?php

# Plugins repository
define('REPO_FOLDER', '/required-plugins');
define('GROWTH_OPTIMIZER_PLUGINS_REPO', ABSPATH . REPO_FOLDER);

/**
 * Plugin zip generator
 * 
 * @param string $plugin
 * @return string
 */
function go_generate_plugin_zip( $plugin ) {

    $target_file     = "/{$plugin}.zip";
    $folderPath      = GROWTH_OPTIMIZER_PLUGINS_REPO;
    $zipFile         = GROWTH_OPTIMIZER_PLUGINS_REPO . $target_file;
    $plugin_file_url = home_url(REPO_FOLDER.$target_file);

    # If file exist no need to generate
    if (file_exists($zipFile)) return $plugin_file_url;

    # Create a new ZipArchive instance
    $zip = new ZipArchive();

    # Open the zip file for writing
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        
        # Create a recursive directory iterator
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        # Loop through the directory and add files to the zip
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                # Get the relative path of the file inside the zip
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folderPath) + 1); # Removing the folder path

                # Add the file to the zip
                $zip->addFile($filePath, $relativePath);
            }
        }

        # Close the zip file
        $zip->close();

        # Return plugin zip file url
        return $plugin_file_url;
    }
    return '';
}
