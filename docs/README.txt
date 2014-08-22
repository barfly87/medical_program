Compass website structure overview
==================================

compass
+--application						//main application folder
   +--common                        //Common files used by all default app & all modules
      +--helpers                    //Helper files used by all views
      +--views
   +--default
      +--controllers
      +--forms
      +--models
      +--services
      +--views
         +--layouts                 //website layout
         +--scripts                 //view files, individual folder for each controller
   +--modules						//additional modules
      +--module1
         +--controllers
         +--models
         +--views
      +--module2
         +--controllers
         +--models
         +--views
   +--bootstrap.php					//bootstrap file
   +--config.ini					//config file
+--db								//database scripts
+--docs								//documentation about compass
+--htdocs							//document root
   +--css							//stylesheets
   +--img							//images
   +--js							//custom javascript and/or prototype/dojo/yahoo library
   +--index.php						//call the bootstrap file
   +--.htaccess						//optional, not necessary if rewrite rule is added to apache conf
+--lib
   +--Compass						//custom library
   +--php-ofc-library				//php drawing tool
   +--standardanalyzer-1.0.0b		//Lucene analyzer
   +--Zend							//Zend framework
+--tests							//test cases
   +--controllers					//test cases for controllers
   +--models						//test cases for models
+--var
   +--cache							//folder for mediabank, user id, ldap user cache
   +--log							//log files for compass
   +--search_index					//lucene index files
   +--search_index_old				//old lucene index files



How to set up compass:

==============================
Server requirements
==============================
1) Apache web server
2) Postgres database
3) Php 5 or above and the following extensions using yum install
   +-- php-ldap
   +-- php-pgsql
   +-- php-dom
   +-- php-soap
   +-- php-mbstring
   +-- php-curl
   +-- php-iconv


==============================
Library
==============================
1) Zend framework 1.7


==============================
Installation
==============================
1) Modify apache conf to allow rewrite, allowoverride #<Directory "/var/www/html"> AllowOverride None -> All #RewriteEngine on LoadModule rewrite_module modules/mod_rewrite.so #HEALTHCHECK /compass/admin/healthcheck
2) Check out compass from CVS
3) Unpack the Zend folder from the Zend framework download into the lib folder
4) Put php-ofc-library, standardanalyzer-1.0.0b in the lib folder
5) Create the following folders and files within compass folder
   +--var (folder)
     +--cache (folder)
     +--log (folder)
       +--log.txt (file)
     +--search_index (folder)
   Change the permission to apache.apache (or _www on a mac)
6) Change owner of folder compass/htdocs/img/noimage to apache.apache (or _www on a mac)
7) If running on a live server, modify index.php in htdocs folder to use "production"
8) If the following rewrite rule is enabled in apache conf, delete the .htacess from htdocs folder

RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php/$1

9) Set up database:
	Ensure you have a working PostgreSQL server, and have created a user with access to create databases in that server, and import the sql scripts in the db/ folder:
	
cd compass
createdb -U web compass
psql -U web compass < db/common/schema_compass_roles.sql
psql -U web compass < db/common/schema_course_structure.sql
psql -U web compass < db/common/schema_curriculum_data.sql
psql -U web compass < db/common/schema_lookup_table.sql
psql -U web compass < db/common/schema_mesh_descriptor.sql
psql -U web compass < db/common/schema_user_related_data.sql
psql -U web compass < db/common/data_lookup_table.sql
psql -U web compass < db/common/data_mesh_descriptor.sql
Then customise the course structure - take the files in db/usyd as an example, make the relevant modifications for your course structure, and import them.

	
10) (Optional) Install mesh crawler
   Following instructions on this page: http://metamap.nlm.nih.gov/#Installation 
11) Update config.ini in application folder to use the correct path

==============================
Mediabank   http://wiki.med.usyd.edu.au/mediabank/index.php/Install_Guide
==============================

Mediabank.conf file should have this xml snippet. The latest data can be got from live mediabank.conf used by compass

----------------------------------------------- START ------------------------------------------------- 
	<collection>
		<collection-id>compassresources</collection-id>
		<description>Resources for Compass.</description>
		<object-connector>
			<class>org.burnit.mepository.connectors.FileOnDiskConnector</class>
			<directory>/Users/ksoni/mediabank/repository</directory>
			<file-extensions>
				<ext>jpg</ext>
				<ext>jpeg</ext>
				<ext>mpg</ext>
				<ext>pdf</ext>
				<ext>png</ext>
				<ext>mov</ext>
				<ext>qt</ext>
				<ext>avi</ext>
				<ext>zip</ext>
				<ext>mp4</ext>
				<ext>mp3</ext>
				<ext>mp2</ext>
				<ext>mpga</ext>
				<ext>wav</ext>
				<ext>gz</ext>
				<ext>txt</ext>
				<ext>asc</ext>
				<ext>text</ext>
				<ext>bin</ext>
				<ext>flv</ext>
				<ext>doc</ext>
				<ext>xls</ext>
			</file-extensions>
		</object-connector>
		<metadata-connector>
			<class>org.burnit.mepository.connectors.JDBCConnector</class>
			<jdbc-driver-class>org.postgresql.Driver</jdbc-driver-class>
			<jdbc-connect-string>jdbc:postgresql:compassresources</jdbc-connect-string>
			<jdbc-connect-user>postgres</jdbc-connect-user>
			<jdbc-connect-password>postgres</jdbc-connect-password>
			<id-primary/>
			<table>
				<name>compassresource</name>
				<primary-key>id</primary-key>
				<primary-key-generator>select nextval('compassresource_seq')</primary-key-generator>
			</table>
		</metadata-connector>
		<acl>
			<ace>
				<usermatch>
					<frontend>REST</frontend>
				</usermatch>
				<right>add</right>
				<rule>allow</rule>
			</ace>
			<ace>
				<usermatch>
					<frontend>compass</frontend>
				</usermatch>
				<right>add</right>
				<rule>allow</rule>
			</ace>
			<ace>
				<right>add</right>
				<rule>allow</rule>
			</ace>
			<ace>
				<usermatch>
					<frontend>REST</frontend>
				</usermatch>
				<right>update</right>
				<rule>allow</rule>
			</ace>
			<ace>
				<usermatch>
					<frontend>compass</frontend>
				</usermatch>
				<right>update</right>
				<rule>allow</rule>
			</ace>
			<ace>
				<right>update</right>
				<rule>allow</rule>
			</ace>
		</acl>
	</collection>
------------------------------------------------ END ------------------------------------------------ 

etc/sample-context.xml or the the META-INF/context.xml should have below content for it to be able to connect to the database 
so that it can store metadata

----------------------------------------------- START ------------------------------------------------- 
<?xml version="1.0" encoding="UTF-8"?>
<Context path="/mediabank">
       <Resource name="jdbc/compassresources" auth="Container"
            type="javax.sql.DataSource"
            driverClassName="org.postgresql.Driver"
            url="jdbc:postgresql:compassresources"
            username="postgres"
            password="postgres"
            maxActive="100"
            maxIdle="50"
            maxWait="20000"
            removeAbandoned="true"
            removeAbandonedTimeout="60"
            logAbandoned="true"
            validationQuery="SELECT 1"
            testOnBorrow="true"
            />
</Context>
------------------------------------------------ END ------------------------------------------------  

Create database and table in postgres on the server where Mediabank is deployed as below

----------------------------------------------- START ------------------------------------------------- 
CREATE DATABASE compassresources;
CREATE TABLE compassresource (
    id serial,
    title character varying(255),
    description text,
    copyright text,
    creator character varying(255),
    status character varying(8)
);

------------------------------------------------ END ------------------------------------------------ 

