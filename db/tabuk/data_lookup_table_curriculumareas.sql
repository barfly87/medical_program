-- Copy curriculum areas from SMP compass, then add the following

--Anatomy
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Skeleton' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Muscles' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Nervous tissue' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Organs' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Vessels' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Integument' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Joints' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Fetal development' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Fetal membranes' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Epithelial tissue' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Connective tissue' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Lymphatic tissues' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Knowledge' FROM lk_discipline WHERE name ='Anatomy';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Transferable  Skills' FROM lk_discipline WHERE name ='Anatomy';

--Physiology
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Central nervous system' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Cardiovascular system' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Autonomic nervous system' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Respiratory system' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Urinary system' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Blood' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Hearing' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Vision' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Endocrine' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'GIT' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Metabolism' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Muscle' FROM lk_discipline WHERE name ='Physiology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Reproduction' FROM lk_discipline WHERE name ='Physiology';

--Pathology
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Cell Injury, Cell Death, and Adaptations' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Acute and Chronic Inflammation' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Tissue Repair; Regeneration, Healing & Fibrosis' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Hemodynamic Disorders' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Neoplasia' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Infectious (Granulomatous) diseases' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Musculoskeletal System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Cardiovascular System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Respiratory System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Urinary System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Immunology, Blood, & Lymphatic System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Nutrition & Metabolism' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Endocrine System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Reproductive System' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Gastrointestinal System, Liver, Gall Bladder, Pancreas' FROM lk_discipline WHERE name ='Pathology';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Central Nervous System' FROM lk_discipline WHERE name ='Pathology';

--Internal Medicine
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Background knowledge' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Clinical presentations' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Public health' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'History taking' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Physical examination' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Clinical diagnosis' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Investigations' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Management' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Drugs and therapeutics' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Emergencies' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Communication' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Global objectives' FROM lk_discipline WHERE name ='Internal Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Procedural skills' FROM lk_discipline WHERE name ='Internal Medicine';

--Community Medicine
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'General concepts' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Quantitative Sciences' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Health Policy & Health Care System' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Environmental Health Sciences' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Nutrition in Public Health' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Family Health' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Communicable Diseases' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Non Communicable Diseases' FROM lk_discipline WHERE name ='Community Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Occupational Health' FROM lk_discipline WHERE name ='Community Medicine';

--Family Medicine
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Principles of Family Medicine' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Family Medicine in the Community' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Communication and Consultation Skills' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Problem Solving' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Child and Adolescent Health' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Women''s Health' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Men''s Health' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Skin Problems' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Chronic Disorders' FROM lk_discipline WHERE name ='Family Medicine';
INSERT INTO lk_curriculumareas (discipline_id, curriculumarea) SELECT auto_id, 'Accident & Emergency' FROM lk_discipline WHERE name ='Family Medicine';

