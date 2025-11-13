<?php
// Set headers FIRST - nothing before this
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Simple error handling - no display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(0); // Turn off all error reporting

// Increase PHP time limits for long PDFs
ini_set('max_execution_time', 180); // 3 minutes
ini_set('memory_limit', '512M'); // 512MB

// Your API Key - REPLACE THIS with your actual key
$API_KEY = getenv('AI_API_KEY');

// Simple response function
function sendResponse($success, $data = null, $error = null) {
    $response = ['success' => $success];
    if ($data) $response = array_merge($response, $data);
    if ($error) $response['error'] = $error;
    echo json_encode($response);
    exit;
}

// Check for file upload
if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, null, 'No file uploaded or upload error');
}

// Basic validation
$file = $_FILES['pdfFile'];
if ($file['size'] > 10 * 1024 * 1024) { // Increased to 10MB
    sendResponse(false, null, 'File too large (max 10MB)');
}

if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
    sendResponse(false, null, 'File must be a PDF');
}

try {
    // Extract text from PDF
    $text = extractPDFText($file['tmp_name']);
    if (empty($text) || strlen(trim($text)) < 10) {
        sendResponse(false, null, 'Could not extract text from PDF');
    }
    
    // Call API
    $terms = callDeepSeekAPI($text);
    sendResponse(true, ['terms' => $terms, 'count' => count($terms)]);
    
} catch (Exception $e) {
    sendResponse(false, null, $e->getMessage());
}

function extractPDFText($filePath) {
    // Method 1: Try pdftotext
    $tempFile = tempnam(sys_get_temp_dir(), 'pdf') . '.txt';
    $command = "pdftotext \"$filePath\" \"$tempFile\" 2>&1";
    $output = shell_exec($command);
    
    if (file_exists($tempFile)) {
        $text = file_get_contents($tempFile);
        unlink($tempFile);
        if (!empty(trim($text))) return $text;
    }
    
    // Method 2: Try strings
    $text = shell_exec("strings \"$filePath\"");
    if (!empty(trim($text))) return $text;
    
    return null;
}

function callDeepSeekAPI($pdfText) {
    global $API_KEY;
    
    // Smart text limiting - prioritize beginning of document where definitions usually are
    $limitedText = smartTextLimit($pdfText, 30000);
    
    $prompt = "ANALYZE THIS DOCUMENT AND EXTRACT ALL KEY TERMS WITH DEFINITIONS:

DOCUMENT STRUCTURE:
- Bold text usually indicates important terms
- Bullet points often contain definitions  
- Headings and subheadings are key concepts
- Italicized text may indicate key terms
- Quoted text like \"Term\" may indicate important concepts
- Look for patterns like: 'Term - definition' or 'Term: definition' or '\"Term\" definition'

EXTRACTION RULES:
1. Extract EVERY term that appears in bold, headings, bullet points, italics, or quotes
2. For each term, extract the first complete sentence that describes or defines it
3. If the term appears in the definition sentence, remove it from the definition to avoid redundancy
4. Include both short and long definitions
5. Focus on terms that represent important concepts, services, attacks, or components
6. Include terms from headings, bullet points, and body text

OUTPUT FORMAT:
\"Term - definition sentence;\"

Be THOROUGH - extract comprehensively from all document sections.

PDF CONTENT:\n" . $limitedText;
    
    $data = [
        'model' => 'deepseek-chat',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'temperature' => 0.1,
        'max_tokens' => 5000 // Increased for longer responses
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.deepseek.com/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $API_KEY
        ],
        CURLOPT_TIMEOUT => 120, // Increased to 120 seconds for long PDFs
        CURLOPT_CONNECTTIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('Network error: ' . $curlError);
    }
    
    if ($httpCode !== 200) {
        throw new Exception('API returned error: HTTP ' . $httpCode);
    }
    
    $responseData = json_decode($response, true);
    if (!isset($responseData['choices'][0]['message']['content'])) {
        throw new Exception('Invalid response from API');
    }
    
    return parseTerms($responseData['choices'][0]['message']['content']);
}

function smartTextLimit($text, $limit) {
    // If text is already within limit, return as is
    if (strlen($text) <= $limit) {
        return $text;
    }
    
    // Split into paragraphs for smarter selection
    $paragraphs = preg_split('/\n\s*\n/', $text);
    $selectedContent = '';
    $currentLength = 0;
    
    // Phase 1: Prioritize high-value paragraphs (where definitions are)
    foreach ($paragraphs as $paragraph) {
        if ($currentLength >= $limit * 0.8) break;
        
        $paragraph = trim($paragraph);
        if (strlen($paragraph) < 30) continue; // Skip very short paragraphs
        
        // Score paragraph based on likelihood of containing terms/definitions
        $score = calculateParagraphScore($paragraph);
        
        // High-scoring paragraphs get priority
        if ($score >= 5 && ($currentLength + strlen($paragraph)) < $limit * 0.8) {
            $selectedContent .= $paragraph . "\n\n";
            $currentLength += strlen($paragraph) + 2;
        }
    }
    
    // Phase 2: If we have space, add medium-priority content
    if ($currentLength < $limit * 0.6) {
        foreach ($paragraphs as $paragraph) {
            if ($currentLength >= $limit * 0.9) break;
            
            $paragraph = trim($paragraph);
            if (strlen($paragraph) < 30) continue;
            
            $score = calculateParagraphScore($paragraph);
            
            // Medium-scoring paragraphs fill remaining space
            if ($score >= 3 && ($currentLength + strlen($paragraph)) < $limit * 0.9) {
                if (!str_contains($selectedContent, $paragraph)) { // Avoid duplicates
                    $selectedContent .= $paragraph . "\n\n";
                    $currentLength += strlen($paragraph) + 2;
                }
            }
        }
    }
    
    // Phase 3: Ensure we use the full limit with remaining content
    if ($currentLength < $limit) {
        $remainingSpace = $limit - $currentLength;
        
        // Add content from sections we haven't covered yet
        foreach ($paragraphs as $paragraph) {
            if ($currentLength >= $limit) break;
            
            $paragraph = trim($paragraph);
            if (strlen($paragraph) < 20) continue;
            
            if (!str_contains($selectedContent, $paragraph) && 
                ($currentLength + strlen($paragraph)) <= $limit) {
                $selectedContent .= $paragraph . "\n\n";
                $currentLength += strlen($paragraph) + 2;
            }
        }
    }
    
    // Final trim to ensure we don't exceed limit
    return substr($selectedContent, 0, $limit);
}

function calculateParagraphScore($paragraph) {
    $score = 0;
    
    // Patterns that indicate term definitions
    if (preg_match('/^[A-Z][a-z]+(\s+[A-Z][a-z]+)*\s*[-:]/', $paragraph)) {
        $score += 10; // "Term - definition" pattern
    }
    if (preg_match('/"\w[^"]*"\s*[-:]/', $paragraph)) {
        $score += 12; // "Term" - definition pattern
    }
    if (preg_match('/\*\*\w[^*]*\*\*/', $paragraph)) {
        $score += 8; // **Bold text** indicators
    }
    if (preg_match('/_\w[^_]*_/', $paragraph)) {
        $score += 6; // _Italic text_ indicators
    }
    if (preg_match('/\b(is|are|means|refers|defined as|known as|called)\b/i', $paragraph)) {
        $score += 7; // Definition keywords
    }
    if (preg_match('/^[•\-]\s*/', $paragraph)) {
        $score += 5; // Bullet points
    }
    if (preg_match('/^#+\s+/', $paragraph)) {
        $score += 8; // Headings (# Heading)
    }
    if (preg_match('/^[A-Z][^.!?]*[.:]\s*$/', $paragraph)) {
        $score += 4; // Short, complete sentences (likely definitions)
    }
    if (preg_match('/\b(example|for instance|such as|including)\b/i', $paragraph)) {
        $score -= 2; // Examples are less important than definitions
    }
    
    // Length-based scoring (medium paragraphs are often definitions)
    $length = strlen($paragraph);
    if ($length > 50 && $length < 300) {
        $score += 3;
    }
    
    return $score;
}

function parseTerms($content) {
    $lines = explode("\n", trim($content));
    $terms = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Handle both "Term - definition" and "Term: definition" formats
        $separatorPos = false;
        if (strpos($line, ' - ') !== false) {
            $separatorPos = strpos($line, ' - ');
            $separatorLength = 3;
        } elseif (strpos($line, ': ') !== false) {
            $separatorPos = strpos($line, ': ');
            $separatorLength = 2;
        }
        
        if ($separatorPos !== false) {
            $term = trim(substr($line, 0, $separatorPos));
            $definition = trim(substr($line, $separatorPos + $separatorLength));
            
            // Remove quotes and numbering
            $term = preg_replace('/^[\d\s\.\-"]+/', '', $term);
            $term = trim($term, ' "');
            $definition = trim($definition, ' ";');
            
            // Remove term from definition if it appears there
            $definition = removeTermFromDefinition($term, $definition);
            
            if (!empty($term) && !empty($definition) && strlen($definition) > 5) {
                $terms[] = ['term' => $term, 'definition' => $definition];
            }
        }
    }
    
    return $terms;
}

function removeTermFromDefinition($term, $definition) {
    // Remove the exact term from beginning of definition
    $termPattern = '/^' . preg_quote($term, '/') . '[,\s]*/i';
    $definition = preg_replace($termPattern, '', $definition);
    
    // Also remove common prefixes
    $definition = preg_replace('/^(is|are|was|were|has|have|had|will|shall|should|could|would|may|might|can)\s+/i', '', $definition);
    
    return trim($definition);
}



?>

