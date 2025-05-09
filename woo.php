<?php

// Function to initialize cURL with options
function init_curl($url, $cookies = '') {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEFILE => $cookies,
        CURLOPT_COOKIEJAR => $cookies,
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.8',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        ]
    ]);
    return $ch;
}

// Function to check if the URL starts with http:// or https://
function ensure_https($url) {
    // If the URL doesn't already start with "http:// or https://", prepend "https://"
    if (!preg_match('#^https?://#', $url)) {
        $url = 'https://' . $url;
    }
    return $url;
}

// Function to extract product IDs from HTML content
function extract_product_ids($html) {
    preg_match_all('/add-to-cart=(\d+)/', $html, $matches);
    return $matches[1];  // Return an array of product IDs
}

// Function to extract product quantity (if it's part of the page)
function extract_quantity($html, $product_id) {
    preg_match('/<input.*name="quantity\[' . $product_id . '\]" value="(\d+)"/', $html, $match);
    return isset($match[1]) ? $match[1] : 1;  // Return the quantity if found, default to 1
}

// Main function to get product details
function get_product_details($domain, $cookies = '') {
    // Ensure the domain includes https://
    $domain = ensure_https($domain);
    
    $url = $domain . '/?s=&post_type=product';  // Adjust this URL to fetch products
    $ch = init_curl($url, $cookies);
    
    // Execute the GET request
    $response = curl_exec($ch);
    if (!$response) {
        curl_close($ch);
        return false;  // Return false if the cURL request fails
    }
    curl_close($ch);
    
    // Extract product IDs
    $product_ids = extract_product_ids($response);
    
    // Check if there's at least one product found
    if (empty($product_ids)) {
        return false;  // Return false if no product IDs are found
    }
    
    // Get the first product ID
    $product_id = $product_ids[0];
    
    // Extract quantity for the first product ID
    $quantity = extract_quantity($response, $product_id);
    
    // Return the product ID and quantity for the first product
    return ['product_id' => $product_id, 'quantity' => $quantity];
}

// Check if domain is provided in the request
if (isset($_POST['domain'])) {
    $domain = $_POST['domain'];

    // Get the first product details
    $product = get_product_details($domain);

    // Output the result with the generated link for the first product
    if ($product) {
        $link = "{$domain}/shop/?add-to-cart={$product['product_id']}&quantity={$product['quantity']}";
        echo $link;  // Output the single link
    } else {
        echo "Failed to capture";  // Shortened error message (only displayed once)
    }
}
?>