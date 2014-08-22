<?php

TestConfiguration::setup();

class TestConfiguration {
    static $compassRoot;
    static $zfRoot;
    
    static function setup() {
        // Set your Zend Framework library path(s) here - default is the master lib/ directory

        $zfRoot = realpath(dirname(basename(__FILE__)) . '/../lib/');
    
        $compassRoot = realpath(dirname(basename(__FILE__)) . '/..');

        TestConfiguration::$compassRoot = $compassRoot;
        TestConfiguration::$zfRoot = $zfRoot;
    
        require_once 'PHPUnit/Framework.php';
        require_once 'PHPUnit/Framework/TestSuite.php';
        require_once 'PHPUnit/TextUI/TestRunner.php';
    
        error_reporting( E_ALL | E_STRICT );
    
        set_include_path($compassRoot . '/application/models/'
            . PATH_SEPARATOR . $zfRoot
            . PATH_SEPARATOR . get_include_path()
        );
    
        include 'Zend/Loader.php';
        Zend_Loader::registerAutoload();
    
        // load configuration
        $section = 'test';
        $config = new Zend_Config_Ini($compassRoot .'/application/config.ini', $section);
        Zend_Registry::set('config', $config);
        
        date_default_timezone_set($config->date_default_timezone);
    
        // set up database
        $db = Zend_Db::factory($config->database);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Registry::set('db', $db); 
     }

    static function setupDatabase() {
        $db = Zend_Registry::get('db');

        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_achievement;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_achievement (
  auto_id SERIAL,
  name varchar(16),
  description  VARCHAR(100),
  PRIMARY KEY (auto_id)
)
EOT
        );

        
        $db->query(<<<EOT
INSERT INTO lk_achievement VALUES(DEFAULT, 'Core', 'CORE objective that ALL students');
INSERT INTO lk_achievement VALUES(DEFAULT, 'Desired', 'DESIRED objective that expert students');
EOT
        );

        $db->query(<<<EOT
DROP TABLE IF EXISTS learningobjective;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE learningobjective (
    auto_id SERIAL,
    lo text,
    achievement integer,
    theme integer,
    skill integer,
    discipline1 integer,
    discipline2 integer,
    discipline3 integer,
    "system" integer,
    "level" integer,
    author_id character varying(32),
    date_submitted timestamp without time zone,
    modified_by character varying(32),
    date_modified timestamp without time zone,
    reviewed_by character varying(32),
    date_reviewed timestamp without time zone,
    date_next_review timestamp without time zone,
    status integer,
    taught integer,
    practiced integer,
    assessed integer,
    evaluated integer,
    jmo integer,
    gradattrib integer,
    keywords text,
    PRIMARY KEY (auto_id)
);
EOT
        );

        $db->query(<<<EOT
INSERT INTO learningobjective VALUES (DEFAULT, 'TBC access for immigrants', 1, 1, 1, 1, 1, 1, 1, 1, NULL, NULL, 'wendyhu', '2008-04-09 00:00:00', NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, NULL);
EOT
        );

        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_theme;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_theme (
  auto_id SERIAL,
  name  VARCHAR(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
        
        $db->query(<<<EOT
INSERT INTO lk_theme VALUES(DEFAULT, 'BCS');
INSERT INTO lk_theme VALUES(DEFAULT, 'PtDr');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_skill;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_skill (
  auto_id SERIAL,
  name varchar(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_skill VALUES (DEFAULT, 'Not Applicable');
INSERT INTO lk_skill VALUES (DEFAULT, 'Communication');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_discipline;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_discipline (
  auto_id SERIAL,
  disc  VARCHAR(100),
  subdisc  VARCHAR(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_discipline VALUES(DEFAULT, 'Addiction Medicine', '');
INSERT INTO lk_discipline VALUES(DEFAULT, 'Anaesthesia', '');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_level;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_level (
  auto_id SERIAL,
  name varchar(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_level VALUES (DEFAULT, 'Course');
INSERT INTO lk_level VALUES (DEFAULT, 'Discipline');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_system;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_system (
  auto_id SERIAL,
  name varchar(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_system VALUES (DEFAULT, 'Cardiovascular');
INSERT INTO lk_system VALUES (DEFAULT, 'Nervous system');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_status;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_status (
  auto_id SERIAL,
  name varchar(100),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_status VALUES (DEFAULT, 'Proposed');
INSERT INTO lk_status VALUES (DEFAULT, 'In progress');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_taught;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_taught (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_taught VALUES (DEFAULT, 'Not at all');
INSERT INTO lk_taught VALUES (DEFAULT, 'Single activity');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_practiced;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_practiced (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_practiced VALUES (DEFAULT, 'Not at all');
INSERT INTO lk_practiced VALUES (DEFAULT, 'Self directed');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_assessed;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_assessed (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_assessed VALUES (DEFAULT, 'Not at all');
INSERT INTO lk_assessed VALUES (DEFAULT, 'Formative');
EOT
        );

        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_evaluated;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_evaluated (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_evaluated VALUES (DEFAULT, 'Not at all');
INSERT INTO lk_evaluated VALUES (DEFAULT, 'Collected');
EOT
        );



        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_jmo;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_jmo (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_jmo VALUES(DEFAULT, 'Common Problems');
INSERT INTO lk_jmo VALUES(DEFAULT, 'Patient Assessment');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_gradattrib;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_gradattrib (
  auto_id SERIAL,
  name varchar(128),
  PRIMARY KEY (auto_id)
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Communication');
INSERT INTO lk_gradattrib VALUES (DEFAULT, 'Ethical, Social & Professional Understanding');
EOT
        );


        $db->query(<<<EOT
DROP TABLE IF EXISTS lk_assesstype;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE lk_assesstype (
  auto_id SERIAL,
  name varchar(64),
  PRIMARY KEY (auto_id)
);
EOT
        );



        $db->query(<<<EOT
DROP TABLE IF EXISTS link_lo_assesstype;
EOT
        );

        $db->query(<<<EOT
CREATE TABLE link_lo_assesstype (
  auto_id SERIAL,
  lo_id integer NOT NULL,
  assesstype_id integer NOT NULL,
  PRIMARY KEY (auto_id)    
);
EOT
        );
     
        $db->query(<<<EOT
INSERT INTO link_lo_assesstype VALUES (DEFAULT, 1, 1);
EOT
        );

    }
}