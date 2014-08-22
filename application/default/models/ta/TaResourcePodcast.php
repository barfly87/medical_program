<?php
class TaResourcePodcast extends TaResourceAbstract {
    
    public function __construct($taId) {
        $this->startProcess($taId);
    }
    
    public function getResources() {
        $ta = $this->getTeachingActivity();
        $podcastResourceService = new PodcastResourceService();
        return $podcastResourceService->process($this->resources, $ta);
    }
    
}