<?php
	define("FILE_NAME", "d2009.bin");
	$id = '';
	$name = '';
	$tree_num = array();
	$synonyms = array();
	$found = FALSE;
	$fp = fopen(FILE_NAME, "r");
	echo "BEGIN;", PHP_EOL;
	while (!feof($fp)) {
        $line = trim(fgets($fp));
		if (trim($line) === '' && $found == TRUE) {
			echo "INSERT INTO descriptor (auto_id, uid, headingtext, treenumbers, synonyms) VALUES (DEFAULT, '$id', '". str_replace("'", "''",$name)."', '".join(',', $tree_num)."', '".str_replace("'", "''",join(' ', $synonyms))."');".PHP_EOL;
			$id = '';
			$name = '';
			$tree_num = array();
			$synonyms = array();
			$found = FALSE;
		}
		else if (strpos($line, 'MH = ') === 0)
			$name = substr($line, strlen('MH = '));
		else if (strpos($line, 'PRINT ENTRY = ') === 0) {
			$str_arr = explode('|', trim(substr($line, strlen('PRINT ENTRY = '))));
			if (count($str_arr) > 1)
				$synonyms[] = $str_arr[0];
		}
		else if (strpos($line, 'ENTRY = ') === 0) {
			$str_arr = explode('|', trim(substr($line, strlen('ENTRY = '))));
			if (count($str_arr) > 1)
				$synonyms[] = $str_arr[0];
		}
		else if (strpos($line, 'UI = ') === 0) {
			$id = substr($line, strlen('UI = '));
			$found = TRUE;
		}
		else if (strpos($line, 'MN = ') === 0)
			$tree_num[] = substr($line, strlen('MN = '));
    }
	if ($found == TRUE)
		echo "INSERT INTO descriptor (auto_id, uid, headingtext, treenumbers, synonyms) VALUES (DEFAULT, '$id', '". str_replace("'", "''",$name)."', '".join(',', $tree_num)."', '".str_replace("'", "''",join(' ', $synonyms))."');".PHP_EOL;
	echo "COMMIT;";
	fclose($fp);
?>
