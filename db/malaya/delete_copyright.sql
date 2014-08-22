-- delete all the resources from collections echo360, medvid, cmsdocs-smp, stage3medvid
-- create a history before deleting

insert into lk_resource_history (lk_resource_id, type, type_id, resource_type, resource_id, resource_title, uid, timestamp, action) select auto_id, type, type_id, resource_type, resource_id,'University of Sydney Third Party Copyright', 'usyd','2013-08-11 11:00:00','delete' from lk_resource where resource_id like '%|medvid|%' or resource_id like '%|echo360|%' or resource_id like '%|cmsdocs-smp|%' or resource_id like '%|stage3medvid|%';
delete from lk_resource where resource_id like '%|medvid|%' or resource_id like '%|echo360|%' or resource_id like '%|cmsdocs-smp|%' or resource_id like '%|stage3medvid|%';

-- delete all the resources which are not tagged university of sydney
insert into lk_resource_history (lk_resource_id, type, type_id, resource_type, resource_id, resource_title, uid, timestamp, action) select auto_id, type, type_id, resource_type, resource_id,'University of Sydney Third Party Copyright', 'usyd','2013-08-11 11:00:00','delete' from lk_resource where CAST(coalesce(replace(resource_id,'http://smp.sydney.edu.au/mediabank/|compassresources|','')) AS integer)  in (select id from compassresource where copyright not in ('University of Sydney', 'University of Sydney (Unchecked)','Copyright The University of Sydney','')) and resource_id like '%|compassresources|%';
delete from lk_resource where CAST(coalesce(replace(resource_id,'http://smp.sydney.edu.au/mediabank/|compassresources|','')) AS integer)  in (select id from compassresource where copyright not in ('University of Sydney', 'University of Sydney (Unchecked)','Copyright The University of Sydney','')) and resource_id like '%|compassresources|%';
