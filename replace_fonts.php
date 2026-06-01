<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/\.blade\.php$/', RegexIterator::MATCH);

foreach ($files as $file) {
    $path = $file->getPathname();
    // Exclude welcome for now
    if (strpos($path, 'welcome.blade.php') !== false) {
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;

    // Remove specific extremes on small text
    $content = preg_replace('/(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm)\s+font-black/', '$1 font-bold', $content);
    $content = preg_replace('/font-black\s+(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm)/', 'font-bold $1', $content);
    
    // Convert remaining inline font-black on regular text to font-bold unless it's a huge heading
    $content = preg_replace('/(text-base|text-lg|text-xl)\s+font-black/', '$1 font-extrabold', $content);
    $content = preg_replace('/font-black\s+(text-base|text-lg|text-xl)/', 'font-extrabold $1', $content);
    
    // Reduce extreme tracking on small texts
    $content = preg_replace('/(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm)\s+tracking-\[0\.[1-9]+em\]/', '$1 tracking-wider', $content);
    $content = preg_replace('/tracking-\[0\.[1-9]+em\]\s+(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm)/', 'tracking-wider $1', $content);

    // Remove explicitly declared font-sans or font-outfit in badges/labels (they should inherit)
    // Only keeping font-outfit if it's on text-2xl or larger.
    $content = preg_replace('/(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm|text-base|text-lg)\s+font-outfit/', '$1', $content);
    $content = preg_replace('/font-outfit\s+(text-xs|text-\[10px\]|text-\[11px\]|text-\[9px\]|text-sm|text-base|text-lg)/', '$1', $content);
    
    $content = preg_replace('/\bfont-sans\b/', '', $content);
    
    // Clean up multiple spaces left by regex but DO NOT remove newlines
    $content = preg_replace('/[ \t]{2,}/', ' ', $content);
    $content = str_replace(' class=" "', ' class=""', $content);
    $content = preg_replace('/class="\s+/', 'class="', $content);
    $content = preg_replace('/\s+"/', '"', $content);

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
    }
}
