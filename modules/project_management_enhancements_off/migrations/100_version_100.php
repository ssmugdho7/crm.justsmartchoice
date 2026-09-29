<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_100 extends App_module_migration {
	public function up()
	{
		$this->ci->db->query(
			'CREATE TABLE IF NOT EXISTS '.db_prefix().'task_types (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(50) NOT NULL,
            `label_color` VARCHAR(10) DEFAULT NULL,
            `sort_order` INT DEFAULT 0,
            `editable` TINYINT(1) DEFAULT 1,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
		);

		$this->ci->db->query(
			'CREATE TABLE IF NOT EXISTS '.db_prefix().'project_task_types (
            `id` INT NOT NULL AUTO_INCREMENT,
            `project_id` INT NOT NULL,
            `task_type_id` INT NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (project_id) REFERENCES `'.db_prefix().'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (task_type_id) REFERENCES `'.db_prefix().'task_types`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;'
		);

		$this->ci->db->query(
			'ALTER TABLE '.db_prefix(
			).'project_task_types ADD UNIQUE `unique_project_task_type`(`project_id`, `task_type_id`)'
		);

		$this->ci->db->query(
			"INSERT INTO ".db_prefix()."task_types (`name`, `label_color`, `sort_order`, `editable`) VALUES
            ('Bug', '#FF5861', 1, 0),
            ('Feature', '#00B6FF', 2, 0),
            ('Task','#00A96E', 3, 0);"
		);

		$default_types = $this->ci->db->query('SELECT id FROM '.db_prefix().'task_types')->result_array();
		$projects = $this->ci->db->query('SELECT id FROM '.db_prefix().'projects')->result_array();

		foreach ($projects as $project)
		{
			foreach ($default_types as $type)
			{
				$query = sprintf(
					"INSERT INTO %sproject_task_types (`project_id`, `task_type_id`) VALUES (%s,%s)",
					db_prefix(),
					$project['id'],
					$type['id']
				);

				$this->ci->db->query($query);
			}
		}

		$this->ci->db->query('ALTER TABLE '.db_prefix().'tasks ADD `task_type` INT NULL');
		$this->ci->db->query(
			'ALTER TABLE '.db_prefix().'tasks ADD CONSTRAINT FOREIGN KEY(task_type) REFERENCES '.db_prefix(
			).'project_task_types(id) ON DELETE SET NULL'
		);
	}

	public function down()
	{
		$this->ci->db->query('ALTER TABLE '.db_prefix().'tasks DROP FOREIGN KEY `tbltasks_ibfk_1`');

		$this->ci->db->query('ALTER TABLE '.db_prefix().'tasks DROP COLUMN `task_type`');

		$this->ci->db->query(
			"DROP TABLE if EXISTS ".db_prefix()."project_task_types;"
		);

		$this->ci->db->query(
			"DROP TABLE if EXISTS ".db_prefix()."task_types;"
		);
	}
}