<?php
/**
 * Candidate for ban (CFB)
 *
 * @package CFB
 * @author emanuele
 * @copyright 2011 emanuele, Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 0.1.1
 */

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
							'type' => 'mediumint',
							'default' => 0
					),
				array(
							'name' => 'id_reporter',
							'type' => 'tinyint',
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
					'type' => 'primary',
					'columns' => array('id_report'),
				),
				array(
					'name' => 'reports',
					'type' => 'unique',
					'columns' => array('id_member', 'id_reporter', 'reason'),
				),
			)
		);

	// /me wants to know how many admins you have. :P
	$request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE id_group = {int:admin_group}
			OR FIND_IN_SET({int:admin_group}, additional_groups) != 0',
		array(
			'admin_group' => 1,
	));

	while ($row = $smcFunc['db_fetch_assoc']($request))
		$admins[] = $row['id_member'];
	$smcFunc['db_free_result']($request);

	$numb_admins = count($admins);

	updateSettings(array(
		'candidate_for_ban_admin_number' => $numb_admins,
		'candidate_for_ban_admins_id' => serialize($admins),
	));

?>