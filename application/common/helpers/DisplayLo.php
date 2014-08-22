<?php

class Zend_View_Helper_DisplayLo {
	protected $_view;
	
	function setView($view) {
        $this->_view = $view;
    }
    
    function displayLo($cur_ta_id, $cur_lo, $edit_link, $resource_url=null) {
    	$url_viewlo = $this->_view->url(array("controller"=>"learningobjective", "action"=>"view", "id"=>$cur_lo['auto_id']), null, true);
    	$url_deletelo = $this->_view->url(array("controller"=>"lotalinkage", "action"=>"delete", "taid"=>$cur_ta_id, "loid"=>$cur_lo['auto_id'], "type"=>"ta"), null, true);
    	
        echo '<div style="position: relative" class="lo">';
        echo $cur_lo['lo'];
        echo '<span class="locontrol">';
		echo '<span class="lo_resourceshowbutton">';
		echo '<span style="color: rgb(48, 111, 223); text-decoration:underline; cursor:pointer" onclick="$(\'#lo_resourceholder_'.$cur_lo['loid'].'\').toggle(); showloresources('.$cur_lo['loid'].');">Show student resources</span> | ';
		echo '</span>';
        echo "<a href=\"{$url_viewlo}\">View</a>";
        if ($edit_link) {
        	echo " | <a href=\"{$url_deletelo}\">Unlink</a>";
        }
        echo '</span>';
        ?>
        <?php if($resource_url != null):?>
        <div id="lo_resourceholder_<?php echo $cur_lo['loid']?>" class="lo_resourceholder"><b>Student Resources <small>(sort by 
        <a href="#" onclick="$('#lo_resources_<?php echo $cur_lo['loid']?>').load('<?php echo $resource_url; ?>/sortby/rating');">rating</a>
        |
        <a href="#" onclick="$('#lo_resources_<?php echo $cur_lo['loid']?>').load('<?php echo $resource_url; ?>/sortby/date');">date</a>
        )</small>
       </b>
        <div id="lo_resources_<?php echo $cur_lo['loid']?>" class="lo_resources"></div></div> <?php endif;?><?php 
        echo '</div>';
    }
}