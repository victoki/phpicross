<?php

// These are the solution keys for the puzzles - in other words, the data for the puzzles.
//A value of 1 is unfilled.
//A value of 2 is a filled square.

//Fish
$fish = array(
	1, 1, 1, 1, 1, 2, 2, 1, 1, 1,
	1, 1, 1, 1, 2, 2, 1, 1, 1, 2,
	1, 1, 1, 2, 2, 1, 1, 1, 2, 1,
	1, 1, 2, 2, 2, 2, 2, 1, 2, 1,
	1, 2, 2, 2, 1, 1, 2, 2, 2, 1,
	2, 2, 1, 2, 1, 2, 2, 2, 2, 1,
	2, 2, 2, 2, 2, 2, 1, 2, 2, 1,
	1, 2, 2, 2, 1, 2, 2, 1, 2, 1,
	1, 1, 1, 2, 2, 1, 2, 1, 1, 2,
	1, 1, 1, 1, 2, 1, 1, 1, 1, 1);

$fishRows = array('2', '2, 1', '2, 1', '5, 1', '3, 3', '2, 1, 4', '6, 2', '3, 2, 1', '2, 1, 1', '1');

$fishCols = array('2', '4', '2<br>2', '7', '3<br>1<br>2', '2<br>1<br>3', '1<br>3<br>2', '3', '6', '1<br>1');

//Clock
$clock = array(
	1, 1, 2, 2, 2, 2, 2, 2, 1, 1,
	1, 2, 1, 1, 1, 1, 1, 1, 2, 1,
	2, 1, 1, 2, 2, 2, 2, 1, 1, 2,
	2, 1, 2, 2, 1, 2, 2, 2, 1, 2,
	2, 1, 2, 2, 1, 2, 2, 2, 1, 2,
	2, 1, 2, 2, 1, 1, 1, 2, 1, 2,
	2, 1, 2, 2, 2, 2, 2, 2, 1, 2,
	2, 1, 1, 2, 2, 2, 2, 1, 1, 2,
	1, 2, 1, 1, 1, 1, 1, 1, 2, 1,
	1, 1, 2, 2, 2, 2, 2, 2, 1, 1);

$clockRows = array('6', '1, 1', '1, 4, 1', '1, 2, 3, 1', '1, 2, 3, 1', '1, 2, 1, 1', '1, 6, 1', '1, 4, 1', '1, 1', '6');

$clockCols = array('6', '1<br>1', '1<br>4<br>1', '1<br>6<br>1', '1<br>1<br>2<br>1', '1<br>3<br>2<br>1', '1<br>3<br>2<br>1', '1<br>4<br>1', '1<br>1', '6');

//Bird
$bird = array(
	1, 1, 1, 1, 2, 2, 2, 1, 1, 1,
	1, 1, 1, 2, 1, 1, 2, 2, 1, 1,
	2, 2, 2, 1, 2, 1, 2, 2, 1, 1,
	1, 2, 2, 1, 1, 1, 2, 2, 1, 1,
	1, 1, 1, 2, 2, 2, 2, 1, 1, 1,
	1, 1, 2, 1, 1, 2, 2, 1, 1, 1,
	1, 1, 2, 1, 2, 2, 2, 2, 1, 1,
	1, 1, 2, 1, 2, 2, 2, 2, 1, 2,
	1, 1, 1, 2, 1, 2, 2, 2, 2, 2,
	1, 1, 1, 2, 2, 1, 2, 2, 2, 1);

$birdRows = array('3', '1, 2', '3, 1, 2', '2, 2', '4', '1, 2', '1, 4', '1, 4, 1', '1, 5', '2, 3');

$birdCols = array('1', '2', '2<br>3', '1<br>1<br>2', '1<br>1<br>1<br>2<br>1', '1<br>5', '10', '3<br>4', '2', '2');

//Box (Used for development/debug only)
$box = array(
	2, 2, 2, 2, 2, 2, 2, 2, 2, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 1, 1, 1, 1, 1, 1, 1, 1, 2,
	2, 2, 2, 2, 2, 2, 2, 2, 2, 2);

$boxRows = array('10', '1, 1', '1, 1', '1, 1', '1, 1', '1, 1', '1, 1', '1, 1', '1, 1', '10');

$boxCols = array('10', '1<br>1', '1<br>1', '1<br>1', '1<br>1', '1<br>1', '1<br>1', '1<br>1', '1<br>1', '10');

?>

