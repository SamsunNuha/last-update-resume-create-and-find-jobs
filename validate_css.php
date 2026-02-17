<?php
$file = 'assets/css/style.css';
if (!file_exists($file)) {
    die("File not found: $file");
}

$content = file_get_contents($file);
$lines = explode("\n", $content);

$openBraces = 0;
$closeBraces = 0;
$inComment = false;
$errors = [];

for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    $lineNum = $i + 1;
    
    // Check for comments
    $tempLine = $line;
    while (true) {
        if (!$inComment) {
            $start = strpos($tempLine, '/*');
            if ($start !== false) {
                $inComment = true;
                // Remove everything before comment start for brace counting? 
                // No, braces before comment are valid.
                // But braces inside comment are not.
                
                // Helper to remove comments for brace counting
                // simple regex approach might be better
                $tempLine = substr($tempLine, $start + 2);
            } else {
                break;
            }
        } else {
            $end = strpos($tempLine, '*/');
            if ($end !== false) {
                $inComment = false;
                $tempLine = substr($tempLine, $end + 2);
            } else {
                break;
            }
        }
    }
}

// Check for unclosed quotes
$inSingleQuote = false;
$inDoubleQuote = false;
$quoteErrorLine = 0;

// Remove comments from content
$stripped = preg_replace('!/\*.*?\*/!s', '', $content);
$strippedLines = explode("\n", $stripped);
foreach ($strippedLines as $idx => $sLine) {
    if (trim($sLine) === '') continue;
    
    // Check for missing semicolon in property definitions
    // Heuristic: line has ":" but no ";" and not "{" and not "}" and not "," (for multi-line)
    if (strpos($sLine, ':') !== false && strpos($sLine, ';') === false) {
        if (strpos($sLine, '{') === false && strpos($sLine, '}') === false && strpos($sLine, ',') === false) {
             // Exclude some valid cases like media queries (though they usually have {)
             // and base64 data urls (which might be long)
             echo "WARNING: Possible missing semicolon on line " . ($idx + 1) . ": " . trim($sLine) . "\n";
        }
    }
}

?>
