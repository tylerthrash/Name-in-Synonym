<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="styles/main_style.css" type="text/css"> 
	<title>Final Project</title>
</head>
<body>
 	<?php
 		require('create_puzzle.php');
	?> 
  <h2>Final Project</h2>
  <h3>Team: DOLPHIN</h3>
  <h3>Dennis Lee, Gary Webb, Prashant Shrestha, Tyler Thrash</h3>
  <br><br><br>
  <div class="main-container">
  <div class="header">
    <a href="./index.php"><img class="logo" src="./pic/logo.png"></img></a>
    <div class="imageDiv">
	  <a href="./list_puzzles.php"><input class="headerButton" type="image" src="./pic/list.png"></a>
	  <a href="./add_puzzle.php"><input class="headerButton" type="image" src="./pic/addPuzzle.png"></a>
      <a href="./addWordPair.php"><input class="headerButton" type="image" src="./pic/addWord.png"></a>
      <a href="./login.php"><input class="headerButton" type="image" src="./pic/login.png"></a>
    </div>
    <div class="divTitle"><font class="font">Name in Synonyms</font></div>
    <br>
  </div>
  <div id="pop_up_fail" class="container pop_up" style="display:none">
		<div class="pop_up_background">
			
			<img class="pop_up_img_fail" src="pic/info_circle.png">
			<div class="pop_up_text">Incorrect! <br>Try Again!</div>
			<button class="pop_up_button" onclick="toggle_display('pop_up_fail')">OK</button>
		</div>
  </div>
  <?php
		$sqlUpdate ="";
		if (isset($_GET['puzzleName'])) {
			$nameEntered = validate_input($_GET['puzzleName']);
			echo create_puzzle_table(strlen($nameEntered), $nameEntered, "change_puzzle.php?");	
		}
		else if ($_SERVER["REQUEST_METHOD"] == "POST"){
			if (isset($_POST["word"])) {
				$name = $size = "";
				$list = array();
				if(empty($_POST["word"]) && empty($_POST["size"])) {
					//should not happen
				}
				else {
					$name = strtolower(validate_input($_POST["word"]));
					$size = validate_input($_POST["size"]);
					$errorflag = FALSE;
					for ($j = 0; $j < $size; $j++) {
						$tempWord = "word". $j;
						$tempClue = "clue" . $j;
						if(empty($_POST[$tempWord]) && empty($_POST[$tempClue])) {
							// left one of the Synonym or Clues empty
							// let user know of error
							if ($errorflag == FALSE) {
								echo create_puzzle_table($size, $name, "change_puzzle.php?");
								echo display_error("Please give every synonym and clue a value!");
								$errorflag = TRUE;
							}
						}
						else {
							// valid input
							$word1 = strtolower(validate_input($_POST[$tempWord]));
							$word2 = strtolower(validate_input($_POST[$tempClue]));
							//echo "words: " . $word1. $word2;
							$char = substr($name, $j, 1);
							//echo "char: " . $char;
							$index = strpos($word1, $char);
							//echo "index: " . $index;
							if ($index === false){
								echo	create_puzzle_table($size, $name, "change_puzzle.php?");
								echo display_error("Char not found in word!");
								return;
							} else {
								// add to words
								insertIntoWords($word1, $word2);
								
								// add to char
								insertIntoCharacters(getMaxWordId($word1));
								insertIntoCharacters(getMaxWordId($word2));
								
								$db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
								$sqlUpdate = 'UPDATE puzzle_words SET word_id=\'' . getMaxWordId($word1) . '\' WHERE puzzle_id=\'' . getMaxPuzzleId($name) . '\' AND position_inName=\'' . $j . '\';';
								$result =  $db->query($sqlUpdate);
								//$num_rows = $result->num_rows;
							}
						}
						
					}
				}
				if (strcmp($sqlUpdate, "") == 0) {
					
				} else {
					echo createHeader(validate_input($_POST["word"]));
					echo '<table class="main-tables" id="puzzle_table"><tr><th>Clue</th><th>Synonym</th></tr>';
					puzzleAddedTable();
					echo "</table>";
					echo createFooter();
				}
			}
		}
  ?>
	</div>
</body>
<!-- <script type="text/javascript" src="javascript/puzzle.js"></script> -->
</html>