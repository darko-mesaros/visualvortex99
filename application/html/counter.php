<?php
// Set the file path for storing the visitor count
$countFile = 'visitor_count.txt';

// Check if the file exists, if not, create it with an initial count of 0
if (!file_exists($countFile)) {
    $count = 0;
    file_put_contents($countFile, $count);
} else {
    // Read the current visitor count from the file
    $count = intval(file_get_contents($countFile));

    // Increment the visitor count
    $count++;

    // Write the updated count back to the file
    file_put_contents($countFile, $count);
}

$paddedCounter = sprintf('%08d', $count);
// Display the visitor count on the webpage
echo "<i>Number of visitors: </i><b>" . $paddedCounter ."</b>";
?>

