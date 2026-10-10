<?php

$translations = ["kjv", "ehv"];
$tomorrow = new DateTime("tomorrow");
$startDate = new DateTime("2022-04-07");
$tomorrowsNumber = (int) $tomorrow->diff($startDate)->format("%a");

foreach ($translations as $translation) {
    $folder = "bibordle-bibles/{$translation}";

    $words = loadJsonFile("{$folder}/words.json");
    $bible = loadJsonFile("{$folder}/bible.json");

    if (!$words || !$bible) {
        error_log("Failed to load JSON for translation: {$translation}");
        continue;
    }

    $wordOfTheDay = $words[($tomorrowsNumber - 1) % count($words)];

    $matchingVerses = array_filter($bible, function ($verse) use ($wordOfTheDay) {
        return containsExactWord($wordOfTheDay, strtolower($verse->text));
    });

    if (empty($matchingVerses)) {
        error_log("No matching verses found for word '{$wordOfTheDay}' in {$translation}");
        continue;
    }

    $randomVerse = $matchingVerses[array_rand($matchingVerses)];

    $verseText = $randomVerse->text;
    $reference = "{$randomVerse->book_name} {$randomVerse->chapter}:{$randomVerse->verse}";

    $outputData = [
        "dailyNumber" => $tomorrowsNumber,
        "word" => $wordOfTheDay,
        "verse" => $verseText,
        "reference" => $reference,
        "wordCount" => count($matchingVerses),
    ];

    $outputJson = json_encode($outputData, JSON_UNESCAPED_UNICODE);
    file_put_contents("{$translation}/{$tomorrowsNumber}.json", $outputJson);

    // Delete file from 3 days ago
    $oldNumber = $tomorrowsNumber - 3;
    $oldFile = "{$translation}/{$oldNumber}.json";
    if (file_exists($oldFile) && is_writable($oldFile)) {
        unlink($oldFile);
    }
}

/**
 * Load and decode a JSON file safely.
 */
function loadJsonFile(string $filename) {
    if (!file_exists($filename)) {
        error_log("File not found: {$filename}");
        return null;
    }

    $content = file_get_contents($filename);
    if ($content === false) {
        error_log("Failed to read file: {$filename}");
        return null;
    }

    $encodedContent = mb_convert_encoding(
        mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true)),
        'HTML-ENTITIES',
        'UTF-8'
    );

    return json_decode($encodedContent);
}

/**
 * Check if a word exists in a string as a full word (not as a substring).
 */
function containsExactWord(string $word, string $text): bool {
    $wspaces = " " . $text . " ";

    $process1 = preg_replace('/\s[^a-zA-Z]+?/', ' ', $wspaces);
    $process2 = preg_replace('/[^a-zA-Z]+?\s/', ' ', $process1);

    // Add spaces around the word for accurate matching
    $wordToCheck = ' ' . $word . ' ';
    $processedString = ' ' . $process2 . ' '; // Add spaces at the beginning and end

    // Check if the word exists in the processed string
    $wordExists = strpos($processedString, $wordToCheck) !== false;

    return $wordExists;
}

?>
