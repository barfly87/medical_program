--Changing ta name from 'Theme Session' to 'Practical'
update lk_activitytype set name='Practical' where auto_id = 3;
--Changing ta name from 'Learning Topic' to 'Essential readings'
update lk_activitytype set name='Essential readings' where auto_id = 5;
-- Add new types of ta's
insert into lk_activitytype values(default, 'Seminar',29);
insert into lk_activitytype values(default, 'Tutorial',30);
insert into lk_activitytype values(default, 'Q&A session',31);
insert into lk_activitytype values(default, 'Assessment',32);


-- add collaboration functionality into student resources table
alter table studentresourcelink add collaborative varchar(8);

--add summary student resource type
insert into lk_studentresourcecategories (auto_id,name, seq_no, description) values (6, 'Summary',6,'Shared summary of this Learning Objective');

--add the ability to have private student resources - COMPASSSOCNET-139
alter table studentresourcelink add private varchar(8);
update studentresourcelink set private='false' where private is null;

--add a download counter for student resources - COMPASSSOCNET-134
alter table studentresourcelink add downloadcount numeric default 0;
create index studentresourcelink_mid on studentresourcelink(mid);

--track history of edited student resources
alter table studentresourcelink add archived numeric default 0;
alter table studentresourcelink add previous_version_id int;