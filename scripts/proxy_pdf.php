<?php
ini_set("display_errors", "0"); // Turn off errors for direct output
error_reporting(0);

if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    echo "Missing 'url' parameter.";
    exit;
}

$original_url = $_GET['url'];

// Basic validation for URL scheme
if (!preg_match('/^https?:\/\//i', $original_url)) {
    http_response_code(400);
    echo "Invalid URL scheme.";
    exit;
}

// --- cURL Setup --- 
set_time_limit(0); 
$ch = curl_init();

if (!$ch) {
    http_response_code(500);
    echo "Failed to initialize cURL session.";
    exit;
}

curl_setopt($ch, CURLOPT_URL, $original_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return content as string initially to check for errors
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 90); // Longer timeout for potentially large PDFs
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Keep as needed for hosting
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36");

// Execute cURL
$pdf_content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

curl_close($ch);

// --- Error Handling --- 
if ($pdf_content === false || $http_code >= 400) {
    http_response_code($http_code >= 400 ? $http_code : 500);
    echo "Failed to fetch PDF from original source. HTTP Status: {$http_code}. Error: {$curl_error}";
    exit;
}

// --- Output PDF --- 
header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdf_content));
header('Content-Disposition: inline; filename="proxied.pdf"'); // Suggest filename
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Output the raw PDF content
echo $pdf_content;

exit;
?> 