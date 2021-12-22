<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Attempt to treat the input as rectangular slices and take the efficiency hit, for ease
// of coding.
class Rectangle {
  public int $x1, $x2, $y1, $y2;

  public function __construct(int $x1, int $x2, int $y1, int $y2) {
    $this->x1 = min($x1, $x2);
    $this->x2 = max($x1, $x2);
    $this->y1 = min($y1, $y2);
    $this->y2 = max($y1, $y2);
  }

  public function count(): int {
    return ($this->x2 - $this->x1 + 1) * ($this->y2 - $this->y1 + 1);
  }

  public function intersection(Rectangle $other): ?Rectangle {
    $new_x1 = max($this->x1, $other->x1);
    $new_x2 = min($this->x2, $other->x2);
    if ($new_x1 > $new_x2) {
      return null;
    }

    $new_y1 = max($this->y1, $other->y1);
    $new_y2 = min($this->y2, $other->y2);
    if ($new_y1 > $new_y2) {
      return null;
    }

    return new Rectangle($new_x1, $new_x2, $new_y1, $new_y2);
  }

  // Remove $this without $other. Can return between 0 and 8 rectangles
  public function subtract(Rectangle $other): array {
    $disjoint_rectangles = [
      new Rectangle(PHP_INT_MIN, $other->x1 - 1, $other->y2 + 1, PHP_INT_MAX), // above and to left
      new Rectangle($other->x1, $other->x2, $other->y2 + 1, PHP_INT_MAX), // above
      new Rectangle($other->x2 + 1, PHP_INT_MAX, $other->y2 + 1, PHP_INT_MAX), // above and to right
      new Rectangle(PHP_INT_MIN, $other->x1 - 1, $other->y1, $other->y2), // left
      new Rectangle($other->x2 + 1, PHP_INT_MAX, $other->y1, $other->y2), // right
      new Rectangle(PHP_INT_MIN, $other->x1 - 1, PHP_INT_MIN, $other->y1 - 1), // below and to left
      new Rectangle($other->x1, $other->x2, PHP_INT_MIN, $other->y1 - 1), // below
      new Rectangle($other->x2 + 1, PHP_INT_MAX, PHP_INT_MIN, $other->y1 - 1), // below and to right
    ];
    $final_rectangles = [];

    foreach ($disjoint_rectangles as $disjoint_rectangle) {
      $intersection = $this->intersection($disjoint_rectangle);
      if ($intersection !== null) {
        $final_rectangles[] = $intersection;
      }
    }

    return $final_rectangles;
  }
}

// Read the reboot instructions.
$reboot_steps = [];
while (($line = fscanf($file_input, "%s x=%d..%d,y=%d..%d,z=%d..%d\n")) !== false) {
  $on = $line[0] === 'on';
  [$x0, $x1, $y0, $y1, $z0, $z1] = [
    $line[1], $line[2], $line[3], $line[4], $line[5], $line[6],
  ];

  $reboot_steps[] = [$on, $x0, $x1, $y0, $y1, $z0, $z1];
}

// All of the existing non-overlapping rectangles in the reactor core. Map from Z index to the array
// of rectangles at that Z index.
$reactor_rectangles = [];
$window = new Rectangle(-50 /* x0 */, 50 /* x1 */, -50 /* y0 */, 50 /* y1 */);
foreach ($reboot_steps as $step) {
  [$on, $x0, $x1, $y0, $y1, $z0, $z1] = $step;
  // Skip any steps that are completely outside of the initializer window.
  if ($x0 > 50 && $x1 > 50 || $y0 > 50 && $y1 > 50 || $z0 > 50 && $z1 > 50 ||
      $x0 < -50 && $x1 < -50 || $y0 < -50 && $y1 < -50 || $z0 < -50 && $z1 < -50) {
    continue;
  }

  // Decompose the input step into rectangular planes along the Z axis, limiting the rectangles
  // to the input window of [-50, 50],[-50, 50],[-50,50]
  $xy_rectangle = new Rectangle($x0, $x1, $y0, $y1);
  $step_rectangle = $window->intersection($xy_rectangle);
  for ($z = max($z0, -50); $z <= min($z1, 50); $z++) {
    // Subtract any existing rectangles at this Z index from the rectangle to be added, and then
    // add it to this Z index.
    if ($on) {
      $step_rectangles = [$step_rectangle];
      foreach (($reactor_rectangles[$z] ?? []) as $rectangle_at_z) {
        $new_step_rectangles = [];
        foreach ($step_rectangles as $rectangle_being_added) {
          $new_step_rectangles = array_merge($new_step_rectangles, $rectangle_being_added->subtract($rectangle_at_z));
        }
        $step_rectangles = $new_step_rectangles;
      }
      $reactor_rectangles[$z] = array_merge($reactor_rectangles[$z] ?? [], $step_rectangles);
    } else {
      // Subtract the "off" rectangle from each of the rectangles at this Z index.
      $new_z_rectangles = [];
      foreach (($reactor_rectangles[$z] ?? []) as $rectangle_at_z) {
        $new_z_rectangles = array_merge($new_z_rectangles, $rectangle_at_z->subtract($step_rectangle));
      }
      $reactor_rectangles[$z] = $new_z_rectangles;
    }
  }
}

$sum = 0;
foreach ($reactor_rectangles as $z_rectangles) {
  foreach ($z_rectangles as $z_rectangle) {
    $sum += $z_rectangle->count();
  }
}
print("The final cube count is [$sum]\n");
