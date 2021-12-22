<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

class RectangularPrism {
  public int $x1, $x2, $y1, $y2, $z1, $z2;

  public function __construct(int $x1, int $x2, int $y1, int $y2, int $z1, int $z2) {
    $this->x1 = min($x1, $x2);
    $this->x2 = max($x1, $x2);
    $this->y1 = min($y1, $y2);
    $this->y2 = max($y1, $y2);
    $this->z1 = min($z1, $z2);
    $this->z2 = max($z1, $z2);
  }

  public function count(): int {
    return ($this->x2 - $this->x1 + 1) * ($this->y2 - $this->y1 + 1) * ($this->z2 - $this->z1 + 1);
  }

  public function intersection(RectangularPrism $other): ?RectangularPrism {
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

    $new_z1 = max($this->z1, $other->z1);
    $new_z2 = min($this->z2, $other->z2);
    if ($new_z1 > $new_z2) {
      return null;
    }

    return new RectangularPrism($new_x1, $new_x2, $new_y1, $new_y2, $new_z1, $new_z2);
  }

  // Remove $this without $other. Can return between 0 and 27 rectangular prisms
  public function subtract(RectangularPrism $other): array {
    // Shortcut when there is no intersection.
    if ($this->intersection($other) === null) {
      return [$this];
    }
    // All descriptions have X right, Y up, and Z forward
    $disjoint_prisms = [];
    foreach ([[PHP_INT_MIN, $other->x1 - 1], [$other->x1, $other->x2], [$other->x2 + 1, PHP_INT_MAX]] as $x_bound_index => $x_bounds) {
      foreach ([[PHP_INT_MIN, $other->y1 - 1], [$other->y1, $other->y2], [$other->y2 + 1, PHP_INT_MAX]] as $y_bound_index => $y_bounds) {
        foreach ([[PHP_INT_MIN, $other->z1 - 1], [$other->z1, $other->z2], [$other->z2 + 1, PHP_INT_MAX]] as $z_bound_index => $z_bounds) {
          if ($x_bound_index === 1 && $y_bound_index === 1 && $z_bound_index === 1) {
            continue;
          }
          $disjoint_prisms[] = new RectangularPrism(
            $x_bounds[0], $x_bounds[1],
            $y_bounds[0], $y_bounds[1],
            $z_bounds[0], $z_bounds[1]
          );
        }
      }
    }

    $final_prisms = [];
    foreach ($disjoint_prisms as $disjoint_prism) {
      $intersection = $this->intersection($disjoint_prism);
      if ($intersection !== null) {
        $final_prisms[] = $intersection;
      }
    }

    return $final_prisms;
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

$reactor_prisms = [];
foreach ($reboot_steps as $i => $step) {
  [$on, $x0, $x1, $y0, $y1, $z0, $z1] = $step;

  // Decompose the input step into rectangular planes along the Z axis
  $step_prism = new RectangularPrism($x0, $x1, $y0, $y1, $z0, $z1);
  if ($on) {
    $step_prisms = [$step_prism];
    foreach ($reactor_prisms as $reactor_prism) {
      $new_step_prisms = [];
      foreach ($step_prisms as $prism_being_added) {
        $new_step_prisms = array_merge($new_step_prisms, $prism_being_added->subtract($reactor_prism));
      }
      $step_prisms = $new_step_prisms;
    }
    $reactor_prisms = array_merge($reactor_prisms, $step_prisms);
  } else {
    $new_reactor_prisms = [];
    foreach ($reactor_prisms as $reactor_prism) {
      $new_reactor_prisms = array_merge($new_reactor_prisms, $reactor_prism->subtract($step_prism));
    }
    $reactor_prisms = $new_reactor_prisms;
  }
}

$sum = 0;
foreach ($reactor_prisms as $reactor_prism) {
  $sum += $reactor_prism->count();
}
print("The final cube count is [$sum]\n");
