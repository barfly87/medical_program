BEGIN;
INSERT INTO lk_discipline VALUES (DEFAULT, '', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'Anatomy', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'EMBRYOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'Anatomy'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'HISTOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'Anatomy'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'BEHAVIORAL SCIENCES', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'BIO-ETHICS', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'CLINICAL BIOCHEMISTRY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'CLINICAL MICROBIOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'COMMUNITY MEDICINE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'BIOSTATISTICS', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'COMMUNITY MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'ENVIRONMENTAL HEALTH', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'COMMUNITY MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'EPIDEMIOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'COMMUNITY MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'OCCUPATIONAL MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'COMMUNITY MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'PREVENTIVE MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'COMMUNITY MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'CRITICAL CARE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'ACUTE SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'CRITICAL CARE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'ANAESTHESIA', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'CRITICAL CARE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'EMERGENCY MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'CRITICAL CARE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'INTENSIVE CARE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'CRITICAL CARE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'DERMATOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'EAR, NOSE & THROAT', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'FAMILY MEDICINE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'FORENSIC MEDICINE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'GENETICS', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'IMAGING', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'NUCLEAR MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'IMAGING'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'RADIOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'IMAGING'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'INTERDISCIPLINARY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'INTERNAL MEDICINE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'CARDIOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'ENDOCRINOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'GASTROENTEROLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'GERIATRIC MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'HAEMATOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'HEPATOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'IMMUNOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'INFECTIOUS DISEASES', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'NEPHROLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'NEUROLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'ONCOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'PULMONOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'RHEUMATOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'SLEEP MEDICINE', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'INTERNAL MEDICINE'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'MEDICAL PROFESSIONALISM', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'OBSTETRICS & GYNAECOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'OPHTHALMOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PAEDIATRICS', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PALLIATIVE CARE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PATHOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PHARMACOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PHYSIOLOGY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'PSYCHIATRY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'REHABILITATION MEDICINE', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'SURGERY', NULL, 1, 0, 0);
INSERT INTO lk_discipline VALUES (DEFAULT, 'BBREAST & ENDOCRINE SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'CARDIOTHORACIC SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'COLORECTAL SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'HEAD & NECK SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'NEUROSURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'ORTHOPAEDIC SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'PAEDIATRIC SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'PLASTIC SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'SURGICAL ONCOLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'UPPER GI & HEPATOBILIARY SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'UROLOGY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
INSERT INTO lk_discipline VALUES (DEFAULT, 'VASCULAR SURGERY', NULL, 1, 0, (SELECT auto_id FROM lk_discipline WHERE name = 'SURGERY'));
COMMIT;
