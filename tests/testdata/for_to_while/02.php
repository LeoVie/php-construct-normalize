<?php

$array = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
for ($i = 0; $i < count($array); $i++) {
    for ($j = 0; $j < count($array[$i]); $j++) {
        print($array[$i][$j]);
    }
}
