-- STAGE (lk_stage)
INSERT INTO lk_stage VALUES (DEFAULT, '');
INSERT INTO lk_stage VALUES (DEFAULT, '1');
INSERT INTO lk_stage VALUES (DEFAULT, '2');
INSERT INTO lk_stage VALUES (DEFAULT, '3');
INSERT INTO lk_stage VALUES (DEFAULT, '3 (Year 4)');

-- BLOCK (lk_block)
INSERT INTO lk_block VALUES(DEFAULT, '');
INSERT INTO lk_block VALUES(DEFAULT, 'Health Informatics - Needs, Objectives and Limitations (5HI000)');
INSERT INTO lk_block VALUES(DEFAULT, 'Basic Medical Science');
INSERT INTO lk_block VALUES(DEFAULT, 'Health Care Organization and Management ');
INSERT INTO lk_block VALUES(DEFAULT, 'Computer Applications in Health Care and Biomedicine');
INSERT INTO lk_block VALUES(DEFAULT, 'User Needs and Requirements Engineering');
INSERT INTO lk_block VALUES(DEFAULT, 'Evaluation Methods for Health Informatics');
INSERT INTO lk_block VALUES(DEFAULT, 'Standardisation within health informatics');
INSERT INTO lk_block VALUES(DEFAULT, 'Projects in Health Informatics 1 – from Idea to Specification');
INSERT INTO lk_block VALUES(DEFAULT, 'Projects in Health Informatics 2 - project and information management');
INSERT INTO lk_block VALUES(DEFAULT, 'Case Studies in Health Informatics');
INSERT INTO lk_block VALUES(DEFAULT, 'Clinical Decision Support');
INSERT INTO lk_block VALUES(DEFAULT, 'Modelling, Simulation and Visualisation in Health Informatics');
INSERT INTO lk_block VALUES(DEFAULT, 'Data Mining in Computer and System Sciences');
INSERT INTO lk_block VALUES(DEFAULT, 'Principles of Computer Security');
INSERT INTO lk_block VALUES(DEFAULT, 'Scientific Communication and Research Methodology');
INSERT INTO lk_block VALUES(DEFAULT, 'Project Management');
INSERT INTO lk_block VALUES(DEFAULT, 'From Idea to Service Business');
INSERT INTO lk_block VALUES(DEFAULT, 'Scientific Research Methods');
INSERT INTO lk_block VALUES(DEFAULT, 'Informatics and improvement work in healthcare organisations');
INSERT INTO lk_block VALUES(DEFAULT, 'Advanced course in Health and Medical Care Management');
INSERT INTO lk_block VALUES(DEFAULT, 'Degree project in Health Informatics');


-- STAGE_BLOCK_SEQ (stage_block_seq)
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 1, 2, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 2, 3, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 3, 4, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 4, 5, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 5, 6, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 6, 7, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 7, 8, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 8, 9, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 9, 10, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 10, 11, 1);

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




-- BLOCK (lk_pbl)
INSERT INTO lk_pbl VALUES(DEFAULT, '', '');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 1', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 2', 'Group work Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 3', 'Self study Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 4', 'Presentation Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 5', 'Examination Week');


-- BLOCK WEEK (lk_blockweek)
INSERT INTO lk_blockweek VALUES (DEFAULT, NULL);
INSERT INTO lk_blockweek VALUES (DEFAULT, 1);
INSERT INTO lk_blockweek VALUES (DEFAULT, 2);
INSERT INTO lk_blockweek VALUES (DEFAULT, 3);
INSERT INTO lk_blockweek VALUES (DEFAULT, 4);
INSERT INTO lk_blockweek VALUES (DEFAULT, 5);
INSERT INTO lk_blockweek VALUES (DEFAULT, 6);
INSERT INTO lk_blockweek VALUES (DEFAULT, 7);
INSERT INTO lk_blockweek VALUES (DEFAULT, 8);
INSERT INTO lk_blockweek VALUES (DEFAULT, 9);
INSERT INTO lk_blockweek VALUES (DEFAULT, 10);
INSERT INTO lk_blockweek VALUES (DEFAULT, 11);
INSERT INTO lk_blockweek VALUES (DEFAULT, 12);
INSERT INTO lk_blockweek VALUES (DEFAULT, 13);
INSERT INTO lk_blockweek VALUES (DEFAULT, 14);
INSERT INTO lk_blockweek VALUES (DEFAULT, 15);
INSERT INTO lk_blockweek VALUES (DEFAULT, 16);
INSERT INTO lk_blockweek VALUES (DEFAULT, 17);
INSERT INTO lk_blockweek VALUES (DEFAULT, 18);
INSERT INTO lk_blockweek VALUES (DEFAULT, 19);
INSERT INTO lk_blockweek VALUES (DEFAULT, 20);
INSERT INTO lk_blockweek VALUES (DEFAULT, 21);
INSERT INTO lk_blockweek VALUES (DEFAULT, 22);
INSERT INTO lk_blockweek VALUES (DEFAULT, 23);
INSERT INTO lk_blockweek VALUES (DEFAULT, 24);
INSERT INTO lk_blockweek VALUES (DEFAULT, 25);
INSERT INTO lk_blockweek VALUES (DEFAULT, 26);
INSERT INTO lk_blockweek VALUES (DEFAULT, 27);
INSERT INTO lk_blockweek VALUES (DEFAULT, 28);
INSERT INTO lk_blockweek VALUES (DEFAULT, 29);
INSERT INTO lk_blockweek VALUES (DEFAULT, 30);

-- BLOCK_PBL_SEQ (block_pbl_seq)
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 4, 4);


INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 5, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 2, 1);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 3, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 4, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 5, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 6, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 7, 6);


INSERT INTO block_pbl_seq VALUES (DEFAULT, 33, 2, 2);