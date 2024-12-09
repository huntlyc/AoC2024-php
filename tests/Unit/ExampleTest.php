<?php

use AOC\D08\P1\InputToGrid;

test('InputToGrid', function () {
    $input = <<<EOF
...
...
...
EOF;
    $grid = new InputToGrid($input);
    expect($grid->getGrid())->toBeArray()->toBe(['...', '...', '...']);
});
