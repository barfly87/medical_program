-- Assessment Type
INSERT INTO lk_assesstype VALUES (DEFAULT, 'SBA');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Spot test');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'OSCE');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Clinical exercise');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Long case');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Log book');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Supervisor feedback');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Written assessment');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Portfolio');
SELECT nextval('lk_assesstype_auto_id_seq');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Oral presentation');
INSERT INTO lk_assesstype VALUES (DEFAULT, 'Not assessed');


-- Achievement
INSERT INTO lk_achievement VALUES(DEFAULT, 'Core', 'CORE objective that ALL students', 'Competence. Core objectives that all students should achieve. Focus on what is relevant for safe and competent intern practice. Major component of assessment');
INSERT INTO lk_achievement VALUES(DEFAULT, 'Desired', 'DESIRED objective that expert students', 'Expert. Desired objectives that some students should achieve. Minor component of assessment activities.');


-- Graduate Attribute
INSERT INTO lk_gradattrib VALUES (DEFAULT, '');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Research and Inquiry');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Information Literacy');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Personal and Intellectual Autonomy');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Ethical, Social and Professional Understanding');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Communication');


-- JMO
INSERT INTO lk_jmo VALUES(DEFAULT, '');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Common Problems');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Patient Assessment');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Emergencies');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Patient Management');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Patient Interaction');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Skills and Procedures');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Managing Information');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Working in Teams');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Safe Patient Care');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Professional Behaviour');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Teaching and Learning');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Doctor and Society');


-- LO and TA Linkage Strength
INSERT INTO lk_strength VALUES (DEFAULT, '', '');
INSERT INTO lk_strength VALUES (DEFAULT, 'Main', 'MAIN: This LO is a key or major focus of this TA');
INSERT INTO lk_strength VALUES (DEFAULT, 'Other', 'OTHER: This LO is a secondary or other focus of this TA');
INSERT INTO lk_strength VALUES (DEFAULT, 'Incidental', 'INCIDENTAL: This TA incidentally fulfils this LO, not intended or planned');
INSERT INTO lk_strength VALUES (DEFAULT, 'TBC', 'To be confirmed');


-- Review
INSERT INTO lk_review VALUES (DEFAULT, 'CAM', 'Course components related to complementary & alternative medicine');
INSERT INTO lk_review VALUES (DEFAULT, 'Community Commitment', 'All aspects of community service, including the study of health inequalities, advocacy and student initiated projects');
INSERT INTO lk_review VALUES (DEFAULT, 'Cross Cultural Sensitivity', 'Course components that increase students'' awareness of cross cultural differences and sensitivities');
INSERT INTO lk_review VALUES (DEFAULT, 'Disability', 'Developmental and intellectual disability');
INSERT INTO lk_review VALUES (DEFAULT, 'Indigenous Health', 'Course components related to indigenous health');
INSERT INTO lk_review VALUES (DEFAULT, 'Information Skills', 'Skills in information literacy, health information sources, use of library resources.');
INSERT INTO lk_review VALUES (DEFAULT, 'International Health', 'Includes key international health issues, comparative health systems, global health policies and organisations');
INSERT INTO lk_review VALUES (DEFAULT, 'Interprofessional Learning', 'Collaborative learning between medical and other health professional students, including teamwork and communication, multidisciplinary care, professional roles and relationships.');
INSERT INTO lk_review VALUES (DEFAULT, 'Leadership', 'Student leadership, including Faculty and community based activities and advocacy');
INSERT INTO lk_review VALUES (DEFAULT, 'Management Skills', 'Healthcare delivery at system, organisation and team level, resource allocation, the patient journey and time management');
INSERT INTO lk_review VALUES (DEFAULT, 'Mentoring', 'Faculty and student initiated programs in mentoring, including peer mentoring');
INSERT INTO lk_review VALUES (DEFAULT, 'Quality & Safety', 'Course components related to patient safety and health care quality');
INSERT INTO lk_review VALUES (DEFAULT, 'Refugee Health', 'Refugee health issues, health and human rights');
INSERT INTO lk_review VALUES (DEFAULT, 'Spirituality', 'Clinical aspects of spirituality and health');
INSERT INTO lk_review VALUES (DEFAULT, 'Students as Teachers', 'Skills in peer teaching, public speaking');
INSERT INTO lk_review VALUES (DEFAULT, 'Clinical Pharmacology', 'Pharmacology in clinical practice, including rational and safe prescribing');
INSERT INTO lk_review VALUES (DEFAULT, 'Diagnostic Pathology', 'Pathology in clinical practice, including appropriate ordering and interpretation of laboratory and other diagnostic investigations');
INSERT INTO lk_review VALUES (DEFAULT, 'Research', 'Research skills and experience including basic science, clinical, public health, health services, humanities');
INSERT INTO lk_review VALUES (DEFAULT, 'Clinical Biochemistry', 'Appropriate ordering and interpretation of biochemical investigations and an awareness of their costs.');
INSERT INTO lk_review VALUES (DEFAULT, 'Medical Humanities', 'The arts and humanities in medicine');


-- LO and TA Status
INSERT INTO lk_status VALUES (DEFAULT, '');
INSERT INTO lk_status VALUES (DEFAULT, 'In development');
INSERT INTO lk_status VALUES (DEFAULT, 'Awaiting approval');
INSERT INTO lk_status VALUES (DEFAULT, 'Released');
INSERT INTO lk_status VALUES (DEFAULT, 'Rejected');
INSERT INTO lk_status VALUES (DEFAULT, 'Archived');
INSERT INTO lk_status VALUES (DEFAULT, 'New version');
INSERT INTO lk_status VALUES (DEFAULT, 'Old version');


-- Student Group
INSERT INTO lk_studentgroup VALUES (DEFAULT, '');
INSERT INTO lk_studentgroup VALUES (DEFAULT, 'Year cohort');
INSERT INTO lk_studentgroup VALUES (DEFAULT, 'PBL group');
INSERT INTO lk_studentgroup VALUES (DEFAULT, 'Clinical School cohort');
INSERT INTO lk_studentgroup VALUES (DEFAULT, 'Clinical School group');
INSERT INTO lk_studentgroup VALUES (DEFAULT, 'Select group');


-- Skill
INSERT INTO lk_skill VALUES (DEFAULT, '');
INSERT INTO lk_skill VALUES (DEFAULT, 'Communication');
INSERT INTO lk_skill VALUES (DEFAULT, 'Clinical Diagnostic');
INSERT INTO lk_skill VALUES (DEFAULT, 'Procedural');
INSERT INTO lk_skill VALUES (DEFAULT, 'Management');


-- System
INSERT INTO lk_system VALUES (DEFAULT, 'Not Applicable', 1);
INSERT INTO lk_system VALUES (DEFAULT, 'Cardiovascular', 2);
INSERT INTO lk_system VALUES (DEFAULT, 'Cellular Systems', 3);
INSERT INTO lk_system VALUES (DEFAULT, 'Central and Peripheral Nervous', 4);
INSERT INTO lk_system VALUES (DEFAULT, 'Endocrine', 5);
INSERT INTO lk_system VALUES (DEFAULT, 'Gastrointestinal', 6);
INSERT INTO lk_system VALUES (DEFAULT, 'Haem and Lymph', 7);
INSERT INTO lk_system VALUES (DEFAULT, 'Musculoskeletal', 8);
INSERT INTO lk_system VALUES (DEFAULT, 'Renal and Urinary', 9);
INSERT INTO lk_system VALUES (DEFAULT, 'Reproductive', 10);
INSERT INTO lk_system VALUES (DEFAULT, 'Respiratory', 11);
INSERT INTO lk_system VALUES (DEFAULT, 'Skin and Connective Tissue', 12);


-- TA Activity Type
INSERT INTO lk_activitytype VALUES(DEFAULT,'',0);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Lecture',1);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Theme session',2);
INSERT INTO lk_activitytype VALUES(DEFAULT,'PBL session',3);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Essential readings',4);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Pt-Dr tutorial',5);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Skills session',6);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Workshop/Intensive',7);
INSERT INTO lk_activitytype VALUES(DEFAULT,'SCORPIO',8);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Options',9);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Clinical attachment',10);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Clinical reasoning session',11);
INSERT INTO lk_activitytype VALUES(DEFAULT,'PEARLs',12);
INSERT INTO lk_activitytype VALUES(DEFAULT,'PPD session',13);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Pop Med',14);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Self directed learning',15);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Mentoring',16);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Honours',17);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Elective',18);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Remediation',19);
INSERT INTO lk_activitytype VALUES(DEFAULT,'OTHER',20);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Combined activities',21);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Online resource',22);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Integrative seminars', 23);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Clinical Day', 24);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Core curriculum', 25);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Case presentation', 26);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Assessment portfolio', 27);
INSERT INTO lk_activitytype VALUES(DEFAULT,'Procedural skills session', 28);


-- Sequence Number
INSERT INTO lk_sequence_num VALUES (DEFAULT, NULL);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 1);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 2);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 3);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 4);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 5);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 6);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 7);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 8);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 9);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 10);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 11);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 12);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 13);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 14);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 15);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 16);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 17);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 18);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 19);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 20);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 21);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 22);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 23);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 24);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 25);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 26);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 27);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 28);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 29);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 30);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 31);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 32);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 33);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 34);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 35);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 36);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 37);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 38);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 39);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 40);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 41);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 42);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 43);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 44);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 45);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 46);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 47);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 48);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 49);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 50);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 51);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 52);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 53);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 54);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 55);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 56);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 57);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 58);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 59);
INSERT INTO lk_sequence_num VALUES (DEFAULT, 60);


-- TA Term
INSERT INTO lk_term VALUES (DEFAULT, NULL);
INSERT INTO lk_term VALUES (DEFAULT, 'A');
INSERT INTO lk_term VALUES (DEFAULT, 'B');
INSERT INTO lk_term VALUES (DEFAULT, 'C');
INSERT INTO lk_term VALUES (DEFAULT, 'D');
INSERT INTO lk_term VALUES (DEFAULT, 'E');
INSERT INTO lk_term VALUES (DEFAULT, 'F');
INSERT INTO lk_term VALUES (DEFAULT, 'G');
INSERT INTO lk_term VALUES (DEFAULT, 'H');
INSERT INTO lk_term VALUES (DEFAULT, 'I');
INSERT INTO lk_term VALUES (DEFAULT, 'J');
INSERT INTO lk_term VALUES (DEFAULT, 'B/G');
INSERT INTO lk_term VALUES (DEFAULT, 'C/H');
INSERT INTO lk_term VALUES (DEFAULT, 'D/I');


-- Resource Type;
-- 1
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Case Summary',                    'casesummary',                      'student',                1, 0, 0, 0);

-- 2 
-- 'Resources' would be deleted after the insert happens. Its done like this so we can use DEFAULT
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Resources',                       'resources',                        'student',                0, 0, 0, 0);

-- 3
-- 'Medical Humanities' would be deleted after the insert happens. Its done like this so we can use DEFAULT
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Medical Humanities',              'medicalhumanities',                'student',                1, 0, 0, 0);

-- 4
-- 'Tutor Guide' would be deleted after the insert happens. Its done like this so we can use DEFAULT 
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Tutor Guide',                     'tutorguide',                       'staff',                  1, 0, 0, 0); 

-- 5
-- 'Student Guide' would be deleted after the insert happens. Its done like this so we can use DEFAULT
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Student Guide',                   'studentguide',                     'student',                1, 0, 0, 0); 

-- 6
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Hand Book',                       'handbook',                         'student',                1, 1, 1, 1); 

-- 7
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Resources',                       'resources',                        'student',                1, 1, 1, 1); 

-- 8
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Content',                         'content',                          'student',                0, 0, 1, 1); 

-- 9
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'References',                      'references',                       'student',                0, 0, 1, 1); 

-- 10
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Staff Only',                      'staffonly',                        'staff',                  1, 1, 1, 1); 

-- 11
-- 'Curriculum Resources' would be deleted after the insert happens. Its done like this so we can use DEFAULT
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Curriculum Resources',            'curriculumresources',              'student',                0, 0, 0, 0); 

-- 12
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Administrative Resources',        'administrativeresources',          'student',                1, 1, 1, 1); 

-- 13
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Recordings',                      '',                                 'student',                0, 0, 1, 0); 

-- 14
-- 'Mechanisms' would be deleted after the insert happens. Its done like this so we can use DEFAULT
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Mechanisms',                      'mechanisms',                       'student',                1, 0, 0, 0);

-- 15
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Introduction',                    'introduction',                     'student',                0, 1, 0, 0);

-- 16
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Prologue',                        'prologue',                         'student',                0, 0, 1, 0);

-- 17
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'PBL Icon', '', 'student', 1, 0, 0, 0);

-- 18
INSERT INTO lk_resourcetype VALUES (DEFAULT, 'International',                    'international',                     'student',                0, 1, 0, 0);

INSERT INTO lk_resourcetype VALUES (DEFAULT, 'Pt-Dr Handbook Introduction', '', 'student', 0, 1, 0, 0);

DELETE FROM lk_resourcetype where auto_id = 2 and resource_type ='Resources';
DELETE FROM lk_resourcetype where auto_id = 3 and resource_type ='Medical Humanities';
DELETE FROM lk_resourcetype where auto_id = 4 and resource_type ='Tutor Guide';
DELETE FROM lk_resourcetype where auto_id = 5 and resource_type ='Student Guide';
DELETE FROM lk_resourcetype where auto_id = 11 and resource_type ='Curriculum Resources';
DELETE FROM lk_resourcetype where auto_id = 14 and resource_type ='Mechanisms';


-- Curriculum Area Status
INSERT INTO lk_curriculumareas_status VALUES(DEFAULT, 'Current');
INSERT INTO lk_curriculumareas_status VALUES(DEFAULT, 'Archived');


-- Student Resource Categories
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Comment', 1,'A comment on this Learning Objective');
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Reflection', 2,'A comment on this Learning Objective');
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Learning Resource', 3,'A useful learning resource related to this Learning Objective');
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Notes', 4,'My notes related to this topic');
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Article', 5,'A peer reviewed article relevant to this Objective');
INSERT INTO lk_studentresourcecategories VALUES(DEFAULT, 'Summary', 6,'Shared summary of this Learning Objective');


-- Rating Categories
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Relevant', 1,'Useful and relevant to this Objective');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Informative', 1,'I learned something from this');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Thought-provoking', 1,'Made me think');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Off-topic', -1,'Not relevant to this Objective');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Entertaining', 1,'I learned while being entertained');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Misleading', -1,'Parts or all of this were factually incorrect');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'Confusing', -1,'Too difficult to comprehend');
INSERT INTO lk_ratingcategories VALUES(DEFAULT, 'tl;dr', -1,'Too long; didn''t read');


-- Staff Type
COPY lk_stafftype (auto_id, name) FROM stdin;
1	Theme Chairs
2	Honours
3	Admissions
4	Electives
5	Independent Learning Activity
6	Core Blocks
7	Specialty Blocks
8	Pre-Internship
9	Executive Officers
10	Medical Education Academics
11	Sub Deans
12	Developers
\.

INSERT INTO lk_year VALUES (DEFAULT, NULL);
INSERT INTO lk_year VALUES (DEFAULT, 2);
INSERT INTO lk_year VALUES (DEFAULT, 3);
INSERT INTO lk_year VALUES (DEFAULT, 4);
INSERT INTO lk_year VALUES (DEFAULT, 5);
INSERT INTO lk_year VALUES (DEFAULT, 6);

INSERT INTO lk_loscope VALUES (DEFAULT, 'this teaching session');
INSERT INTO lk_loscope VALUES (DEFAULT, 'this PBL (or Week)');
INSERT INTO lk_loscope VALUES (DEFAULT, 'this Block');
INSERT INTO lk_loscope VALUES (DEFAULT, 'this Stage');
INSERT INTO lk_loscope VALUES (DEFAULT, 'the Medical Program (on graduation)');

INSERT INTO lk_loverb VALUES (DEFAULT, 'describe');
INSERT INTO lk_loverb VALUES (DEFAULT, 'list');
INSERT INTO lk_loverb VALUES (DEFAULT, 'outline');
INSERT INTO lk_loverb VALUES (DEFAULT, 'recognise');
INSERT INTO lk_loverb VALUES (DEFAULT, 'identify');
INSERT INTO lk_loverb VALUES (DEFAULT, 'summarise');
INSERT INTO lk_loverb VALUES (DEFAULT, 'explain');
INSERT INTO lk_loverb VALUES (DEFAULT, 'examine');
INSERT INTO lk_loverb VALUES (DEFAULT, 'discuss');
INSERT INTO lk_loverb VALUES (DEFAULT, 'interpret');
INSERT INTO lk_loverb VALUES (DEFAULT, 'compare');
INSERT INTO lk_loverb VALUES (DEFAULT, 'contrast');
INSERT INTO lk_loverb VALUES (DEFAULT, 'estimate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'calculate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'demonstrate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'apply');
INSERT INTO lk_loverb VALUES (DEFAULT, 'use');
INSERT INTO lk_loverb VALUES (DEFAULT, 'operate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'understand');
INSERT INTO lk_loverb VALUES (DEFAULT, 'differentiate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'appraise');
INSERT INTO lk_loverb VALUES (DEFAULT, 'formulate');
INSERT INTO lk_loverb VALUES (DEFAULT, 'develop');
INSERT INTO lk_loverb VALUES (DEFAULT, 'test');
