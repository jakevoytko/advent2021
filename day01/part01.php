<?php

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

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

  if ($previous !== NULL) {  
    if ($current > $previous) {
      $count++;
    }
  }

  $previous = $current;
}

print("Answer is [{$count}]\n");
