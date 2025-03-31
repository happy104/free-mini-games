<?php
// Game Data Update Tool

// Load necessary functions and configuration
require_once 'functions.php';

// Display simple HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Update Game Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .info {
            background-color: #d9edf7;
            color: #31708f;
            border: 1px solid #bce8f1;
        }
        pre {
            background: #f7f7f7;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: auto;
        }
        a {
            color: #3498db;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .nav {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Game Data Update Tool</h1>";

// Try to update game data
try {
    // Get all games
    $games = getAllGames();
    
    // Show number of games found
    echo "<div class='message info'>Found " . count($games) . " games</div>";
    
    // Display all games (newest games first by add order)
    $featuredGames = array_reverse($games);
    
    // Generate game data JSON file
    $gameDataPath = ROOT_PATH . '/js/games-data.json';
    
    // Prepare game data, only include fields needed by frontend
    $gameDataForJson = [];
    foreach ($featuredGames as $game) {
        $category = getCategory($game['category']);
        $categoryName = $category ? $category['name'] : 'Uncategorized';
        
        $gameDataForJson[] = [
            'id' => $game['id'],
            'title' => $game['title'],
            'category' => $game['category'],
            'categoryName' => $categoryName,
            'rating' => $game['rating'],
            'thumbnail' => $game['thumbnail']
        ];
    }
    
    // Save JSON data file
    $jsonData = json_encode($gameDataForJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($gameDataPath, $jsonData);
    
    echo "<div class='message success'>Successfully updated game data to js/games-data.json</div>";
    
    // Show JSON data preview
    echo "<h2>JSON Data Preview (First 5 games):</h2>";
    echo "<pre>" . htmlspecialchars(json_encode(array_slice($gameDataForJson, 0, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    // Update index.html and games.html to ensure they have the correct script references
    $homepagePath = ROOT_PATH . '/index.html';
    $categoryPagePath = ROOT_PATH . '/games.html';
    
    $pagesUpdated = [];
    
    if (file_exists($homepagePath)) {
        $homepageContent = file_get_contents($homepagePath);
        // Check if <script src="js/games-loader.js"></script> already exists
        if (strpos($homepageContent, 'games-loader.js') === false) {
            // Add our new script to the JavaScript loading section
            $scriptInsertPoint = strpos($homepageContent, '<!-- JavaScript Files -->');
            if ($scriptInsertPoint === false) {
                $scriptInsertPoint = strpos($homepageContent, '</body>');
            }
            
            if ($scriptInsertPoint !== false) {
                $scriptTag = "\n<script src=\"js/games-loader.js\"></script>\n";
                $homepageContent = substr_replace($homepageContent, $scriptTag, $scriptInsertPoint, 0);
                file_put_contents($homepagePath, $homepageContent);
                $pagesUpdated[] = 'index.html';
            }
        } else {
            echo "<div class='message info'>index.html already includes games-loader.js reference</div>";
        }
    }
    
    if (file_exists($categoryPagePath)) {
        $categoryPageContent = file_get_contents($categoryPagePath);
        // Check if <script src="js/games-loader.js"></script> already exists
        if (strpos($categoryPageContent, 'games-loader.js') === false) {
            // Add our new script to the JavaScript loading section
            $scriptInsertPoint = strpos($categoryPageContent, '<!-- JavaScript Files -->');
            if ($scriptInsertPoint === false) {
                $scriptInsertPoint = strpos($categoryPageContent, '</body>');
            }
            
            if ($scriptInsertPoint !== false) {
                $scriptTag = "\n<script src=\"js/games-loader.js\"></script>\n";
                $categoryPageContent = substr_replace($categoryPageContent, $scriptTag, $scriptInsertPoint, 0);
                file_put_contents($categoryPagePath, $categoryPageContent);
                $pagesUpdated[] = 'games.html';
            }
        } else {
            echo "<div class='message info'>games.html already includes games-loader.js reference</div>";
        }
    }
    
    if (!empty($pagesUpdated)) {
        echo "<div class='message success'>Updated script references in the following pages: " . implode(', ', $pagesUpdated) . "</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='message error'>Error updating game data: " . $e->getMessage() . "</div>";
}

// Show navigation links
echo "    <div class='nav'>
        <a href='../index.html'>Back to Homepage</a> | 
        <a href='dashboard.php'>Back to Dashboard</a>
    </div>
    </div>
</body>
</html>"; 