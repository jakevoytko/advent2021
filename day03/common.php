<?php

// Takes an array of strings assumed to be binary strings: "01101010111" etc.
// Assumes they're all the same length.
// Returns an array of the count of all of the ones at row 0, all the ones at row 1, etc.
function countBitsPerPosition(array $lines): array {
  $accumulator = [];

  $line_count = count($lines);
  for ($i = 0; $i < $line_count; $i++) {
    $line = $lines[$i];
    $length = strlen($line);

    // Initialize the accumulator on the first pass.
    if (empty($accumulator)) {
      $accumulator = array_fill(0, $length, 0);
    }

    // Count the corresponding bits.
    for ($j = 0; $j < $length; $j++) {
      $accumulator[$j] += ($line[$j] === '1') ? 1 : 0;
    }

  }

  return $accumulator;
}