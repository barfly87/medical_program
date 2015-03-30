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

-- T1 Upptakten - introduktion till 
INSERT INTO lk_pbl VALUES(DEFAULT, 'Upptakt', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Vetenskaplig Utveckling', 'Group work Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Professionell Utveckling', 'Self study Week');

-- T1: Den friska människan I
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Grundläggande struktur och utveckling – från ägg till embryo');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Matsmältning och ämnesomsättning');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Primärvården');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 4', 'Integrering med slutexamination');

-- T2: Den friska människan II
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Blodbildning, immunsystemet, hud, cirkulation, temperaturreglering och andning');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Urinorganen, kroppsvätskorna, endokrina systemet och reproduktion');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Människan i rörelse');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 4', 'Vetenskaplig och professionell utveckling');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 5', 'Integrering och slutexamination');

-- T3: Den friska människan III
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Kroppen som enhet ytanatomi och topografi');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Nervsystemet - från jonkanal till beteende');

-- T3: Den sjuka människan 1 - basvetenskaplig grund
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Inflammation, sjukdomsmekanismer och organspecifik patologi');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Integration och examination');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'PU, PV, VetU');

-- T4: Den sjuka människan II
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Infektionsorsaker – infektionsförsvar och läkemedel');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Medicinsk diagnostik');

-- T4: Integrerad deltentamen (IDT)
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'En praktisk färdighetsexamination på patient');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Skriftlig tentamen');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Integrerande seminarium');

-- T5: Klinisk medicin Termin 5
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Blodbildning och immunsystemet');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Cirkulation');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Andning');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 4', 'Matsmältning');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 5', 'Ämnesomsättning och endokrina systemet');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 6', 'Urinorganen');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 7', 'Rörelse');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 8', 'Hud');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 9', 'Åldrande');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 10', 'Infektioner');

-- T6: Klinisk medicin Termin 6


-- T7: Klinisk medicin - inriktning kirurgi
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Matsmältning I');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Matsmältning II');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Rörelse');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 4', 'KUA');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 5', 'Urinorgansfunktioner');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 6', 'Akut I');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 7', 'Akut II');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 8', 'Tumörer/det endokrina systemet');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 9', 'Integration och examination');

-- T8: Examensarbetet i medicin
INSERT INTO lk_pbl VALUES(DEFAULT, 'Fas 1', 'Planering');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Fas 2', 'Praktiskt arbete med halvtidsrapport');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Fas 3', 'Redovisning');

-- T9: Klinisk medicin - inriktning neuro, sinnen, psyke (30hp)
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Neuro');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Sinnen');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Psyke');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 4', 'Tentamen');

-- T10: Klinisk medicin - inriktning reproduktion och utveckling
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Reproduktion');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Utveckling');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 3', 'Integration och examination');

-- T10: SVK

--T11: Hälsa i samhälle och miljö

-- T11: Integrerad sluttentamen
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 1', 'Situationsbaserad examination');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Moment 2', 'Reflekterande uppgift');

-- SVK

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
-- Second row corresponds to number of weeks (sequence, starts from 01 to... next to the title)
-- Third row row corresponds to name of the week from table lk_pbl above
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 2, 7);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 3, 8);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 4, 9);


INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 2, 10);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 3, 11);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 4, 12);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 5, 13);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 2, 14);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 3, 15);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 4, 16);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 5, 17);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 6, 18);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 2, 19);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 3, 20);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 2, 21);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 3, 22);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 4, 23);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 2, 24);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 3, 25);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 2, 26);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 3, 27);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 4, 28);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 2, 29);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 3, 30);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 4, 31);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 5, 32);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 6, 33);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 7, 34);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 8, 35);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 9, 36);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 10, 37);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 11, 38);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 2, 2);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 3, 3);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 4, 4);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 6, 6);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 2, 39);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 3, 40);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 4, 41);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 5, 42);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 6, 43);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 7, 44);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 8, 45);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 9, 46);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 10, 47);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 2, 48);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 3, 49);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 4, 50);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 2, 51);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 3, 52);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 4, 53);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 12, 5, 54);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 2, 55);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 3, 56);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 13, 4, 57);

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

INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 2, 58);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 16, 3, 59);

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