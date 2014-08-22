<?php
/** MeSH related functionalities appear here */

class MeshService {
	public static $rootCategories = Array(
		"A" => "Anatomy",
		"B" => "Organisms",
		"C" => "Diseases",
		"D" => "Chemicals and Drugs",
		"E" => "Analytical, Diagnostic and Therapeutic Techniques and Equipment",
		"F" => "Psychiatry and Psychology",
		"G" => "Phenomena and Processes",
		"H" => "Disciplines and Occupations",
		"I" => "Anthropology, Education, Sociology and Social Phenomena",
		"J" => "Technology, Industry, Agriculture",
		"K" => "Humanities",
		"L" => "Information Science",
		"M" => "Named Groups",
		"N" => "Health Care",
		"V" => "Publication Characteristics",
		"Z" => "Geographic Locations"
	);
	
	/** Auto generate keywords base on the input text, and return those above the cut off value set in config file */
	public static function autoGenerateKeywords($input) {
		//get Mesh crawler to analyze the input
		$config = Zend_Registry::get('config');
		$cutoff = (int)($config->mesh->cutoff);
		$output = shell_exec("echo \"$input\" | ".$config->mesh->path." -I");
		
		// process the output, see sample output below
/*Processing 00000000.tx.1: lung cancer

Phrase: "lung cancer"
Meta Candidates (8):
  1000 C0242379:Lung Cancer (Malignant neoplasm of lung) [Neoplastic Process]
  1000 C0684249:Lung Cancer (Carcinoma of lung) [Neoplastic Process]
   861 C0006826:Cancer (Malignant Neoplasms) [Neoplastic Process]
   861 C0024109:Lung [Body Part, Organ, or Organ Component]
   861 C0998265:Cancer (Cancer Genus) [Invertebrate]
   861 C1278908:Lung (Entire lung) [Body Part, Organ, or Organ Component]
   861 C1306459:Cancer (Primary malignant neoplasm) [Neoplastic Process]
   768 C0032285:Pneumonia [Disease or Syndrome]
Meta Mapping (1000):
  1000 C0684249:Lung Cancer (Carcinoma of lung) [Neoplastic Process]
Meta Mapping (1000):
  1000 C0242379:Lung Cancer (Malignant neoplasm of lung) [Neoplastic Process]*/

		$result = array();
		$arr = split(PHP_EOL, $output);
		foreach ($arr as $line) {
			$line = trim($line);
			$arr2 = explode(' ', $line);
			$value = array_shift($arr2);
			if ($value >= $cutoff) {
				$keywords = array();
				foreach ($arr2 as $k => $v) {
					if ($k == 0) {
						$first_arr = explode(':', $v);
						$keywords[] = strtolower($first_arr[1]);
						continue;
					}
					if (strpos($v, '(') === 0 || strpos($v, '[') === 0) {
						break;
					}
					$keywords[] = strtolower($v);
				}
				$result[] = join(' ', $keywords);
			}
		}
		$result = array_unique($result);
		
		$descriptorFinder = new Descriptors();
		$descriptor_arr = $descriptorFinder->getHeadingsAsAssociativeArray();
		
		//remove non-mesh keywords from crawler
		$newResult = array();
		foreach ($result as $v) {
			if (array_key_exists($v, $descriptor_arr)) {
				$newResult[] = $descriptor_arr[$v];
			}
		}
		return $newResult;
	}
	
	/** batch-processing all released learning objectives in the datebase to auto generate keywords.
	 *  WARNING: it will overwrite the existing keywords.
	 */
	public static function batchProcessAllLos() {
		$config = Zend_Registry::get('config');
		
        $descriptorFinder = new Descriptors();
        $descriptor_arr = $descriptorFinder->getHeadingsAsAssociativeArray();
        
        $loFinder = new LearningObjectives();
        $linkFinder = new LinkageLoTas();
        
        $los = $loFinder->getReleasedLos();
        foreach ($los as $lo) {
            $discipline = $lo->allDisciplineNames;
            $text = $lo->allDisciplineNames . '. ' . strip_tags($lo->lo). '.';
            //Zend_Registry::get('logger')->info(__METHOD__. ": - CRAWLER TEXT: $text");

            $output = shell_exec("echo \"$text\" | ".$config->mesh->path." -I");
            $arr = split(PHP_EOL, $output);
            $result = array();
            foreach ($arr as $line) {
                $line = trim($line);
                $arr2 = explode(' ', $line);
                $value = array_shift($arr2);
                if ($value >= (int)($config->mesh->cutoff)) {
                    $keywords = array();
                    foreach ($arr2 as $k => $v) {
                        if ($k == 0) {
                            $first_arr = explode(':', $v);
                            $keywords[] = strtolower($first_arr[1]);
                            continue;
                        }
                        if (strpos($v, '(') === 0 || strpos($v, '[') === 0)
                            break;
                        $keywords[] = strtolower($v);
                    }
                    $result[] = join(' ', $keywords);
                }
            }
            $result = array_unique($result);
	
	        //remove non-mesh keywords from crawler
	        $newResult = array();
	        foreach ($result as $v) {
	            if (array_key_exists($v, $descriptor_arr))
	                $newResult[] = $descriptor_arr[$v];
	        }
	        if (count($newResult) > 0)	{
	            $lo->keywords = join("|", $newResult);
	            $lo->save();
            }
        }
        return count($los);
	}
}