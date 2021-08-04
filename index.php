<?php
define("RED", "\e[41m");
define("LGREEN", "\e[102m");
define("WHITE", "\e[0m");
define("DGRAY", "\e[0;100m");
echo "Первым делом зададим размер поля для разминирования: \n";

$handle = fopen("php://stdin", "r");
echo "Введите количество рядов\n";
$rows = (int)fgets($handle);
echo "Введите количество колонок\n";
$cols = (int)fgets($handle);

//Функция возвращает массив по заданным параметрам $x и $y
function create_array($x, $y)
{

    $x = intval($x, 10);
    $y = intval($y, 10);

    //Создание массива с нулями
    for ($i_for_y = 0; $i_for_y < $y; $i_for_y++) {
        for ($i_for_x = 0; $i_for_x < $x; $i_for_x++) {
            $array_sheet[$i_for_y][$i_for_x] = 0;
        }
    }
    //Создание массива бомб
    $my_array_mines = [];
    $i_for_mines = 0;
    $x_y = $x * $y;

    while (intdiv($x_y, 16) * 3 > $i_for_mines) {
        $buffer_number = rand(0, ($x_y - 1));
        if ((in_array($buffer_number, $my_array_mines)) == false) {
            $my_array_mines[$i_for_mines] = $buffer_number;
            $i_for_mines++;
        }
    }

    //Накладываем массив бомб на нулевой массив
    for ($yy = 0, $i_for_mines = 0; $yy < $y; $yy++) {

        for ($xx = 0; $xx < $x; $xx++, $i_for_mines++) {

            $a = in_array($i_for_mines, $my_array_mines);
            if ($a == true) {
                $array_sheet[$yy][$xx] = "*";
            }

        }
    }

    //Создаем доп элементы массива для подсчета коэф.
    $array_sheet[-1] = range(0, $x);
    $array_sheet[-1][-1] = 0;
    $array_sheet[$y] = range(0, $x);
    $array_sheet[$y][-1] = 0;

    foreach ($array_sheet as &$item) {
        $item[-1] = 0;
        $item[$x] = 0;
    }

    //Добавляем вокруг каждой бомбы 1
    for ($yy = 0; $yy < $y; $yy++) {
        for ($xx = 0; $xx < $x; $xx++) {
            if ($array_sheet[$yy][$xx] === "*") {
                $array_sheet[$yy - 1][$xx - 1]++;
                $array_sheet[$yy - 1][$xx]++;
                $array_sheet[$yy - 1][$xx + 1]++;
                $array_sheet[$yy][$xx - 1]++;
                $array_sheet[$yy][$xx + 1]++;
                $array_sheet[$yy + 1][$xx - 1]++;
                $array_sheet[$yy + 1][$xx]++;
                $array_sheet[$yy + 1][$xx + 1]++;
            }
        }
    }
    //Убираем доп элементы массива после подсчета коэф.
    unset($array_sheet[-1]);
    unset($array_sheet[-1][-1]);
    unset($array_sheet[$y]);
    unset($array_sheet[$y][-1]);
    foreach ($array_sheet as &$item) {
        unset($item[-1]);
        unset($item[$x]);
    }
    return $array_sheet;
}

// added optional parameter $visibleArray for the case when we need to show the whole array
function nice_output($bombArray, $visibleArray = false)
{
    foreach ($bombArray as $keyRow => $row) {
        echo str_repeat("+---", count($row)) . "+\n";
        foreach ($row as $keyCol => $item) {
            $cellOutput = ' ';
            $isCellVisible = $visibleArray?$visibleArray[$keyRow][$keyCol]:true;    // if there is a visible_array - check how to display, if not - show the cell
            if (!$isCellVisible) {
                $cellOutput = '?';
            } else {
                if ($item === '*') {
                    $cellOutput = RED . '*';
                } elseif (!$item) {
                    $cellOutput = DGRAY . ' ';
                } else {
                    $cellOutput = LGREEN . $item;
                }
            }
            $str = "| " . $cellOutput . WHITE . " ";
            echo $str;
        }
        echo WHITE . "|\n";
    }
    echo str_repeat("+---", count($row)) . "+\n";
}

function createVisibleArray($row, $col)                             // creation of visible array
{
    $field = [];
    foreach (range(1, $row) as $value) {
        $field[] = array_fill(0, $col, false);

    }
    return $field;
}

function makeAMove($bombArray, $visibleArray, $x = NULL, $y = NULL) // function to check moves
{
    if ($x === NULL || $y === NULL) {
        $handle = fopen("php://stdin", "r");
        echo "Введите x: \n";
        $x = (int)fgets($handle) - 1;
        echo "Введите y: \n";
        $y = (int)fgets($handle) - 1;
    }
    if (!isset($visibleArray[$x][$y]) || $visibleArray[$x][$y]) {       //if entered value os greater than dimensions - redraw the erray.
        return $visibleArray;
    }
    $cellValue = $bombArray[$x][$y];
    $visibleArray[$x][$y] = true;

    //check if cell is a bomb
    if ($cellValue === '*') {
        nice_output($bombArray);
        echo 'GAME OVER';
        die;
    }
    //check if you are a winner
    $bombCount = 0;
    foreach ($bombArray as $row) {
        foreach ($row as $col) {
            if($col === '*') {
                $bombCount++;
            }
        }
    }
    $notVisibleCount = 0;                   // check how many cells are not open
    foreach ($visibleArray as $row) {
        foreach ($row as $col) {
            if(!$col) {
                $notVisibleCount++;
            }
        }
    }
    if ($bombCount == $notVisibleCount){    // if number of bombs = not opened cells - you won
        nice_output($bombArray);
        die;
    }
    //check if cell is 0
    if (!$cellValue) {
        $visibleArray = makeAMove($bombArray, $visibleArray, $x - 1, $y - 1);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x - 1, $y);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x - 1, $y + 1);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x, $y - 1);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x, $y);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x, $y + 1);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x + 1, $y - 1);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x + 1, $y);
        $visibleArray = makeAMove($bombArray, $visibleArray, $x + 1, $y + 1);
    }
    return $visibleArray;
}

$visibleArray = createVisibleArray($rows, $cols);       //initial state of field to be shown to the user: ALL FALSE
//var_dump($visibleArray);
$bombArray = create_array($rows, $cols);                //hiden state of array: with all data
var_dump($bombArray);
nice_output($bombArray, $visibleArray);
$gameOver = false;
while (!$gameOver) {
    $visibleArray = makeAMove($bombArray, $visibleArray);
    nice_output($bombArray, $visibleArray);
}

