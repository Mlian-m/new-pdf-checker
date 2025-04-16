<?php
/**
 * Created by PhpStorm.
 * User: Ahmed Ammar
 * Date: 9/2/2019
 * Time: 6:10 AM
 */
ini_set("display_errors", "on");
error_reporting(E_ALL);
define('APP_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

if (isset($_POST['url'])) {
    $pdf_file = APP_PATH . "uploads" . DIRECTORY_SEPARATOR . "my.pdf";
    
    // Only download the file to get its size
    $error = curl_file($pdf_file, $_POST['url']);
    // print_r($error); // Optional server-side debug

    $file_size = 0;
    if (empty($error) && file_exists($pdf_file)) {
        $file_size = filesize($pdf_file);
    }

    echo json_encode(array(
        // num_pages is no longer determined here
        'file_size' => formatBytes($file_size),
        'error' => $error
    ));
}

function formatBytes($bytes, $precision = 2)
{
    // Keep KB precision at 0 if bytes > 0, otherwise default precision
    $effective_precision = ($bytes > 0 && floor(($bytes ? log($bytes) : 0) / log(1024)) == 1) ? 0 : $precision;
    
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $units_fr = array('B', 'Ko', 'Mo', 'Go', 'To'); // Corrected French units

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    $unit = $units[$pow];
    $fr_unit = $units_fr[$pow];

    // No longer forcing precision based on unit here, done above
    // if ($unit == "KB") {
    //     $precision = 0;
    // }
    
    $num = number_format($bytes, $effective_precision);
    $fr_num = str_replace(".",",",$num);
    
    return array(
        'en' => $num . "&nbsp;" . $unit,
        'fr' => $fr_num . "&nbsp;" . $fr_unit
    );
}

function curl_file($output_file, $url)
{
    set_time_limit(0);
    // File to save the contents to
    $fp = fopen($output_file, 'w+');
    if (!$fp) {
        return "Failed to open output file for writing.";
    }

    // Here is the file we are downloading, replace spaces with %20
    $ch = curl_init(str_replace(" ", "%20", $url));
    if (!$ch) {
        fclose($fp);
        return "Failed to initialize cURL session.";
    }

    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout slightly
    // give curl the file pointer so that it can write to it
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // It's better practice to use a proper CA bundle if possible, 
    // but keep verify peer false if required by the hosting environment.
    // curl_setopt($ch, CURLOPT_CAPATH, APP_PATH."scripts".DIRECTORY_SEPARATOR."cacert.pem"); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Also often needed when VERIFYPEER is false
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36"); // Updated User-Agent

    $data = curl_exec($ch); // Get curl response
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    fclose($fp); // Close the file handle

    if ($data === false) {
        // Append HTTP code to error if download failed
        return "cURL Error ({$http_code}): " . $error;
    }
    if ($http_code >= 400) {
        // Handle HTTP errors (like 404 Not Found, 403 Forbidden)
        return "HTTP Error: Status Code " . $http_code;
    }

    return ""; // Return empty string on success
}