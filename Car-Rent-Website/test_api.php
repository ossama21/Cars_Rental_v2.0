<?php
// A simple browser-friendly script to test the Gemini API connection
$apiResults = [];

// Handle export request - must come before any HTML output
if (isset($_GET['export']) && $_GET['export'] == 'true' && isset($_GET['data'])) {
    $exportData = json_decode(urldecode($_GET['data']), true);
    
    // Set headers for file download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="gemini_api_test_' . date('Y-m-d_H-i-s') . '.json"');
    header('Pragma: no-cache');
    
    // Output the data and exit
    echo json_encode($exportData, JSON_PRETTY_PRINT);
    exit;
}

if (isset($_GET['test']) && $_GET['test'] == 'true') {
    $apiKey = "AIzaSyCxIMgE1JXj8ZN70DeNVzuFyAdFBp9CypY";
    // Updated API endpoint with correct model name and version
    $apiUrl = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent";

    // A simple test prompt
    $requestBody = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => "Hello, please respond with a short message to confirm you're working."]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 100,
        ]
    ];

    // Collect system information for better troubleshooting
    $systemInfo = [
        "php_version" => phpversion(),
        "server" => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        "curl_version" => function_exists('curl_version') ? curl_version()['version'] : 'Not available',
        "ssl_support" => function_exists('curl_version') ? (curl_version()['features'] & CURL_VERSION_SSL ? 'Yes' : 'No') : 'Unknown',
        "user_agent" => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ];

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl . "?key=" . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    // Add detailed curl info for debugging
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    // Execute the request
    $response = curl_exec($ch);

    // Get verbose debug information
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);

    // Check for errors
    $error = null;
    if($response === false) {
        $error = curl_error($ch);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlInfo = curl_getinfo($ch);

    // Close cURL
    curl_close($ch);

    // Prepare results
    $apiResults = [
        "timestamp" => date("Y-m-d H:i:s"),
        "status" => $error ? "error" : ($httpCode == 200 ? "success" : "failed"),
        "http_code" => $httpCode,
        "system_info" => $systemInfo,
        "curl_info" => $curlInfo,
        "curl_verbose" => $verboseLog
    ];

    if ($error) {
        $apiResults["error"] = $error;
    } else {
        // Decode the response
        $responseData = json_decode($response, true);
        $apiResults["raw_response"] = $response; // Store raw response for debugging
        
        if($httpCode == 200 && isset($responseData["candidates"][0]["content"]["parts"][0]["text"])) {
            $apiResults["response"] = $responseData["candidates"][0]["content"]["parts"][0]["text"];
            $apiResults["message"] = "API connection successful!";
        } else {
            $apiResults["response"] = $responseData;
            $apiResults["message"] = "API returned a response, but in an unexpected format.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini API Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .api-key {
            font-family: monospace;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
            margin-bottom: 20px;
            word-break: break-all;
        }
        .result-container {
            margin-top: 30px;
            padding: 20px;
            border-radius: 5px;
        }
        .result-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .result-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .result-neutral {
            background-color: #e2e3e5;
            border: 1px solid #d6d8db;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .system-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .system-info h5 {
            margin-bottom: 10px;
            color: #495057;
        }
        .collapsible {
            cursor: pointer;
        }
        .collapse-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .collapse-content.show {
            max-height: 500px;
            overflow-y: auto;
        }
        .toggle-icon {
            transition: transform 0.3s;
        }
        .rotate {
            transform: rotate(90deg);
        }
        
        /* Loading spinner */
        .spinner {
            display: none;
            margin-left: 10px;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="mb-4">Gemini API Connection Test</h1>
        
        <div class="mb-4">
            <h4>Testing API Key:</h4>
            <div class="api-key">AIzaSyCxIMgE1JXj8ZN70DeNVzuFyAdFBp9CypY</div>
            <p>Model: <code>gemini-1.5-pro</code></p>
        </div>
        
        <form action="test_api.php" method="get" id="testForm">
            <input type="hidden" name="test" value="true">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-vial"></i> Test API Connection
                <div class="spinner" id="loadingSpinner"></div>
            </button>
        </form>
        
        <?php if (isset($_GET['test'])): ?>
            <div class="result-container <?php 
                if (isset($apiResults['status'])) {
                    echo $apiResults['status'] == 'success' ? 'result-success' : 'result-error';
                } else {
                    echo 'result-neutral';
                }
            ?>">
                <h4>Test Results:</h4>
                
                <?php if (isset($apiResults['status']) && $apiResults['status'] == 'success'): ?>
                    <div class="alert alert-success">
                        <strong><i class="fas fa-check-circle"></i> Success!</strong> The API connection is working correctly.
                    </div>
                    <p><strong>API Response:</strong></p>
                    <p><?php echo htmlspecialchars($apiResults['response']); ?></p>
                <?php elseif (isset($apiResults['error'])): ?>
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> There was a problem connecting to the API.
                    </div>
                    <p><strong>Error Message:</strong></p>
                    <p><?php echo htmlspecialchars($apiResults['error']); ?></p>
                <?php elseif (isset($apiResults['status']) && $apiResults['status'] == 'failed'): ?>
                    <div class="alert alert-warning">
                        <strong><i class="fas fa-exclamation-triangle"></i> Failed!</strong> The API returned an error response.
                    </div>
                    <p><strong>HTTP Code:</strong> <?php echo $apiResults['http_code']; ?></p>
                    <?php if (isset($apiResults['response'])): ?>
                        <p><strong>Response Details:</strong></p>
                        <pre><?php print_r($apiResults['response']); ?></pre>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- System Information section -->
                <div class="system-info">
                    <h5 class="collapsible" data-target="sysInfoCollapse">
                        <i class="fas fa-info-circle"></i> System Information
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </h5>
                    <div class="collapse-content" id="sysInfoCollapse">
                        <table class="table table-sm table-striped">
                            <tbody>
                                <tr>
                                    <td>PHP Version</td>
                                    <td><?php echo htmlspecialchars($apiResults['system_info']['php_version']); ?></td>
                                </tr>
                                <tr>
                                    <td>Server</td>
                                    <td><?php echo htmlspecialchars($apiResults['system_info']['server']); ?></td>
                                </tr>
                                <tr>
                                    <td>cURL Version</td>
                                    <td><?php echo htmlspecialchars($apiResults['system_info']['curl_version']); ?></td>
                                </tr>
                                <tr>
                                    <td>SSL Support</td>
                                    <td><?php echo htmlspecialchars($apiResults['system_info']['ssl_support']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Detailed Debug Information section -->
                <div class="system-info mt-3">
                    <h5 class="collapsible" data-target="debugInfoCollapse">
                        <i class="fas fa-bug"></i> Detailed Debug Information
                        <i class="fas fa-chevron-right toggle-icon"></i>
                    </h5>
                    <div class="collapse-content" id="debugInfoCollapse">
                        <h6>cURL Verbose Log:</h6>
                        <pre><?php echo htmlspecialchars($apiResults['curl_verbose']); ?></pre>
                        
                        <h6>cURL Info:</h6>
                        <pre><?php print_r($apiResults['curl_info']); ?></pre>
                    </div>
                </div>
                
                <!-- Export buttons -->
                <div class="action-buttons">
                    <a href="test_api.php?export=true&data=<?php echo urlencode(json_encode($apiResults)); ?>" class="btn btn-info">
                        <i class="fas fa-file-export"></i> Export Error Details
                    </a>
                    
                    <button type="button" class="btn btn-secondary" onclick="copyToClipboard('<?php echo addslashes(json_encode($apiResults)); ?>')">
                        <i class="fas fa-copy"></i> Copy to Clipboard
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <h4>Troubleshooting Tips:</h4>
            <ul>
                <li>Make sure the API key is valid and has access to the Gemini API</li>
                <li>Check if you've enabled the Generative Language API in your Google Cloud Console</li>
                <li>Ensure you're using the correct model name (<code>gemini-1.5-pro</code>) and API version (<code>v1</code>)</li>
                <li>Ensure your server has outbound internet access and can reach the Google API endpoints</li>
                <li>Verify PHP curl extension is enabled and working correctly</li>
                <li>Check your firewall or proxy settings if you're behind a corporate network</li>
                <li>Make sure your SSL certificates are up-to-date if you're having SSL-related issues</li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show loading spinner when form is submitted
            document.getElementById('testForm').addEventListener('submit', function() {
                document.getElementById('loadingSpinner').style.display = 'inline-block';
            });
            
            // Handle collapsible sections
            document.querySelectorAll('.collapsible').forEach(function(element) {
                element.addEventListener('click', function() {
                    const target = document.getElementById(this.getAttribute('data-target'));
                    target.classList.toggle('show');
                    this.querySelector('.toggle-icon').classList.toggle('rotate');
                });
            });
        });
        
        // Function to copy JSON data to clipboard
        function copyToClipboard(jsonData) {
            // Parse and re-stringify for pretty formatting
            const prettyJson = JSON.stringify(JSON.parse(jsonData), null, 2);
            
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = prettyJson;
            textarea.style.position = 'fixed';  // Prevent scrolling to bottom
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            
            try {
                // Execute copy command
                const successful = document.execCommand('copy');
                if (successful) {
                    alert('Debug information copied to clipboard!');
                } else {
                    alert('Failed to copy text');
                }
            } catch (err) {
                console.error('Error copying text: ', err);
                alert('Error copying text: ' + err);
            }
            
            // Remove the temporary element
            document.body.removeChild(textarea);
        }
    </script>
</body>
</html>