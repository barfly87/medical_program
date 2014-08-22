CREATE TABLE lk_assesstype (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_achievement (
  auto_id SERIAL,
  name varchar(32),
  description  VARCHAR(128),
  memostr VARCHAR(255),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_gradattrib (
  auto_id SERIAL,
  name varchar(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_jmo (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_strength (
  auto_id SERIAL,
  name VARCHAR(32),
  description VARCHAR(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_review (
  auto_id SERIAL,
  name VARCHAR(128),
  memostr VARCHAR(255),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_status (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);

CREATE TABLE lk_staffpage (
	auto_id SERIAL,
	name character varying(64),
	PRIMARY KEY (auto_id)
);

CREATE TABLE lk_stafftype (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_studentgroup (
  auto_id SERIAL,
  name varchar(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_skill (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_system (
  auto_id SERIAL,
  name varchar(128),
  seq_no INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_activitytype (
  auto_id SERIAL,
  name  VARCHAR(64),
  importance  INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_sequence_num (
  auto_id SERIAL,
  seqnum  INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_term (
    auto_id SERIAL,
    term char(3),
    PRIMARY KEY (auto_id)
);


CREATE TABLE lk_resourcetype  (
    auto_id SERIAL,
    resource_type VARCHAR,
    url_name VARCHAR,
    allow varchar(80) DEFAULT 'admin',
    pbl INT,
    block INT,
    ta INT,
    lo INT,
    PRIMARY KEY(auto_id)
);


CREATE TABLE lk_curriculumareas (
  auto_id SERIAL,
  discipline_id Integer,
  curriculumarea VARCHAR(255),
  order_by Integer,
  status INT DEFAULT 1,
  PRIMARY KEY (auto_id)   
);


CREATE TABLE lk_curriculumareas_status (
    auto_id SERIAL,
    name VARCHAR(80),
    primary key (auto_id)
);


CREATE TABLE lk_discipline (
  auto_id SERIAL,
  name VARCHAR(128),
  synonym VARCHAR(128),
  compass INT,
  org INT,
  parent_id INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE clinical_school (
    auto_id SERIAL,
    name character varying(256),
    primary key(auto_id)
);


CREATE TABLE lk_theme (
  auto_id SERIAL,
  name VARCHAR(255),
  memostr VARCHAR(255),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_domain (
    auto_id SERIAL,
    name character varying(255),
    PRIMARY KEY (auto_id)
);


CREATE TABLE lk_studentresourcecategories (
  auto_id SERIAL,
  name varchar(32),
  seq_no int,
  description  VARCHAR(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_ratingcategories (
  auto_id SERIAL,
  name varchar(32),
  rating int,
  description VARCHAR(128),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_loscope (
  auto_id SERIAL,
  name varchar(255),
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_loverb (
  auto_id SERIAL,
  name varchar(255),
  PRIMARY KEY (auto_id)
);


-- Used for tabuk uni only
CREATE TABLE lk_year (
  auto_id SERIAL,
  year  INT,
  PRIMARY KEY (auto_id)
);


CREATE TABLE lk_pblroom (
  auto_id SERIAL,
  groupname varchar(32),
  room varchar(255),
  PRIMARY KEY (auto_id)
);

-- Tables that are not being used anymore
-- lk_facilities
-- lk_selectgroup
-- lk_site
-- lk_venue
-- resource
