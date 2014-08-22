<?php
/** Service that draws different kind of charts */
Class ChartService {
	
	/** Draw a pie chart displaying the number of learning objectives in different stages*/
	public static function pieLoChart() {
		require_once('php-ofc-library/open-flash-chart.php');
		$title = new title("Learning Objective Distribution among Stages");
		
		$pie = new pie();
		$pie->set_alpha(0.6);
		$pie->set_start_angle(35);
		$pie->add_animation(new pie_fade());
		$pie->add_animation(new pie_bounce(4));
		
		$statusFinder = new Status();
		$status_id = $statusFinder->getIdForStatus(Status::$RELEASED);
		
		$db = Zend_Registry::get("db");
		$select = $db->select()
					->from(array('t' => 'teachingactivity'), array('stage' => 'stage', 'lo_count' => 'count(*)'))
					->join(array('lk' => 'link_lo_ta'), 't.auto_id = lk.ta_id', array())
					->join(array('l' => 'learningobjective'), 'lk.lo_id = l.auto_id', array())
					->where("lk.status = ?", $status_id)
					->group("stage");
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
		
		$stageFinder = new Stages();
		$pie_values = array();
		foreach ($results as $row) {
			$pie_values[] = new pie_value((int)$row['lo_count'], 'Stage '.$stageFinder->getStageName($row['stage']));
		}
		$pie->set_tooltip('#val# of #total#<br>#percent# of 100%');
		$pie->set_colours( array('#1C9E05','#D4FA00','#9E1176','#FF368D','#454545'));
		$pie->set_values($pie_values);
		
		$chart = new open_flash_chart();
		$chart->set_title( $title );
		$chart->add_element( $pie );	
		
		$chart->x_axis = null;
		return $chart;
	}
	
	/** Draw a bar chart which has all parent disciplines as x-axis and 
	 *  the number of learning objectives as main discipline and all disciplines as y-axis
	 */
	public static function barLoDiscChart() {
		require_once('php-ofc-library/open-flash-chart.php');
		$title = new title("Learning Objective Vs. Disipline");
		
		$discFinder = new Discipline();
		$disc_result = $discFinder->fetchAll("name != '' and parent_id = 0", 'name ASC');
		$count_arr = array();
		foreach ($disc_result as $disc) {
			$count_arr[$disc->name] = 0;
		}
		
		$db = Zend_Registry::get("db");
		$select = $db->select()
					->from(array('d' => 'lk_discipline'), array('name'))
					->join(array('l' => 'learningobjective'), 'd.auto_id = l.discipline1', array('lo_count' => 'COUNT(*)'))
					->where("name != '' AND d.parent_id = 0")
					->group('d.name');
		$stmt = $db->query($select);
		$results = $stmt->fetchAll();
		
		foreach ($results as $row) {
			$count_arr[$row['name']] = (int)$row['lo_count'];
		}
		
		$bar = new bar_glass();
		$bar->colour('#0066CC');
		$bar->key('Main', 12);
		$bar->set_values(array_values($count_arr));
		
		$select2 = $db->select()
					->from(array('d' => 'lk_discipline'), array('name'))
					->join(array('l' => 'learningobjective'), 'd.auto_id = l.discipline2', array('lo_count' => 'COUNT(*)'))
					->where("name != '' AND d.parent_id = 0")
					->group('d.name');
		$stmt2 = $db->query($select2);
		$results2 = $stmt2->fetchAll();
		
    	foreach ($results2 as $row) {
			$count_arr[$row['name']] += (int)$row['lo_count'];
		}
		
    	$select3 = $db->select()
					->from(array('d' => 'lk_discipline'), array('name'))
					->join(array('l' => 'learningobjective'), 'd.auto_id = l.discipline3', array('lo_count' => 'COUNT(*)'))
					->where("name != '' AND d.parent_id = 0")
					->group('d.name');
		$stmt3 = $db->query($select3);
		$results3 = $stmt3->fetchAll();
		
    	foreach ($results3 as $row) {
			$count_arr[$row['name']] += (int)$row['lo_count'];
		}

		$bar2 = new bar_glass();
		$bar2->colour('#9933CC');
		$bar2->key('All', 12);
		$bar2->set_values(array_values($count_arr));

		$x_labels = new x_axis_labels();
		$x_labels->rotate(45);
		$x_labels->set_labels(array_keys($count_arr));
		$x = new x_axis();
		$x->set_labels($x_labels);		
		
		$y = new y_axis();
		$y_max = ceil(max(array_values($count_arr)) / 100) * 100 + 50;
		$y->set_range(0, $y_max, 50);

		$chart = new open_flash_chart();		
		$chart->set_title($title);
		$chart->add_element($bar);
		$chart->add_element($bar2);
		$chart->set_x_axis($x);
		$chart->set_y_axis($y);
		
		return $chart;
	}
}