<?php 
// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');
  
$integration_function = empty($context['uninstalling']) ? 'add_integration_function' : 'remove_integration_function';

$integration_function('integrate_pre_include',  '$sourcedir/Subs-CandidateForBan.php');
$integration_function('integrate_load_theme ',  'candidateForBan_common_permissions');
$integration_function('integrate_load_permissions',  'candidateForBan_add_permissions');
$integration_function('integrate_profile_areas',  'candidateForBan_add_profile_menu');
$integration_function('integrate_admin_areas',  'candidateForBan_add_admin_menu');
$integration_function('integrate_general_mod_settings', 'candidateForBan_settings');

?>