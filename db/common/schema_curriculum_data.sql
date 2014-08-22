CREATE TABLE teachingactivity (
  auto_id SERIAL,
  taid INT,
  owner INT DEFAULT 1,
  parent_id INT DEFAULT 0,
  name VARCHAR(255),
  type INT,
  pbl INT DEFAULT 1,
  stage INT,
  year INT DEFAULT 1,
  block INT,
  block_week INT DEFAULT 1,
  sequence_num INT DEFAULT 1,
  term INT DEFAULT 1,
  student_grp INT,
  principal_teacher VARCHAR(255),
  current_teacher VARCHAR(255),
  created_by VARCHAR(32),
  date_created timestamp,
  approved_by VARCHAR(32),
  date_approved timestamp,
  reviewed_by varchar(32),
  date_reviewed timestamp,
  notes text,
  version INT,
  PRIMARY KEY (auto_id)
);
CREATE INDEX taid ON teachingactivity (taid);
CREATE INDEX type ON teachingactivity (type);
CREATE INDEX pbl ON teachingactivity (pbl);
CREATE INDEX stage ON teachingactivity (stage);
CREATE INDEX year ON teachingactivity (year);
CREATE INDEX block ON teachingactivity (block);
CREATE INDEX block_week ON teachingactivity (block_week);
CREATE INDEX student_grp ON teachingactivity (student_grp);
CREATE INDEX ta_parent_id ON teachingactivity (parent_id);
CREATE INDEX ta_owner ON teachingactivity (owner);



CREATE TABLE learningobjective (
  auto_id SERIAL,
  loid INT,
  owner INT DEFAULT 1,
  parent_id INT DEFAULT 0,
  lo text,
  shorttitle VARCHAR(255),
  achievement INT,
  theme1 INT DEFAULT 1,
  theme2 INT DEFAULT 1,
  theme3 INT DEFAULT 1,
  skill INT,
  discipline1 INT DEFAULT 1,
  discipline2 INT DEFAULT 1,
  discipline3 INT DEFAULT 1,
  system INT,
  created_by varchar(32),
  date_created timestamp,
  approved_by varchar(32),
  date_approved timestamp,
  date_next_review timestamp,
  notes text,
  jmo INT,
  gradattrib INT,
  keywords text,
  version INT,
  curriculumarea1 INT DEFAULT 0,
  curriculumarea2 INT DEFAULT 0,
  curriculumarea3 INT DEFAULT 0,
  primary key (auto_id)
);
CREATE INDEX loid ON learningobjective (loid);
CREATE INDEX achievement ON learningobjective (achievement);
CREATE INDEX theme1 ON learningobjective (theme1);
CREATE INDEX theme2 ON learningobjective (theme2);
CREATE INDEX theme3 ON learningobjective (theme3);
CREATE INDEX skill ON learningobjective (skill);
CREATE INDEX discipline1 ON learningobjective (discipline1);
CREATE INDEX discipline2 ON learningobjective (discipline2);
CREATE INDEX discipline3 ON learningobjective (discipline3);
CREATE INDEX system ON learningobjective (system);
CREATE INDEX jmo ON learningobjective (jmo);
CREATE INDEX gradattrib ON learningobjective (gradattrib);
CREATE INDEX lo_parent_id ON learningobjective (parent_id);
CREATE INDEX lo_owner ON learningobjective (owner);



CREATE TABLE link_lo_ta (
  auto_id SERIAL,
  lo_id INT,
  ta_id INT,
  strength INT,
  notes TEXT,
  created_by VARCHAR(32),
  date_created timestamp,
  modified_by varchar(32),
  date_modified timestamp,
  approved_by varchar(32),
  date_approved timestamp,
  status INT,
  new_status INT,
  type char(2),
  lo_order INT DEFAULT 1,
  PRIMARY KEY (auto_id)
);
CREATE INDEX lo_id ON link_lo_ta (lo_id);
CREATE INDEX ta_id ON link_lo_ta (ta_id );
CREATE INDEX strength ON link_lo_ta (strength);
CREATE INDEX link_type ON link_lo_ta (type);
CREATE INDEX link_status ON link_lo_ta (status);
CREATE INDEX link_new_status ON link_lo_ta (new_status);



CREATE TABLE link_lo_ta_history (
  auto_id SERIAL,
  lo_id INT,
  ta_id INT,
  strength INT,
  notes text,
  created_by VARCHAR(32),
  date_created timestamp,
  modified_by varchar(32),
  date_modified timestamp,
  approved_by varchar(32),
  date_approved timestamp,
  status INT,
  new_status INT,
  type char(2),
  lo_order INT,
  PRIMARY KEY (auto_id)
);
CREATE INDEX history_lo_id ON link_lo_ta_history (lo_id);
CREATE INDEX history_ta_id ON link_lo_ta_history (ta_id );



CREATE TABLE link_lo_review (
  auto_id SERIAL,
  lo_id int NOT NULL,
  review_id INT NOT NULL,
  PRIMARY KEY (auto_id)
);
CREATE INDEX review_lo_id ON link_lo_review (lo_id);
CREATE INDEX review_id ON link_lo_review (review_id);



CREATE TABLE link_lo_assesstype (
  auto_id SERIAL,
  lo_id int NOT NULL,
  assesstype_id INT NOT NULL,
  PRIMARY KEY (auto_id)
);
CREATE INDEX assess_lo_id ON link_lo_assesstype (lo_id);
CREATE INDEX assess_assesstype_id ON link_lo_assesstype (assesstype_id);



CREATE TABLE lk_resource(
  auto_id SERIAL,
  type VARCHAR(128),
  type_id INT,
  resource_type VARCHAR(80),
  resource_id VARCHAR(255),
  order_by INT DEFAULT 1,
  resource_type_id INT,
  PRIMARY KEY(auto_id)
);
CREATE INDEX lk_resource_typeid_type_resourceid ON lk_resource(type_id, "type", resource_id);



CREATE TABLE lk_resource_history (
  auto_id serial,
  lk_resource_id integer,
  type varchar(128),
  type_id integer,
  resource_type varchar(80),
  resource_id varchar(255),
  resource_title varchar(255),
  uid varchar(256) NOT NULL,
  "timestamp" timestamp without time zone NOT NULL,
  action varchar(20) NOT NULL,
  PRIMARY KEY (auto_id)
);
CREATE INDEX lk_resource_history_typeid_type ON lk_resource_history(type_id,type);



CREATE TABLE link_lo_domain (
  auto_id SERIAL,
  lo_id INT,
  domain_id INT,
  primary key(auto_id)
);
CREATE INDEX link_lo_domain_loid ON link_lo_domain(lo_id);
CREATE INDEX link_lo_domain_domainid ON link_lo_domain(domain_id);



CREATE TABLE link_ta_domain (
  auto_id SERIAL,
  ta_id INT,
  domain_id INT,
  primary key(auto_id)
);
CREATE INDEX link_ta_domain_taid ON link_ta_domain(ta_id);
CREATE INDEX link_ta_domain_domainid ON link_ta_domain(domain_id);
