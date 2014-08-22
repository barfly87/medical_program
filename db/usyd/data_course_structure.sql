-- STAGE (lk_stage)
INSERT INTO lk_stage VALUES (DEFAULT, '');
INSERT INTO lk_stage VALUES (DEFAULT, '1');
INSERT INTO lk_stage VALUES (DEFAULT, '2');
INSERT INTO lk_stage VALUES (DEFAULT, '3');
INSERT INTO lk_stage VALUES (DEFAULT, '3 (Year 4)');

-- BLOCK (lk_block)
INSERT INTO lk_block VALUES(DEFAULT, '');
INSERT INTO lk_block VALUES(DEFAULT, 'Foundation Block');
INSERT INTO lk_block VALUES(DEFAULT, 'Drug and Alcohol, Musculoskeletal Sciences');
INSERT INTO lk_block VALUES(DEFAULT, 'Respiratory Sciences');
INSERT INTO lk_block VALUES(DEFAULT, 'Haematology');
INSERT INTO lk_block VALUES(DEFAULT, 'Cardiovascular Sciences');
INSERT INTO lk_block VALUES(DEFAULT, 'Neurosciences, Vision, Behaviour');
INSERT INTO lk_block VALUES(DEFAULT, 'Endocrinology, Nutrition and Reproductive Health');
INSERT INTO lk_block VALUES(DEFAULT, 'Urology and Renal');
INSERT INTO lk_block VALUES(DEFAULT, 'Gastroenterology and Nutrition');
INSERT INTO lk_block VALUES(DEFAULT, 'Oncology, HIV/AIDS and Palliative Care');
INSERT INTO lk_block VALUES(DEFAULT, 'Child and Adolescent Health');
INSERT INTO lk_block VALUES(DEFAULT, 'Community');
INSERT INTO lk_block VALUES(DEFAULT, 'Critical Care');
INSERT INTO lk_block VALUES(DEFAULT, 'Medicine 3');
INSERT INTO lk_block VALUES(DEFAULT, 'Medicine 4');
INSERT INTO lk_block VALUES(DEFAULT, 'Perinatal and Women''s Health');
INSERT INTO lk_block VALUES(DEFAULT, 'Print');
INSERT INTO lk_block VALUES(DEFAULT, 'Surgery');
INSERT INTO lk_block VALUES(DEFAULT, 'Psychiatry and Addiction Medicine');
INSERT INTO lk_block VALUES(DEFAULT, 'Core Block Activities');
INSERT INTO lk_block VALUES(DEFAULT, 'Elective');
INSERT INTO lk_block VALUES(DEFAULT, 'Orientation');

-- STAGE_BLOCK_SEQ (stage_block_seq)
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 1, 2, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 2, 3, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 3, 4, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 4, 5, 1);
INSERT INTO stage_block_seq VALUES (DEFAULT, 2, 5, 6, 1);

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

-- BLOCK (lk_pbl)
INSERT INTO lk_pbl VALUES(DEFAULT, '', '');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 1', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 2', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 3', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 4', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Week 5', 'Introduction Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I''ve been here before', 'Normal pregnancy');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Is this serious', 'Carcinoma');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A question of communication', 'Tuberculosis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Mrs. Newman''s indigestion', 'Acute cardiac presentation');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Just coping', 'Prescription drugs & alcohol use');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Tracey''s life', 'Opioid dependence');
INSERT INTO lk_pbl VALUES(DEFAULT, 'New wheels', 'Fractured femur (MVA)');
INSERT INTO lk_pbl VALUES(DEFAULT, 'An embarrassing fall', 'Fractured NOF & osteporosis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I must be getting old', 'Osteoarthritis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'She says she''ll lose her job', 'Carpal tunnel syndrome');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Why me?', 'Rheumatoid arthritis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I always work hard', 'Back injury, sciatica');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Not at fault', 'Chest trauma, pneumothorax');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Wheezing and breathless', 'Asthma');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A nasty cough', 'Acute exacerbation of chronic obstructive pulmonary disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Ex-Navy', 'Interstitial lung disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Sleeping on the job', 'Sleep apnoea, respiratory failure');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A different cause of cough', 'Cystic fibrosis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Difficult circumstances', 'Pneumonia, Otitis media');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Always tired', 'Anaemia');
INSERT INTO lk_pbl VALUES(DEFAULT, 'While I''m here', 'CLL, Herpes zoster');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A swollen knee', 'Bleeding disorder');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Pale and feverish', 'Thalassaemia, malaria');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Michelle''s painful calf', 'DVT, iron deficiency in pregnancy');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Going downhill', 'Heart failure');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A breathless pregnancy', 'Valvular heart disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Mrs Newman''s indigestion', 'Myocardial ischaemia');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Jennifer and David''s baby', 'Congenital heart disease, Down syndrome');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Mr. Fisher''s snoring', 'Hypertension');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Considering alternatives', 'Simple faint, alternative health practices');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Kevin''s accident', 'Spinal cord trauma');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Jason and Brooke', 'Epilepsy, spina bifida');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A nasty drop', 'Cerebrovascular accident');
INSERT INTO lk_pbl VALUES(DEFAULT, 'My head hurts', 'Meningitis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Reading the score', 'Migraine');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A near accident', 'Multiple sclerosis, visual loss');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Slowing down', 'Parkinson''s disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Where am I?', 'Dementia');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I can''t cope', 'Depressive disorder');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I know you''re out to get me', 'Schizophrenia');

INSERT INTO lk_pbl VALUES(DEFAULT, 'A difficult colleague', 'Thyrotoxicosis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Problems at school', 'Pituitary tumour');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Unwell and unhappy', 'Steroid excess');
INSERT INTO lk_pbl VALUES(DEFAULT, 'They mustn''t find out', 'Type 1 diabetes');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Taking care of business', 'Type 2 diabetes');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Under pressure', 'Infertility');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Don''t tell my mother', 'Sexually transmitted disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Abnormal bleeding', 'Menstrual disorders');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Bill''s concern', 'Prostate disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Washed out', 'Diuretic abuse, hypovolaemia');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Found confused', 'Acute renal failure');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Swollen ankles', 'Chronic renal failure');

INSERT INTO lk_pbl VALUES(DEFAULT, 'A persistent pain', 'Peptic ulcer');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I''m not a hundred per cent', 'Coeliac disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'The good life Part1', 'Alcoholic liver disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'The good life Part2', 'Liver disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'My eyes look yellow', 'Gallstones');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Small and Sickly', 'Failure to thrive in infancy');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Really miserable', 'Acute infective diarrhoea');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Like clockwork', 'Colorectal cancer');
INSERT INTO lk_pbl VALUES(DEFAULT, 'What can I do', 'Lung cancer');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A concerned GP', 'Cervical cancer');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A matter of choice', 'Breast cancer');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I can''t train', 'HIV/AIDS');

INSERT INTO lk_pbl VALUES(DEFAULT, 'Aching joints', 'SLE and connective tissue diseases');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Getting worse', 'Myeloma');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A lump in the neck', 'Lymphoma (Hodgkins and non-Hodgkins)');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Short of breath', 'CCF, AF, peripheral embolus');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A dull ache', 'Chest pain, carotid bruit');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Still sick', 'Pleural effsion, empyema');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Out of puff', 'Breathlessness in a smoker');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Awful colic', 'Acute abdomen');
INSERT INTO lk_pbl VALUES(DEFAULT, 'An uncomfortable night', 'Bloody diarrhoea');
INSERT INTO lk_pbl VALUES(DEFAULT, 'What does this mean', 'Haemachromatosis, DD includes alcohol and drugs');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Murky waters', 'Mesangial IgA disease, DD glomerulonephritis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A dragging pain', 'Polycystic kidney disease');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Plumbing problems', 'Hydronephrosis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A bad reaction', 'Allergies, urticaria, anaphylaxis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Burning up', 'Acute CMV, DD PUO');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Uphill battle', 'Muscle disorders, myasthenia, myotonia, myositis');
INSERT INTO lk_pbl VALUES(DEFAULT, 'I think I''m losing it', 'Cerebral space occupying lesion, DD CNS infection');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Lost control', 'Vestibular disorders and DD');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Pre-op check', 'General anaesthetic in the compromised patient');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Love hurts', 'Paracetamol poisoning and liver failure');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Flat as a tack', 'Shock (cardiogenic, hypovolaemic, septic)');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Off colour', 'Integrated cancer care: adenocarcinoma as an example');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Lost the plot', 'Iatrogenic disease in the elderly (electrolyte disorder)');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Mum''s hurt', 'Aged carer decompensation');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Losing sight', 'Blindness, common causes and prevention');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Confused and drowsy', 'Hypercalcaemia, hyperparathyroidism and DD');
INSERT INTO lk_pbl VALUES(DEFAULT, 'What''s gone wrong', 'Pituitary adenoma (microprolactinoma)');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Had enough', 'Osteoporosis, Paget''s disease, DD');
INSERT INTO lk_pbl VALUES(DEFAULT, 'Orientation Week', 'Orientation Week');
INSERT INTO lk_pbl VALUES(DEFAULT, 'A worrying blood test', 'Chronic kidney disease, diabetic nephropathy');
INSERT INTO lk_pbl VALUES(DEFAULT, 'TBA 1', 'TBA 1');
INSERT INTO lk_pbl VALUES(DEFAULT, 'TBA 2', 'TBA 2');

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
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 5, 5);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 6, 6);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 7, 7);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 8, 8);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 9, 9);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 10, 10);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 2, 11);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 3, 12);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 4, 13);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 5, 14);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 6, 15);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 7, 16);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 8, 17);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 2, 9, 18);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 2, 19);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 3, 20);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 4, 21);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 5, 22);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 6, 23);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 7, 24);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 3, 8, 25);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 2, 26);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 3, 27);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 4, 28);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 5, 29);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 4, 6, 30);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 2, 31);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 3, 32);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 4, 33);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 5, 34);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 6, 35);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 5, 7, 36);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 2, 37);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 3, 38);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 4, 39);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 5, 40);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 6, 41);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 7, 42);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 8, 43);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 9, 44);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 10, 45);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 6, 11, 46);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 2, 47);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 3, 48);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 4, 49);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 5, 50);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 6, 51);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 7, 52);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 8, 53);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 7, 9, 54);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 2, 55);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 3, 56);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 4, 57);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 8, 5, 58);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 2, 59);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 3, 60);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 4, 61);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 5, 62);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 6, 63);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 7, 64);
--INSERT INTO block_pbl_seq VALUES (DEFAULT, 9, 8, 65);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 2, 66);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 3, 67);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 4, 68);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 5, 69);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 10, 6, 70);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 2, 71);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 3, 72);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 4, 73);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 5, 74);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 6, 75);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 7, 76);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 8, 77);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 9, 78);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 10, 79);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 11, 80);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 12, 81);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 13, 82);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 14, 83);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 15, 84);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 16, 85);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 17, 86);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 18, 87);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 19, 88);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 20, 89);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 21, 90);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 22, 91);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 23, 92);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 24, 93);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 25, 94);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 26, 95);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 27, 96);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 28, 97);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 11, 29, 98);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 2, 71);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 3, 72);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 4, 73);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 5, 74);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 6, 75);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 7, 76);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 8, 77);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 9, 78);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 10, 79);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 11, 80);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 12, 81);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 13, 82);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 14, 83);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 15, 84);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 16, 85);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 17, 86);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 18, 87);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 19, 88);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 20, 89);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 21, 90);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 22, 91);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 23, 92);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 24, 93);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 25, 94);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 26, 95);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 27, 96);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 28, 97);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 21, 29, 98);

INSERT INTO block_pbl_seq VALUES (DEFAULT, 33, 2, 99);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 11,101);
INSERT INTO block_pbl_seq VALUES (DEFAULT, 1, 12,102);

