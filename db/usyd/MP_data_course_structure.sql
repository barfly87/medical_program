-- STAGE (lk_stage)
INSERT INTO lk_stage VALUES (DEFAULT, '');
INSERT INTO lk_stage VALUES (DEFAULT, '1');
INSERT INTO lk_stage VALUES (DEFAULT, '2');
INSERT INTO lk_stage VALUES (DEFAULT, '3');
INSERT INTO lk_stage VALUES (DEFAULT, '4');
INSERT INTO lk_stage VALUES (DEFAULT, '5');
INSERT INTO lk_stage VALUES (DEFAULT, '6');
INSERT INTO lk_stage VALUES (DEFAULT, '7');
INSERT INTO lk_stage VALUES (DEFAULT, '8');
INSERT INTO lk_stage VALUES (DEFAULT, '9');

-- BLOCK (lk_block)
INSERT INTO lk_block VALUES(DEFAULT, '');
INSERT INTO lk_block VALUES(DEFAULT, 'T1: Upptakten - introduktion till läkaryrket');
INSERT INTO lk_block VALUES(DEFAULT, 'T1: Den friska människan I');
INSERT INTO lk_block VALUES(DEFAULT, 'T2: Den friska människan II');
INSERT INTO lk_block VALUES(DEFAULT, 'T3: Den friska människan III');
INSERT INTO lk_block VALUES(DEFAULT, 'T3: Den sjuka människan 1 - basvetenskaplig grund ');
INSERT INTO lk_block VALUES(DEFAULT, 'T4: Den sjuka människan II');
INSERT INTO lk_block VALUES(DEFAULT, 'T4: Integrerad deltentamen (IDT)');
INSERT INTO lk_block VALUES(DEFAULT, 'T5: Klinisk medicin Termin 5');
INSERT INTO lk_block VALUES(DEFAULT, 'T6: Klinisk medicin Termin 6');
INSERT INTO lk_block VALUES(DEFAULT, 'T7: Klinisk medicin - inriktning kirurgi');
INSERT INTO lk_block VALUES(DEFAULT, 'T8: Examensarbetet i medicin');
INSERT INTO lk_block VALUES(DEFAULT, 'T9: Klinisk medicin - inriktning neuro, sinnen, psyke (30hp)');
INSERT INTO lk_block VALUES(DEFAULT, 'T10: Klinisk medicin - inriktning reproduktion och utveckling (22,5 hp)');
INSERT INTO lk_block VALUES(DEFAULT, 'T10: SVK (7,5hp)');
INSERT INTO lk_block VALUES(DEFAULT, 'T11: Hälsa i samhälle och miljö');
INSERT INTO lk_block VALUES(DEFAULT, 'T11: Integrerad sluttentamen');
INSERT INTO lk_block VALUES(DEFAULT, 'SVK (7,5hp + 7,5hp)');



-- STAGE_BLOCK_SEQ (stage_block_seq)
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 1, 2, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 2, 3, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 3, 4, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 4, 5, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 5, 6, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 3, 6, 7, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 7, 8, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 8, 9, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 9, 10, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 6, 10, 11, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 7, 11, 12, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 8, 12, 13, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 9, 13, 14, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 9, 14, 15, 1);

INSERT INTO stage_block_seq VALUES (DEFAULT, 10, 15, 16, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 10, 16, 17, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 10, 17, 18, 1);

-- INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 18, 19, 1);
-- INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 19, 20, 1);
-- INSERT INTO stage_block_seq VALUES (DEFAULT, 4, 20, 21, 1);
-- INSERT INTO stage_block_seq VALUES (DEFAULT, 5, 25, 12, 1);




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

-- BLOCK_PBL_SEQ (block_pbl_seq) to build the number and names of each week in the course
-- First row corresponds to block
-- Second row corresponds to number of weeks
-- Third row row corresponds to name of the week from table lk_pbl above
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

INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 14, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 14, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 14, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 14, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 14, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 15, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 15, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 15, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 15, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 15, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 17, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 17, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 17, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 17, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 17, 6, 6);

-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 2, 1);
-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 3, 2);
-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 4, 3);
-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 5, 4);
--INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 6, 5);
-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 7, 6);

-- INSERT INTO block_pbl_seq VALUES (DEFAULT, 33, 2, 2);