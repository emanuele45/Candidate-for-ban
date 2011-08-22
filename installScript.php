<?php 
// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

db_extend('packages');
$smcFunc['db_create_table'](
			'{db_prefix}reported_for_ban', 
			array(
				array(
							'name' => 'id_report',
							'type' => 'SMALLINT',
							'auto' => true,
					),
				array(
							'name' => 'id_member',
							'type' => 'MEDIUMINT',
							'default' => 0
					),
				array(
							'name' => 'id_reporter',
							'type' => 'TINYINT',
							'default' => 0
					),
				array(
							'name' => 'added',
							'type' => 'INT',
							'default' => 0
					),
				array(
							'name' => 'reason',
							'type' => 'VARCHAR',
							'size' => 255
					),
			),
			array(
				array(
					'name' => 'id_report',
					'type' => 'reporting',
					'columns' => array('id_member', 'id_reporter', 'reason'),
				),
			)
		);

?>