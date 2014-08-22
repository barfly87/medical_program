
-----------------------------------------------------------------link_lo_ta
delete from link_lo_ta where ta_id in (select distinct auto_id from teachingactivity where owner != 1) or lo_id in (select distinct auto_id from learningobjective where owner != 1);
delete from link_lo_ta where status != 4;

-----------------------------------------------------------------link_lo_ta_history 
delete from link_lo_ta_history;
alter sequence link_lo_ta_history_auto_id_seq restart with 1;

-----------------------------------------------------------------teachingactivity
-- select count(*) from teachingactivity where auto_id not in (select distinct(ta_id) from link_lo_ta where status = 4);
-- This one needs to restart there sequence with 15000
-- make sure you make the version set to 1

delete from teachingactivity where auto_id not in (select distinct(ta_id) from link_lo_ta);
alter table teachingactivity drop column excelid;
update teachingactivity set version = 1, principal_teacher = '', current_teacher = '', created_by = 'USYD Staff', approved_by = '' , notes = '', reviewed_by = '';
alter sequence teachingactivity_auto_id_seq restart with 50000;

-- select count(*) from link_lo_ta where ta_id not in (select auto_id from teachingactivity);
--check
delete from link_lo_ta where ta_id not in (select auto_id from teachingactivity);

-----------------------------------------------------------------learningobjective   
delete from learningobjective where auto_id not in (select distinct(lo_id) from link_lo_ta);
alter table learningobjective drop column excelid;
update learningobjective set version = 1, created_by = 'USYD Staff', approved_by = '' , notes = '';
alter sequence learningobjective_auto_id_seq restart with 50000;

--check
delete from link_lo_ta where lo_id not in (select auto_id from learningobjective);

-----------------------------------------------------------------link_lo_domain 
--delete from link_lo_domain where domain_id != 1;
delete from link_lo_domain where lo_id not in (select auto_id from learningobjective);

-----------------------------------------------------------------link_ta_domain   
--delete from link_ta_domain where domain_id != 1;
delete from link_ta_domain where ta_id not in (select auto_id from teachingactivity);

-----------------------------------------------------------------link_lo_review  
delete from link_lo_review where lo_id not in (select auto_id from learningobjective);

-----------------------------------------------------------------lk_resource 

delete from lk_resource where type='ta' and type_id not in (select auto_id from teachingactivity);
delete from lk_resource where type='lo' and type_id not in (select auto_id from learningobjective);

insert into lk_block values(24, 'Language in Medicine');
-----------------------------------------------------------------stage_block_seq 
-- STAGE_BLOCK_SEQ (stage_block_seq)

--                      VALUES (DEFAULT, STAGE,SEQ,BLOCK_ID, YEAR_ID);
-- STAGE_BLOCK_SEQ (stage_block_seq)

-- STAGE_BLOCK_SEQ (stage_block_seq)
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 1', 'Week 1 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 2', 'Week 2 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 3', 'Week 3 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 4', 'Week 4 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 5', 'Week 5 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 6', 'Week 6 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 7', 'Week 7 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 8', 'Week 8 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 9', 'Week 9 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 10', 'Week 10 Description');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 11', 'Week 11 Description');

delete from stage_block_seq;
alter sequence stage_block_seq_auto_id_seq restart with 1;

INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 1, 24, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 2, 2, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 3, 3, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 4, 4, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 5, 5, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 6, 6, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 7, 7, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 8, 8, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 9, 9, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 10, 10, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 11, 11, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 25, 12, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 26, 13, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 24, 14, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 21, 15, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 22, 16, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 27, 17, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 30, 18, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 23, 19, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 28, 20, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 31, 21, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 25, 12, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 26, 13, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 24, 14, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 21, 15, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 22, 16, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 27, 17, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 30, 18, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 23, 19, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 28, 20, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 31, 21, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 29, 22, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 29, 22, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 0, 23, 1);




delete from block_pbl_seq;
alter sequence block_pbl_seq_auto_id_seq restart with 1;

INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 2, 101);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 3, 102);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 4, 103);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 5, 104);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 6, 105);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 7, 106);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 8, 107);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 9, 108);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 10, 109);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 11, 110);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 12, 111);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 6, 6);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 7, 7);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 8, 8);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 9, 9);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 10, 10);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 2, 11);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 3, 12);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 4, 13);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 5, 14);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 6, 15);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 7, 16);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 8, 17);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 9, 18);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 2, 19);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 3, 20);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 4, 21);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 5, 22);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 6, 23);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 7, 24);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 8, 25);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 2, 26);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 3, 27);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 4, 28);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 5, 29);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 6, 30);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 2, 31);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 3, 32);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 4, 33);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 5, 34);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 6, 35);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 7, 36);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 2, 37);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 3, 38);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 4, 39);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 5, 40);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 6, 41);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 7, 42);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 8, 43);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 9, 44);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 10, 45);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 11, 46);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 2, 47);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 3, 48);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 4, 49);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 5, 50);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 6, 51);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 7, 52);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 8, 53);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 9, 54);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 2, 55);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 3, 56);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 4, 57);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 5, 58);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 2, 59);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 3, 60);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 4, 61);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 5, 62);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 6, 63);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 7, 64);
--INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 8, 65);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 2, 66);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 3, 67);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 4, 68);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 5, 69);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 6, 70);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 2, 71);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 3, 72);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 4, 73);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 5, 74);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 6, 75);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 7, 76);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 8, 77);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 9, 78);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 10, 79);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 11, 80);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 12, 81);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 13, 82);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 14, 83);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 15, 84);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 16, 85);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 17, 86);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 18, 87);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 19, 88);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 20, 89);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 21, 90);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 22, 91);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 23, 92);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 24, 93);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 25, 94);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 26, 95);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 27, 96);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 28, 97);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 29, 98);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 2, 71);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 3, 72);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 4, 73);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 5, 74);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 6, 75);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 7, 76);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 8, 77);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 9, 78);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 10, 79);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 11, 80);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 12, 81);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 13, 82);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 14, 83);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 15, 84);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 16, 85);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 17, 86);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 18, 87);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 19, 88);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 20, 89);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 21, 90);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 22, 91);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 23, 92);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 24, 93);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 25, 94);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 26, 95);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 27, 96);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 28, 97);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 22, 29, 98);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 34, 2, 99);



-----------------------------------------------------------------block_pbl_seq   
-- VALUES block_seq_id, week_id, pbl_id


-- blockchair                  
delete from blockchair;
alter sequence blockchair_auto_id_seq restart with 1;

-- clinical_school 
delete from clinical_school;
alter sequence clinical_school_auto_id_seq restart with 1;

-- descriptor
--LOOK UP

--domainadmin                  
delete from domainadmin;
alter sequence domainadmin_auto_id_seq restart with 1;

--evaluate_lecture   
drop table evaluate_lecture;

--evaluate_lecture_v1          
drop table evaluate_lecture_v1;

--evaluate_ta     
delete from evaluate_ta;
alter sequence evaluate_ta_auto_id_seq restart with 1;


--link_lo_assesstype 
--LOOK UP 


--lk_achievement 
--LOOK UP

--lk_activitytype   
--LOOK UP

--lk_assesstype  
--LOOK UP

--lk_block 
--LOOK UP

--lk_blockweek  
--LOOK UP 

--lk_curriculumareas  
--LOOK UP

--lk_curriculumareas_status  
--LOOK UP

--lk_discipline 
--LOOK UP

--lk_domain  
delete from lk_domain where auto_id in (2,3);
alter sequence lk_domain_auto_id_seq restart with 2;

--lk_facilities  
drop table lk_facilities;

--lk_gradattrib  
--LOOK UP

--lk_jmo
--LOOK UP

--lk_loscope
--LOOK UP

--lk_loverb
--LOOK UP

--lk_pbl ;;Keep it same so the future updates would not have clash.
--LOOK UP

--lk_pblroom 
delete from lk_pblroom;
alter sequence lk_pblroom_auto_id_seq restart with 1;

--lk_ratingcategories 
--LOOK UP

--lk_resource_history 
delete from lk_resource_history;
alter sequence lk_resource_history_auto_id_seq restart with 1;
--; Later when compass staff filter out resources that can be put in the history table

--lk_resourcetype  
--LOOK UP

--lk_review 
--LOOK UP

--lk_selectgroup  
drop table lk_selectgroup;

--lk_sequence_num 
--LOOK UP

--lk_site 
drop table lk_site;

--lk_skill  
--LOOK UP

--lk_staffpage
--LOOK UP

--lk_stafftype 
--LOOK UP

--lk_stage
--Need to add extra stage since umsc curriculum is spread across 5 years
insert into lk_stage values(default, '3 (Year 5)');

--lk_status
--LOOK UP

--lk_strength
--LOOK UP

--lk_studentgroup  
--LOOK UP

--lk_studentresourcecategories 
--LOOK UP

--lk_system
--LOOK UP

--lk_term   
--LOOK UP

--lk_theme
--LOOK UP

--lk_venue 
drop table lk_venue;

--lk_year  
--LOOK UP

--pblcoordinator
delete from pblcoordinator;
alter sequence pblcoordinator_auto_id_seq restart with 1;

--podcasturl
delete from podcasturl;
alter sequence podcasturl_auto_id_seq restart with 1;

--ratings   
delete from ratings;
alter sequence ratings_auto_id_seq restart with 1;

--ratingscores 
delete from ratingscores;
alter sequence ratingscores_auto_id_seq restart with 1;

--resource 
drop table resource;

--search_configure  
delete from search_configure;
alter sequence search_configure_auto_id_seq restart with 1;

--staff 
delete from staff;
alter sequence staff_auto_id_seq restart with 1;

--stagecoordinator 
delete from stagecoordinator;
alter sequence stagecoordinator_auto_id_seq restart with 1;

--student_evaluate
delete from student_evaluate;
alter sequence student_evaluate_auto_id_seq restart with 1;

--student_evaluate_data
delete from student_evaluate_data;
alter sequence student_evaluate_data_auto_id_seq restart with 1;

--studentinfo
delete from studentinfo;
alter sequence studentinfo_auto_id_seq restart with 1;

--studentresourcelink          
delete from studentresourcelink;
alter sequence studentresourcelink_auto_id_seq restart with 1;

--studyconsent 
delete from studyconsent;
alter sequence studyconsent_auto_id_seq restart with 1;

--user_disc 
delete from user_disc;
alter sequence user_disc_auto_id_seq restart with 1;




