<?php
class QRcode {
    public static function png($text, $file_path) {
        // Generate QR code using Google Charts API
        $url = 'https://chart.googleapis.com/chart?';
        $params = array(
            'cht' => 'qr',
            'chs' => '300x300',
            'chl' => urlencode($text),
            'choe' => 'UTF-8'
        );
        
        $url .= http_build_query($params);
        
        // Enable URL fopen
        ini_set('allow_url_fopen', 1);
        
        // Get the QR code image with error handling
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true, // Ignore HTTP errors
                'timeout' => 10 // Set timeout to 10 seconds
            ]
        ]);
        
        $qr_image = @file_get_contents($url, false, $context);
        
        // Save the image to the specified path
        if ($qr_image !== false) {
            file_put_contents($file_path, $qr_image);
            return true;
        }
        return false;
    }
} 