<?php 

class PodcastDownloadService {

    public function process($GET) {
        $podcastDownload = null;
        if(isset($GET['format_id'])) {
            $podcastDownload = new PodcastDownloadLectopia();
        } else if (isset($GET['echo360_id'])) {
            $podcastDownload = new PodcastDownloadEcho360();
        } else if (isset($GET['mid'])) {
            $podcastDownload = new PodcastDownloadDefault();
        }
        
        if(! is_null($podcastDownload) && $podcastDownload instanceof PodcastDownloadAbstract) {
            return $podcastDownload->process($GET);
        }
        exit;
    }
    
    
}