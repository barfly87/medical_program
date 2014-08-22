<?php
class DynamicGuideService {
	public function getEssentialReadingsInBlock($block_no, $order) {
		$identity = Zend_Auth::getInstance()->getIdentity();
		if (UserAcl::isStudent()) {
			list($block, $week) = explode('.', $identity->releasedpbl);
			if ($identity->stage < 3) {
				if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
					//student does not have access to this block yet
					if ($block_no > $block) {
						return null;
					}
				} else {
					if ($block == 1 && $block_no > 5) {
						return null;
					}
				}
			}
		}
		
		//get all essential readings for block $block_no		
		$taFinder = new TeachingActivities();
		return $taFinder->getTaByBlock($block_no, "Essential readings", $order);
	}
	
	public function getEssentialReadingsInBlockByWeek($block_no) {
		$identity = Zend_Auth::getInstance()->getIdentity();
		if (UserAcl::isStudent()) {
			list($block, $week) = explode('.', $identity->releasedpbl);
			if ($identity->stage < 3) {
				if (isset(Zend_Registry::get('config')->event_wsdl_uri)) {
					//student does not have access to this block yet
					if ($block_no > $block) {
						return null;
					}
				} else {
					if ($block == 1 && $block_no > 5) {
						return null;
					}
				}
			}
		}
		
		//get all essential readings for block $block_no		
		$taFinder = new TeachingActivities();
		return $taFinder->getTaInBlockByWeek($block_no, "Essential readings");
	}
	
	public function getProceduralSkillsInStage($stage) {
		$taFinder = new TeachingActivities();
		return $taFinder->getProceduralSkillInStage($stage);
	}
	
	public function getProceduralSkillsInBlock($block) {
		$taFinder = new TeachingActivities();
		return $taFinder->getProceduralSkillInBlock($block);
	}
	
	public function getAllTasInBlock($block) {
		$taFinder = new TeachingActivities();
		return $taFinder->getTaByBlock($block, NULL, array('block_week', 'type', 'sequence_num'));
	}
	
	public function getPtDrTutorialInBlock($block) {
		$taFinder = new TeachingActivities();
		return $taFinder->getTaByBlock($block, 'Pt-Dr tutorial', array('block_week', 'sequence_num'));
	}
}