<?php

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Track the most recent window.
// If this gets longer, manage in an array.
$third_from_last = NULL;
$second_from_last = NULL;
$previous = NULL;
$count = 0;

// Read the file line-by-line.
while (!feof($file_input)) {
  $line = fgets($file_input);

  // Don't process blank lines, such as final blank lines.
  if (!strlen($line)) {
    continue;
  }

  $current = intval($line);
  if ($current === NULL) {
    throw new Exception("value [{$line}] was not all numeric");
  }

  // The comparison is only valid when the pipeline is fully saturated.
  if ($third_from_last !== NULL) {  
    // Since the sliding average has 2 other numbers in common, the
    // sliding average will increase when the current number is greater
    // than the third-from-last.
    if ($current > $third_from_last) {
      $count++;
    }
  }

  $third_from_last = $second_from_last;
  $second_from_last = $previous;
  $previous = $current;
}

print("Answer is [{$count}]\n");
