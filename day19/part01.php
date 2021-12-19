<?php declare(strict_types = 1);

// The 6 directions and 4 orientations. Uses the left-hand rule.

//      - *    
//      -       
//      -       
// -------------
//      -       
//      -       
//      -      

// Standing on XY plane, Z above us, looking along X axis. The point [2,3,4] becomes [2,3,4]
const DIRECTION_FORWARD = 1; 
// Standing on XY plane, Z above us, looking along Y axis. The point [2,3,4] becomes [-3, 2, 4]
const DIRECTION_LEFT = 2;
// Standing on XY plane, Z above us, looking along -X axis. The point [2,3,4] becomes [-2, -3, 4]
const DIRECTION_BACKWARDS = 3; 
// Standing on XY plane, Z above us, looking along -Y axis. The point [2,3,4] becomes [3, -2, 4]
const DIRECTION_RIGHT = 4; 
// Standing on XY plane, Z above us, looking along Z axis. The point [2,3,4] becomes [4, 3, -2]
const DIRECTION_UP = 5; 
// Standing on XY plane, Z above us, looking along -Z axis. The point [2,3,4] becomes [-4, 3, 2]
const DIRECTION_DOWN = 6;
// When standing on XY plane, Z above us, looking along X axis. The point [2,3,4] becomes [2, 3, 4]
const RIGHTSIDE_UP = 7;
// When standing on XY plane, -Z above us, looking along X axis. The point [2,3,4] becomes [2, -3, -4]
const UPSIDE_DOWN = 8;
// When standing on XZ plane, Y above us, looking along X axis. The point [2,3,4] becomes [2, -4, 3]
const TILT_LEFT = 9; 
// When standing on XZ plane, -Y above us, looking along X axis. The point [2,3,4] becomes [2, 4, -3]
const TILT_RIGHT = 10; 

class Point {
  public int $x;
  public int $y;
  public int $z;

  function __construct(int $x, int $y, int $z) {
    $this->x = $x;
    $this->y = $y;
    $this->z = $z;
  }

  function translate(int $dx, int $dy, int $dz): Point {
    return new Point($this->x + $dx, $this->y + $dy, $this->z + $dz);
  }

  // Return the relative position of this point from the input point.
  function relativePosition(Point $point): Point {
    return new Point(
      $this->x - $point->x,
      $this->y - $point->y,
      $this->z - $point->z,
    );
  }

  function pivot(Point $pivot, int $direction, int $orientation): Point {
    $new_point = $this->relativePosition($pivot);
    $new_x = $new_point->x;
    $new_y = $new_point->y;
    $new_z = $new_point->z;

    switch ($direction) {
      case DIRECTION_FORWARD:
        break;
      case DIRECTION_LEFT:
        [$new_x, $new_y, $new_z] = [-$new_y, $new_x, $new_z];
        break;
      case DIRECTION_BACKWARDS:
        [$new_x, $new_y, $new_z] = [-$new_x, -$new_y, $new_z];
        break;
      case DIRECTION_RIGHT:  
        [$new_x, $new_y, $new_z] = [$new_y, -$new_x, $new_z];
        break;
      case DIRECTION_UP:
        [$new_x, $new_y, $new_z] = [$new_z, $new_y, -$new_x];
        break;
      case DIRECTION_DOWN:
        [$new_x, $new_y, $new_z] = [-$new_z, $new_y, $new_x];
        break;
    }

    switch ($orientation) {
      case RIGHTSIDE_UP:
        break;
      case UPSIDE_DOWN:
        [$new_x, $new_y, $new_z] = [$new_x, -$new_y, -$new_z];
        break;
      case TILT_LEFT:
        [$new_x, $new_y, $new_z] = [$new_x, -$new_z, $new_y];
        break;
      case TILT_RIGHT:
        [$new_x, $new_y, $new_z] = [$new_x, $new_z, -$new_y];
        break;
    }

    // Undo the relative shift.
    $new_x += $pivot->x;
    $new_y += $pivot->y;
    $new_z += $pivot->z;

    return new Point($new_x, $new_y, $new_z);
  }
}

class ScannerResults {
  private array $point_map;
  private array $points;

  function __construct(array $points) {
    $this->point_map = [];
    $this->points = $points;

    // Filter down to unique points.
    foreach ($this->points as $point) {
      $this->set($point);
    }

    // Reconstruct the internal points array based on the uniqueness map.
    $new_points = [];
    foreach ($this->point_map as $x => $all_y) {
      foreach ($all_y as $y => $all_z) {
        foreach ($all_z as $z => $ignore) {
          $new_points[] = new Point($x, $y, $z);
        }
      }
    }
    $this->points = $new_points;
  }

  function set(Point $point) {
    $this->point_map[$point->x] = $this->point_map[$point->x] ?? [];
    $this->point_map[$point->x][$point->y] = $this->point_map[$point->x][$point->y] ?? [];
    $this->point_map[$point->x][$point->y][$point->z] = true;
  }

  function translate(int $dx, int $dy, int $dz): ScannerResults {
    return new ScannerResults(
      array_map(function(Point $point) use ($dx, $dy, $dz): Point {
        return $point->translate($dx, $dy, $dz);
      }, $this->points)
    );
  }

  function pivot(Point $pivot, int $direction, int $orientation): ScannerResults {
    return new ScannerResults(
      array_map(function(Point $point) use ($pivot, $direction, $orientation): Point {
        return $point->pivot($pivot, $direction, $orientation);
      }, $this->points),
    );
  }

  function uniqueCount(): int {
    $count = 0;
    foreach ($this->point_map as $x => $all_y) {
      foreach ($all_y as $y => $all_z) {
        $count += count($all_z);
      }
    }
    return $count;
  }

  function merge(ScannerResults $other): ScannerResults {
    return new ScannerResults(array_merge($this->points, $other->points));
  }

  function findMerge(ScannerResults $other): ?ScannerResults {
    foreach ($other->points as $other_point) {
      foreach ($this->points as $point) {
        // Shift this grid towards the other point by picking a point and snapping it to the other point.
        $relative_position = $point->relativePosition($other_point);
        $shifted = $this->translate(-$relative_position->x, -$relative_position->y, -$relative_position->z);

        for ($direction = DIRECTION_FORWARD; $direction <= DIRECTION_DOWN; $direction++) {
          for ($orientation = RIGHTSIDE_UP; $orientation <= TILT_RIGHT; $orientation++) {
            $pivoted = $shifted->pivot($other_point, $direction, $orientation);
            $merged = $pivoted->merge($other);
            if ($merged->uniqueCount() + 12 <= $this->uniqueCount() + $other->uniqueCount()) {
              return $merged;
            }
          }
        }
      }
    }
    return null;
  }
}

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the scanner inputs.
$scanner_results = [];
while (($line = fscanf($file_input, "--- scanner %d ---\n")) !== false) {
  $scanner_points = [];

  while (($point = fscanf($file_input, "%d,%d,%d\n"))) {
    $scanner_points[] = new Point($point[0], $point[1], $point[2]);
  }
  $scanner_results[] = new ScannerResults($scanner_points);
}

while (count($scanner_results) > 1) {
  $merged = [];

  $count = count($scanner_results);
  print("{$count} remaining\n");
  for ($i = 0; $i < $count; $i++) {
    for ($j = 0; $j < $count; $j++) {
      if ($i === $j) {
        continue;
      }
      if (!isset($scanner_results[$i]) || !isset($scanner_results[$j])) {
        continue;
      }
      $new = $scanner_results[$i]->findMerge($scanner_results[$j]);
      if ($new === null) {
        continue;
      }
      unset($scanner_results[$i]);
      unset($scanner_results[$j]);
      $merged[] = $new;
    }
  }
  $scanner_results = array_merge($merged, array_values($scanner_results));
}
$count = $scanner_results[0]->uniqueCount();

print("The count is [$count]\n");
