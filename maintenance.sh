#!/bin/sh 

BASE_DIR="/srv/www/smp/htdocs/compass"
LOG="/srv/www/smp/htdocs/compass/var/log/log.txt";
BASE_PATH="http://smp.sydney.edu.au/compass/admin/maintenance?token=Xdpe934JLTRsZd2&service"
LOGSTRING=""
STATUS=1
date=`/bin/date +%Y-%m-%d`;
time=`/bin/date +%H_%M`;

if [ $# -gt 0 ];then 
    LOGSTRING="$LOGSTRING\nMAINTENANCE : Maintenance service requested is : $1,  $date - $time";
    case $1 in
    'mediabank_collections')            
        SERVICE_PATH="wget '$BASE_PATH=mediabank_collections' -O - -q";
        LOGSTRING="$LOGSTRING\nMAINTENANCE : COMMAND : $SERVICE_PATH";
        result=`eval $SERVICE_PATH`;        
        if [ "$result" = "1" ]; then
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : SUCCESS: $result";
            STATUS=0;
        else
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : ERROR: $result";
            STATUS=1;
        fi;
    ;;
    'backup_search_index')            
        result=`mkdir -p $BASE_DIR/backup/search_index/$date/$time; cp -r $BASE_DIR/var/search_index $BASE_DIR/backup/search_index/$date/$time`;
    	find $BASE_DIR/backup/search_index/ -maxdepth 1 -type d -mtime +5 -name "20*" -exec rm -rf {} \; >> $BASE_DIR/backup/purge.log 2>&1
    	if [ "$result" = "1" ]; then
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : SUCCESS: $result";
            STATUS=0;
        else
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : ERROR: $result";
            STATUS=1;
        fi;
    ;;
    'remove_stage3_stale_cache')
        find $BASE_DIR/var/cache/stage3 -not -type d -mtime +1 -exec rm -rf {} \; > /dev/null 2>&1
        find $BASE_DIR/var/cache/stage3 -max-depth 3 -type d -empty -mtime +1 -exec rm -rf {} \; > /dev/null 2>&1
    ;;
    'optimize_lucene_index')            
        SERVICE_PATH="wget '$BASE_PATH=optimize_lucene_index' -O - -q";
        LOGSTRING="$LOGSTRING\nMAINTENANCE : COMMAND : $SERVICE_PATH";
        result=`eval $SERVICE_PATH`;        
        if [ "$result" = "1" ]; then
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : SUCCESS: $result";
            STATUS=0;
        else
            LOGSTRING="$LOGSTRING\nMAINTENANCE: RESULT : ERROR: $result";
            STATUS=1;
        fi;
    ;;
    esac               
else
    LOGSTRING="$LOGSTRING \n MAINTENANCE: ERROR : Maintenance service was requested with no service info";
fi

if [ -f $LOG ]; then
    echo -e "$LOGSTRING" >> $LOG;
fi       

exit $STATUS;
        
