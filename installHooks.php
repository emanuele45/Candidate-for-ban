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

$scheduledTaskFunctionName = 'cfb_countAdmins';
if (empty($context['uninstalling']))
{
	$integration_function = 'add_integration_function';
	$smcFunc['db_insert']('',
		'{db_prefix}scheduled_tasks',
		array('next_time' => 'int', 'time_offset' => 'int', 'time_regularity' => 'int', 'time_unit' => 'string-1', 'disabled' => 'int', 'task' => 'string'),
		array (0, 0, 1, 'd', 0, $scheduledTaskFunctionName),
		array('id_task')
	);
}
else
{
	$integration_function = 'remove_integration_function';
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}scheduled_tasks
		WHERE task = {string:task_func}',
		array(
			'task_func' => $scheduledTaskFunctionName
	));
}

$integration_function('integrate_pre_include',  '$sourcedir/Subs-CandidateForBan.php');
$integration_function('integrate_load_theme',  'candidateForBan_common_permissions');
$integration_function('integrate_load_permissions',  'candidateForBan_add_permissions');
$integration_function('integrate_profile_areas',  'candidateForBan_add_profile_menu');
$integration_function('integrate_admin_areas',  'candidateForBan_add_admin_menu');
$integration_function('integrate_general_mod_settings', 'candidateForBan_settings');

?>