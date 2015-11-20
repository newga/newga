<?php

class m151120_105342_initial_db extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{

$this->execute("

DROP TABLE IF EXISTS `accession`;

CREATE TABLE `accession` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) unsigned DEFAULT NULL,
  `store2contact_id` int(11) unsigned DEFAULT NULL,
  `original_store2contact_id` int(11) unsigned DEFAULT NULL,
  `terms_agreed` datetime DEFAULT NULL,
  `accession_hash` varchar(40) DEFAULT NULL,
  `children` int(11) unsigned DEFAULT '0',
  `child_ages` varchar(255) DEFAULT NULL,
  `cs_answers` varchar(255) DEFAULT NULL,
  `culture_segment` varchar(30) DEFAULT NULL,
  `level_of_engagement` varchar(30) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `complete` int(11) DEFAULT '0',
  `password` varchar(64) DEFAULT NULL,
  `reset_hash` varchar(64) DEFAULT NULL,
  `invite_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `accession_ibfk_2` (`store2contact_id`),
  CONSTRAINT `accession_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accession_ibfk_2` FOREIGN KEY (`store2contact_id`) REFERENCES `store2contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table archive_accession
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `archive_accession`;

CREATE TABLE `archive_accession` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) unsigned DEFAULT NULL,
  `store2contact_id` int(11) unsigned DEFAULT NULL,
  `original_store2contact_id` int(11) unsigned DEFAULT NULL,
  `terms_agreed` datetime DEFAULT NULL,
  `accession_hash` varchar(40) DEFAULT NULL,
  `children` int(11) unsigned DEFAULT '0',
  `child_ages` varchar(255) DEFAULT NULL,
  `cs_answers` varchar(255) DEFAULT NULL,
  `culture_segment` varchar(30) DEFAULT NULL,
  `level_of_engagement` varchar(30) DEFAULT NULL,
  `step` int(11) DEFAULT NULL,
  `complete` int(11) DEFAULT '0',
  `password` varchar(64) DEFAULT NULL,
  `reset_hash` varchar(64) DEFAULT NULL,
  `invite_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table archive_contact2artform
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `archive_contact2artform`;

CREATE TABLE `archive_contact2artform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accession_id` int(11) unsigned DEFAULT NULL,
  `artform_id` int(11) unsigned DEFAULT NULL,
  `visited` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table archive_contact2venue
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `archive_contact2venue`;

CREATE TABLE `archive_contact2venue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accession_id` int(11) unsigned DEFAULT NULL,
  `venue_id` int(11) unsigned DEFAULT NULL,
  `visited` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table archive_invite
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `archive_invite`;

CREATE TABLE `archive_invite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `contact_warehouse_id` int(11) unsigned NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `store2contact_id` int(11) unsigned NOT NULL,
  `correct_store2contact_id` int(11) unsigned DEFAULT NULL,
  `migrated` int(11) unsigned DEFAULT '0',
  `query_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `hash` varchar(40) NOT NULL DEFAULT '',
  `date` datetime NOT NULL,
  `status` int(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table artform
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `artform`;

CREATE TABLE `artform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `artform` WRITE;
/*!40000 ALTER TABLE `artform` DISABLE KEYS */;

INSERT INTO `artform` (`id`, `title`)
VALUES
	(1,'Example Artform');

/*!40000 ALTER TABLE `artform` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table campaign
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign`;

CREATE TABLE `campaign` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `query_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `size` int(11) unsigned DEFAULT NULL,
  `hash` varchar(6) DEFAULT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `processing` int(11) unsigned NOT NULL DEFAULT '0',
  `creator_id` int(11) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `invite_email_subject` varchar(255) DEFAULT NULL,
  `invite_email_body` text,
  `type` int(10) unsigned DEFAULT NULL,
  `date_run` datetime DEFAULT NULL,
  `json` text,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_1` (`query_id`),
  KEY `fk_campaign_2` (`creator_id`),
  CONSTRAINT `fk_campaign_1` FOREIGN KEY (`query_id`) REFERENCES `query` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_campaign_2` FOREIGN KEY (`creator_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table campaign_contact
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign_contact`;

CREATE TABLE `campaign_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `warehouse_id` int(11) unsigned NOT NULL,
  `processing` int(11) unsigned NOT NULL DEFAULT '0',
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `hash` varchar(6) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `opened` datetime DEFAULT NULL,
  `bounced` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_contact_1` (`campaign_id`),
  KEY `fk_campaign_contact_2` (`group_id`),
  KEY `warehouse_id` (`warehouse_id`),
  CONSTRAINT `fk_campaign_contact_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_campaign_contact_2` FOREIGN KEY (`group_id`) REFERENCES `campaign_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table campaign_contact2outcome
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign_contact2outcome`;

CREATE TABLE `campaign_contact2outcome` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_contact_id` int(11) unsigned NOT NULL,
  `campaign_outcome_id` int(11) unsigned NOT NULL,
  `hash` varchar(8) DEFAULT NULL,
  `outcome` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_contact_id` (`campaign_contact_id`),
  KEY `campaign_outcome_id` (`campaign_outcome_id`),
  CONSTRAINT `campaign_contact2outcome_ibfk_1` FOREIGN KEY (`campaign_contact_id`) REFERENCES `campaign_contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `campaign_contact2outcome_ibfk_2` FOREIGN KEY (`campaign_outcome_id`) REFERENCES `campaign_outcome` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table campaign_file
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign_file`;

CREATE TABLE `campaign_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) unsigned NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `secret` varchar(6) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `uploaded_by` int(11) unsigned NOT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_file_1` (`campaign_id`),
  CONSTRAINT `fk_campaign_file_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table campaign_group
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign_group`;

CREATE TABLE `campaign_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `fraction` float(5,2) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_group_1` (`campaign_id`),
  CONSTRAINT `fk_campaign_group_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table campaign_outcome
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `campaign_outcome`;

CREATE TABLE `campaign_outcome` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `url` text,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_group_outcome_1` (`campaign_id`),
  CONSTRAINT `fk_campaign_outcome_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table config
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) DEFAULT NULL,
  `value` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;

INSERT INTO `config` (`id`, `key`, `value`, `created`)
VALUES
	(1,'host','example.com','2015-01-23 10:29:51'),
	(2,'https','0','2015-01-23 10:29:51');

/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table contact2artform
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `contact2artform`;

CREATE TABLE `contact2artform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accession_id` int(11) unsigned DEFAULT NULL,
  `artform_id` int(11) unsigned DEFAULT NULL,
  `visited` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accession_id` (`accession_id`),
  CONSTRAINT `contact2artform_ibfk_1` FOREIGN KEY (`accession_id`) REFERENCES `accession` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table contact2venue
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `contact2venue`;

CREATE TABLE `contact2venue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `accession_id` int(11) unsigned DEFAULT NULL,
  `venue_id` int(11) unsigned DEFAULT NULL,
  `visited` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accession_id` (`accession_id`),
  CONSTRAINT `contact2venue_ibfk_1` FOREIGN KEY (`accession_id`) REFERENCES `accession` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table csv_cleaning_file
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `csv_cleaning_file`;

CREATE TABLE `csv_cleaning_file` (
  `uuid` varchar(13) NOT NULL,
  `created` datetime NOT NULL,
  `status` int(1) DEFAULT '0',
  `import_date` datetime DEFAULT NULL,
  PRIMARY KEY (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table csv_file
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `csv_file`;

CREATE TABLE `csv_file` (
  `uuid` varchar(13) NOT NULL,
  `created` date NOT NULL,
  `organisation_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uuid`),
  KEY `fk_CsvFile_organisation1_idx` (`organisation_id`),
  CONSTRAINT `fk_CsvFile_organisation1` FOREIGN KEY (`organisation_id`) REFERENCES `organisation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table culture_segment
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `culture_segment`;

CREATE TABLE `culture_segment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `culture_segment` WRITE;
/*!40000 ALTER TABLE `culture_segment` DISABLE KEYS */;

INSERT INTO `culture_segment` (`id`, `name`)
VALUES
	(1,'Example Segment');

/*!40000 ALTER TABLE `culture_segment` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table email_template
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `email_template`;

CREATE TABLE `email_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `html` text NOT NULL,
  `folder` varchar(13) NOT NULL DEFAULT '',
  `created` datetime NOT NULL,
  `images` text,
  `campaign_group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_group_id` (`campaign_group_id`),
  CONSTRAINT `email_template_ibfk_1` FOREIGN KEY (`campaign_group_id`) REFERENCES `campaign_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table import_pointer
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `import_pointer`;

CREATE TABLE `import_pointer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pointer` int(11) NOT NULL DEFAULT '0',
  `processing` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `import_pointer` WRITE;
/*!40000 ALTER TABLE `import_pointer` DISABLE KEYS */;

INSERT INTO `import_pointer` (`id`, `pointer`, `processing`)
VALUES
	(1,1,0);

/*!40000 ALTER TABLE `import_pointer` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table invite
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `invite`;

CREATE TABLE `invite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `contact_warehouse_id` int(11) unsigned NOT NULL,
  `organisation_id` int(11) NOT NULL,
  `store2contact_id` int(11) unsigned NOT NULL,
  `store_id` int(11) unsigned DEFAULT NULL,
  `query_id` int(11) DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `hash` varchar(40) NOT NULL DEFAULT '',
  `date` datetime NOT NULL,
  `status` int(11) unsigned NOT NULL DEFAULT '0',
  `processing` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store2contact_id` (`store2contact_id`),
  KEY `store_id` (`store_id`),
  KEY `accession_ibfk_3` (`contact_warehouse_id`),
  CONSTRAINT `accession_ibfk_3` FOREIGN KEY (`contact_warehouse_id`) REFERENCES `store2contact` (`contact_warehouse_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invite_ibfk_1` FOREIGN KEY (`store2contact_id`) REFERENCES `store2contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `invite_ibfk_2` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table ip_ban
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `ip_ban`;

CREATE TABLE `ip_ban` (
  `ip_ban_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ip_ban_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table login
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `login`;

CREATE TABLE `login` (
  `login_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `success` tinyint(4) NOT NULL DEFAULT '0',
  `date` datetime DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`login_id`),
  KEY `fk_login_user_idx` (`user_id`),
  CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table organisation
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `organisation`;

CREATE TABLE `organisation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(4) DEFAULT '1',
  `view_name` varchar(45) NOT NULL,
  `email_template` varchar(100) DEFAULT NULL,
  `email_domain` varchar(100) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `organisation` WRITE;
/*!40000 ALTER TABLE `organisation` DISABLE KEYS */;

INSERT INTO `organisation` (`id`, `title`, `active`, `view_name`, `email_template`, `email_domain`, `email_address`)
VALUES
	(1,'Example Organisation',1,'View_Organisation','organisation','example.com','email@example.com'),
	(10,'Application Name',1,'Store',NULL,NULL,NULL);

/*!40000 ALTER TABLE `organisation` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table query
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `query`;

CREATE TABLE `query` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `JSON` text,
  `description` text,
  `created` datetime DEFAULT NULL,
  `num_contacts` int(11) DEFAULT '0',
  `invite` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_query_user_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table query_question
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `query_question`;

CREATE TABLE `query_question` (
  `id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1=Contact, 2 =Accession, 3= Campaigns',
  `lang_type` tinyint(1) NOT NULL COMMENT '1=existential,2=possesive,3=response',
  `has_value` int(1) DEFAULT '1',
  `question` varchar(100) NOT NULL DEFAULT '',
  `field_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `query_question` WRITE;
/*!40000 ALTER TABLE `query_question` DISABLE KEYS */;

INSERT INTO `query_question` (`id`, `option_id`, `type`, `lang_type`, `has_value`, `question`, `field_name`)
VALUES
	(3,NULL,1,1,1,'younger than','dob'),
	(4,NULL,1,1,1,'older than','dob'),
	(5,6,2,1,1,'in Segment','culture_segment'),
	(6,NULL,1,2,0,'an email address','email'),
	(7,NULL,1,2,0,'a phone number','phone'),
	(8,NULL,1,2,0,'an SMS number','mobile'),
	(9,NULL,1,2,0,'a postal address','address_line_1'),
	(10,7,2,2,1,'a level of engagement of','level_of_engagement'),
	(11,3,2,3,1,'visited venue',NULL),
	(12,3,2,3,1,'visited venue last 3 yrs',NULL),
	(13,3,2,3,1,'never visited venue but would',NULL),
	(14,3,2,3,1,'never visited venue & won\'t',NULL),
	(15,8,3,1,1,'part of the campaign',NULL),
	(16,2,2,2,1,'an origin organisation of','origin_organisation_id'),
	(17,NULL,2,1,0,'part of Application Name','terms_agreed'),
	(18,NULL,4,4,1,'Invited within the last X days','most_recent_invite_date'),
	(19,4,4,4,1,'part of invitation',NULL),
	(20,NULL,4,2,0,'any previous invites',NULL),
	(21,NULL,2,3,0,'has children','children'),
	(22,5,2,1,1,'visited artform',NULL),
	(23,5,2,1,1,'visited artform last 3 yrs',NULL),
	(24,5,2,1,1,'never visited artform but would',NULL),
	(25,5,2,1,1,'never visited artform & won\'t',NULL),
	(26,NULL,2,2,0,'completed all accession questions','accession_complete'),
	(27,9,3,2,1,'an outcome of',NULL);

/*!40000 ALTER TABLE `query_question` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table raw_import
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `raw_import`;

CREATE TABLE `raw_import` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `MASTER_CCR_ID` int(11) DEFAULT NULL,
  `CCR_ID` int(11) DEFAULT NULL,
  `CCR_Client_URN` varchar(100) DEFAULT NULL,
  `CCR_Source` int(11) DEFAULT NULL,
  `CCR_Title` varchar(200) DEFAULT NULL,
  `CCR_Forename` varchar(200) DEFAULT NULL,
  `CCR_Surname` varchar(200) DEFAULT NULL,
  `CCR_Address1` varchar(200) DEFAULT NULL,
  `CCR_Address2` varchar(200) DEFAULT NULL,
  `CCR_Address3` varchar(200) DEFAULT NULL,
  `CCR_Address4` varchar(200) DEFAULT NULL,
  `CCR_Address5` varchar(200) DEFAULT NULL,
  `CCR_Address6` varchar(200) DEFAULT NULL,
  `CCR_Town` varchar(200) DEFAULT NULL,
  `CCR_County` varchar(200) DEFAULT NULL,
  `CCR_DPS` varchar(200) DEFAULT NULL,
  `CCR_Postcode` varchar(200) DEFAULT NULL,
  `CCR_Country` varchar(200) DEFAULT NULL,
  `CCR_Phone1` varchar(100) DEFAULT NULL,
  `CCR_Phone2` varchar(100) DEFAULT NULL,
  `CCR_Email` varchar(200) DEFAULT NULL,
  `CCR_PAF` varchar(200) DEFAULT NULL,
  `CCR_Ind_Set` int(20) DEFAULT NULL,
  `CCR_Ind_Dupe1` varchar(200) DEFAULT NULL,
  `CCR_Organisation` varchar(200) DEFAULT NULL,
  `CCR_Email_Allow` varchar(200) DEFAULT NULL,
  `Cleaning_UUID` varchar(13) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table store
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `store`;

CREATE TABLE `store` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `origin_organisation_id` int(11) unsigned NOT NULL,
  `origin_unique_id` varchar(100) DEFAULT NULL,
  `salutation` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address_line_1` varchar(100) DEFAULT NULL,
  `address_line_2` varchar(100) DEFAULT NULL,
  `address_line_3` varchar(100) DEFAULT NULL,
  `address_line_4` varchar(100) DEFAULT NULL,
  `address_town` varchar(100) DEFAULT NULL,
  `address_postcode` varchar(20) DEFAULT NULL,
  `address_county` varchar(50) DEFAULT NULL,
  `mobile` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `csv_file_uuid` varchar(13) DEFAULT NULL,
  `date_imported` datetime DEFAULT NULL,
  `date_expired` datetime DEFAULT NULL,
  `contact_email` tinyint(4) NOT NULL DEFAULT '2',
  `contact_sms` tinyint(4) NOT NULL DEFAULT '2',
  `contact_post` tinyint(4) NOT NULL DEFAULT '2',
  `deceased` int(1) DEFAULT '0',
  `ccr_duplicate_id` int(11) DEFAULT NULL,
  `ccr_ind_dupe1` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_contact_organisation_idx` (`origin_organisation_id`),
  KEY `origin_unique_id` (`origin_unique_id`),
  KEY `ccr_duplicate_id` (`ccr_duplicate_id`),
  KEY `origin_organisation_id` (`origin_organisation_id`),
  CONSTRAINT `fk_contact_organisation` FOREIGN KEY (`origin_organisation_id`) REFERENCES `organisation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table store2contact
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `store2contact`;

CREATE TABLE `store2contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(11) unsigned DEFAULT NULL,
  `contact_warehouse_id` int(11) unsigned NOT NULL,
  `most_recent_invite_date` datetime DEFAULT NULL,
  `origin_unique_id` varchar(100) DEFAULT NULL,
  `origin_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `contact_warehouse_id` (`contact_warehouse_id`),
  KEY `origin_id` (`origin_id`),
  CONSTRAINT `store2contact_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `store2contact_ibfk_2` FOREIGN KEY (`origin_id`) REFERENCES `organisation` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `store2contact_ibfk_3` FOREIGN KEY (`contact_warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table suppression_list
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `suppression_list`;

CREATE TABLE `suppression_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) unsigned DEFAULT NULL,
  `store2contact_id` int(11) unsigned DEFAULT NULL,
  `store_id` int(11) unsigned DEFAULT NULL,
  `campaign_id` int(11) unsigned DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouse_id` (`warehouse_id`),
  KEY `store2contact_id` (`store2contact_id`),
  KEY `store_id` (`store_id`),
  CONSTRAINT `suppression_list_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `suppression_list_ibfk_2` FOREIGN KEY (`store2contact_id`) REFERENCES `store2contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `suppression_list_ibfk_3` FOREIGN KEY (`store_id`) REFERENCES `store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table user
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `reset_hash` varchar(40) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT '0',
  `verified` tinyint(4) NOT NULL DEFAULT '0',
  `mothballed` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `organisation_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;

INSERT INTO `user` (`id`, `email`, `password`, `first_name`, `last_name`, `reset_hash`, `role`, `verified`, `mothballed`, `created`, `updated`, `organisation_id`)
VALUES
	(1,'email@example.com','hpbc014JqkPbELwk4XNs1j6ROd8aN5AcSuDl2CKM1iXFKle8uw2wTzqd1nMQdmao','First','Last',NULL,0,0,0,'2013-01-01 00:00:00','2013-01-01 00:00:00',0);

/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table venue
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `venue`;

CREATE TABLE `venue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `organisation_id` int(11) unsigned NOT NULL,
  `active` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_venue_organisation_idx` (`organisation_id`),
  CONSTRAINT `fk_venue_organisation` FOREIGN KEY (`organisation_id`) REFERENCES `organisation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `venue` WRITE;
/*!40000 ALTER TABLE `venue` DISABLE KEYS */;

INSERT INTO `venue` (`id`, `title`, `organisation_id`, `active`)
VALUES
	(1,'Example Venue',1,1);

/*!40000 ALTER TABLE `venue` ENABLE KEYS */;
UNLOCK TABLES;

");

# Dump of table warehouse
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `warehouse`;

CREATE TABLE `warehouse` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

# Dump of table yii_migration
# ------------------------------------------------------------

$this->execute("

DROP TABLE IF EXISTS `yii_migration`;

CREATE TABLE `yii_migration` (
  `version` varchar(255) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	}

");

	public function safeDown()
	{
		/* initial db. No down */
		return false;
	}
	
}