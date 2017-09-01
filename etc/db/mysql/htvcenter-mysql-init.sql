# initializes the htvcenter db

create database htvcenter_DB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci; 
use htvcenter_DB;


# resource table
create table resource_info(
	resource_id BIGINT NOT NULL PRIMARY KEY,
	resource_localboot INT(1),
	resource_kernel VARCHAR(50),
	resource_kernelid BIGINT(3),
	resource_image VARCHAR(50),
	resource_imageid BIGINT,
	resource_htvcenterserver VARCHAR(20),
	resource_basedir VARCHAR(100),
	resource_applianceid BIGINT,
	resource_ip VARCHAR(20),
	resource_subnet VARCHAR(20),
	resource_broadcast VARCHAR(20),
	resource_network VARCHAR(20),
	resource_mac VARCHAR(20),
	resource_nics INT(2),
	resource_uptime BIGINT(10),
	resource_cpunumber INT(2),
	resource_cpuspeed BIGINT(10),
	resource_cpumodel VARCHAR(255),
	resource_memtotal BIGINT(10),
	resource_memused BIGINT(10),
	resource_swaptotal BIGINT(10),
	resource_swapused BIGINT(10),
	resource_hostname VARCHAR(60),
	resource_vtype BIGINT,
	resource_vhostid BIGINT,
	resource_vname VARCHAR(255),
	resource_vnc VARCHAR(30),
	resource_load DOUBLE(3,2),
	resource_execdport BIGINT,
	resource_senddelay INT(3),
	resource_capabilities VARCHAR(255),
	resource_lastgood VARCHAR(10),
	resource_state VARCHAR(20),
	resource_event VARCHAR(20)
);


# kernel table
create table kernel_info(
	kernel_id BIGINT NOT NULL PRIMARY KEY,
	kernel_name VARCHAR(255),
	kernel_version VARCHAR(50),
	kernel_capabilities VARCHAR(255),
	kernel_comment VARCHAR(255)
);


# image table
create table image_info(
	image_id BIGINT NOT NULL PRIMARY KEY,
	image_name VARCHAR(255),
	image_version VARCHAR(30),
	# can be : ramdisk, nfs, local, iscsi
	image_type VARCHAR(255),
	# can be : ram, /dev/hdX, /dev/sdX, nfs, iscsi
	image_rootdevice VARCHAR(255),
	# can be : ext2/3, nfs
	image_rootfstype VARCHAR(255),
	image_storageid BIGINT,
	# freetext parameter for the deployment plugin
	image_deployment_parameter VARCHAR(255),
	image_isshared INT(1),
	image_isactive INT(1),
	image_comment VARCHAR(255),
	image_capabilities VARCHAR(255)
);


# appliance table
create table appliance_info(
	appliance_id BIGINT NOT NULL PRIMARY KEY,
	appliance_name VARCHAR(50),
	appliance_kernelid BIGINT(3),
	appliance_imageid BIGINT,
	appliance_starttime BIGINT(10),
	appliance_stoptime BIGINT(10),
	appliance_cpunumber INT(2),
	appliance_cpuspeed BIGINT(10),
	appliance_cpumodel VARCHAR(255),
	appliance_memtotal BIGINT(10),
	appliance_swaptotal BIGINT(10),
	appliance_nics INT(2),
	appliance_capabilities VARCHAR(1000),
	appliance_cluster BIGINT,
	appliance_ssi BIGINT,
	appliance_resources BIGINT,
	appliance_highavailable BIGINT,
	appliance_virtual BIGINT,
	appliance_virtualization VARCHAR(20),
	appliance_virtualization_host BIGINT,
	appliance_state VARCHAR(20),
	appliance_comment VARCHAR(255),
	appliance_wizard VARCHAR(255),
	appliance_event VARCHAR(20)
);




# event table
create table event_info(
	event_id BIGINT NOT NULL PRIMARY KEY,
	event_name VARCHAR(50),
	event_time VARCHAR(50),
	event_priority INT(4),
	event_source VARCHAR(50),
	event_description VARCHAR(255),
	event_comment VARCHAR(100),
	event_capabilities VARCHAR(255),
	event_status INT(4),
	event_image_id BIGINT,
	event_resource_id BIGINT
);


create table user_info(
	user_id BIGINT NOT NULL PRIMARY KEY,
	user_name VARCHAR(20),
	user_password VARCHAR(20),
	user_gender VARCHAR(1),
	user_first_name VARCHAR(50),
	user_last_name VARCHAR(50),
	user_department VARCHAR(50),
	user_office VARCHAR(50),
	user_role BIGINT,
	user_last_update_time VARCHAR(50),
	user_description VARCHAR(255),
	user_capabilities VARCHAR(255),
	user_wizard_name VARCHAR(255),
	user_wizard_step BIGINT,
	user_wizard_id BIGINT,
	user_state VARCHAR(20),
	user_lang VARCHAR(5)
);

create table role_info(
	role_id BIGINT NOT NULL PRIMARY KEY,
	role_name VARCHAR(20),
	role_comment VARCHAR(255)
);


create table storage_info(
	storage_id BIGINT NOT NULL PRIMARY KEY,
	storage_name VARCHAR(255),
	storage_resource_id BIGINT,
	storage_type BIGINT,
	storage_comment VARCHAR(100),
	storage_capabilities VARCHAR(255),
	storage_state VARCHAR(20)
);


create table resource_service (
	resource_id BIGINT NOT NULL PRIMARY KEY,
	service VARCHAR(50) NOT NULL,
	INDEX(service)
);

create table image_service (
	image_id BIGINT NOT NULL PRIMARY KEY,
	service VARCHAR(50) NOT NULL,
	INDEX(service)
);


# image_authentication table
create table image_authentication_info(
	ia_id BIGINT NOT NULL PRIMARY KEY,
	ia_image_id BIGINT,
	ia_resource_id BIGINT,
	ia_auth_type BIGINT
);

# storage_authentication_blocker table
create table auth_blocker_info(
	ab_id BIGINT NOT NULL PRIMARY KEY,
	ab_image_id BIGINT,
	ab_image_name VARCHAR(255),
	ab_start_time VARCHAR(20)
);

# plugg-able deployment types
create table deployment_info(
	deployment_id BIGINT NOT NULL PRIMARY KEY,
	deployment_name VARCHAR(50),
	deployment_type VARCHAR(50),
	deployment_description VARCHAR(50),
	deployment_storagetype VARCHAR(50),
	deployment_storagedescription VARCHAR(50),
	deployment_mapping VARCHAR(255)
);

# plugg-able virtualization types
create table virtualization_info(
	virtualization_id BIGINT NOT NULL PRIMARY KEY,
	virtualization_name VARCHAR(50),
	virtualization_type VARCHAR(50),
	virtualization_mapping VARCHAR(255)
);

# datacenter statistics
create table datacenter_info(
	datacenter_id BIGINT NOT NULL PRIMARY KEY,
	datacenter_load_overall VARCHAR(50),
	datacenter_load_server VARCHAR(50),
	datacenter_load_storage VARCHAR(50),
	datacenter_cpu_total VARCHAR(50),
	datacenter_mem_total VARCHAR(50),
	datacenter_mem_used VARCHAR(50)
);

# global lock
create table lock_info(
	lock_id BIGINT NOT NULL PRIMARY KEY,
	lock_time VARCHAR(50),
	lock_section VARCHAR(50),
	lock_resource_id BIGINT,
	lock_token VARCHAR(50),
	lock_description VARCHAR(255)
);

create table callendar_rules(
	id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	server_id VARCHAR(64),
	action VARCHAR(32),
	date VARCHAR(64),
	time VARCHAR(32)
);

create table callendar_volgroup_rules(
	id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	volgroup VARCHAR(128),
	lvol VARCHAR(128),
	name VARCHAR(128),
	action VARCHAR(128),
	storage_id VARCHAR(128),
	date VARCHAR(64),
	time VARCHAR(32),
	res_id varchar(128)
);

create table cloud_volumes(
	id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	instance_name VARCHAR(256),
	volume_name VARCHAR(256),
	size BIGINT,
	type VARCHAR(128),
	user_name VARCHAR(256),
	ccu VARCHAR(64)
);


create table cloud_price_limits(
	id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(256),
	date_start VARCHAR(256),
	date_end VARCHAR(256),
	cpu INTEGER,
	memory INTEGER,
	storage INTEGER,
	network INTEGER,
	vm BIGINT,
	user VARCHAR(256)
);


create table cloud_price_alert(
	id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	percent INTEGER,
	budget_id BIGINT,
	user VARCHAR(256)
);





# todolist


#create table todolist(
#  id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
#  task varchar(512)
#);

CREATE TABLE IF NOT EXISTS `todolist` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `task` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;


# INSERT INTO `todolist` (`id`, `task`) VALUES (0, 'htvcenter todo');


# initial data
insert into kernel_info (kernel_id, kernel_name, kernel_version) values ('0', 'htvcenter', 'htvcenter');
insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_isshared) values ('0', 'htvcenter', 'htvcenter', 'ram', 'ram', '0');

insert into image_info (image_id, image_name, image_version, image_type, image_rootdevice, image_rootfstype, image_isshared) values ('1', 'idle', 'htvcenter', 'ram', 'ram', 'ext2', '1');
insert into resource_info (resource_id, resource_localboot, resource_kernel, resource_image, resource_htvcenterserver, resource_ip, resource_vtype) values ('0', '1', 'local', 'local', 'htvcenter_SERVER_IP_ADDRESS', 'htvcenter_SERVER_IP_ADDRESS', '1');
# base deployment type ram
insert into deployment_info (deployment_id, deployment_name, deployment_type, deployment_description, deployment_storagetype, deployment_storagedescription ) values (1, 'ramdisk', 'ram', 'Ramdisk Deployment', 'none', 'none');
# base virtualization type physical
insert into virtualization_info (virtualization_id, virtualization_name, virtualization_type ) values (1, 'Physical System', 'physical');
# user htvcenter
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (0, 'htvcenter', 'htvcenter', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated', 'en');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (1, 'htvcenter', 'htvcenter', '-', '-', '-', '-', '-', 0, '-', 'default admin user', '', 'activated', 'en');
insert into user_info (user_id, user_name, user_password, user_gender, user_first_name, user_last_name, user_department, user_office, user_role, user_last_update_time, user_description, user_capabilities, user_state, user_lang) values (2, 'anonymous', 'htvcenter', '-', '-', '-', '-', '-', 1, '-', 'default readonly user', '', 'activated', 'en');
insert into role_info (role_id, role_name) values (0, 'administrator');
insert into role_info (role_id, role_name) values (1, 'readonly');

insert into appliance_info (appliance_id, appliance_name, appliance_kernelid, appliance_imageid, appliance_starttime, appliance_resources, appliance_virtualization, appliance_state, appliance_comment) values (1, 'htvcenter', 0, 0, '10', 0, 1, 'active', 'htvcenter server');

