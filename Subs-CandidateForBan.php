<?php
if (!defined('SMF'))
	die('Hacking attempt...');

/*
 *
 * Hooks
 *
 */

function reportForBan_add_permissions (&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context;
	$permissionList['membergroup']['report_for_ban'] = array(false, 'member_admin', 'administrate');
	$context['non_guest_permissions'][] = 'report_for_ban';
}

function reportForBan_add_profile_menu (&$profile_areas)
{
	global $txt, $context;

	$profile_areas['profile_action']['areas']['report_for_ban'] = array(
		'label' => $txt['report_for_ban'],
		'file' => 'Subs-CandidateForBan.php',
		'function' => 'reportForBan',
		'password' => true,
		'permission' => array(
			'own' => array(),
			'any' => array('report_for_ban'),
		),
	);
}

function reportForBan_add_admin_menu (&$admin_areas)
{
	global $txt;

	$admin_areas['members']['areas']['ban']['subsections']['propban'] = array($txt['proposed_bans']);
}

function reportForBan_settings (&$config_vars) {
	global $txt;
	$config_vars[] = array('text', 'reportForBan_ban_name');

	if (isset($_GET['save']))
	{
		$_POST['reportForBan_ban_name'] = !empty($_POST['reportForBan_ban_name']) ? $_POST['reportForBan_ban_name'] : $txt['reported_bans'];
	}
}

/*
 *
 * End of hooks
 *
 */

function reportForBan ()
{
	global $context, $txt, $smcFunc, $user_info, $scripturl;

	isAllowedTo('report_for_ban');
	loadTemplate('CandidateForBan');
	loadLanguage('Admin');

	if (isset($_REQUEST['request_ban']) && empty($context['reportforban_errors']))
		reportForBan2();

	$member_reported = array();
	$reportes = array();
	$request = $smcFunc['db_query']('', '
		SELECT rep.id_reporter, rep.reason, mem.real_name
		FROM {db_prefix}reported_for_ban as rep
		LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = rep.id_reporter)
		WHERE rep.id_member = {int:id_member}',
		array(
			'id_member' => $context['id_member'],
	));
	if($smcFunc['db_num_rows']($request) > 0)
		while($row = $smcFunc['db_fetch_assoc']($request))
			$member_reported[] = $row;

	$smcFunc['db_free_result']($request);

	$context['ban']['reason'] = isset($_POST['reason']) ? $_POST['reason'] : '';

	if (!empty($context['reportforban_errors']))
	{
		$context['reportforban_errors']['messages'] = array();
		foreach($context['reportforban_errors'] as $error)
			if(!empty($error))
				$context['reportforban_errors']['messages'][] = $txt['reportforban_errors_' . $error];
	}
	if (!empty($member_reported))
	{
		$context['already_reported'] = array();
		foreach ($member_reported as $rep)
			$context['already_reported'][] = $txt['member_reported'] . '<a href="' . $scripturl . '?action=profile;u=' . $rep['id_reporter'] . '">' . $rep['real_name'] . '</a> ' . vsprintf($txt['report_for_ban_member_reported'], $rep['reason']);
	}

	if(empty($context['html_headers']))
		$context['html_headers'] = '';

	$context['html_headers'] .= '
	<style type="text/css">
		.ban_restriction
		{
			margin: 0.2em 0 0.2em 2.2em;
		}
		.ban_settings
		{
			width: 46%;
		}
		#manage_bans dl
		{
			margin-bottom: 1em;
		}
		#manage_bans fieldset dl.settings
		{
			margin-bottom: 0;
		}
	</style>';

}
function reportForBan2 ()
{
	global $context, $smcFunc, $user_info;

	checkSession();
	$context['reportforban_errors'] = array();

	if(empty($_POST['reason']))
		$context['reportforban_errors']['reason'] = 'reason';

	if(!empty($context['reportforban_errors']))
		return reportForBan();

// 	$id = $smcFunc['db_insert_id']('{db_prefix}reported_for_ban', 'id_report');
// 	_debug($id);
	$smcFunc['db_insert']('insert',
		'{db_prefix}reported_for_ban',
		array(
			'id_member' => 'int', 'id_reporter' => 'int', 'reason' => 'string-255', 'added' => 'int',
		),
		array(
			$context['id_member'], $user_info['id'], $smcFunc['htmlspecialchars']($_POST['reason'], ENT_QUOTES), time(),
		),
		array('id_report')
	);
}

function list_getPropBans ($start, $items_per_page, $sort)
{
	global $smcFunc, $context;

	$request = $smcFunc['db_query']('', '
		SELECT rep.id_report, rep.id_member, rep.id_reporter, rep.reason, rep.added, mem.member_name, mem.real_name, mem.email_address, mem.member_ip, mem.member_ip2
		FROM {db_prefix}reported_for_ban AS rep
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = rep.id_member)
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'sort' => $sort,
			'offset' => $start,
			'limit' => $items_per_page,
		)
	);

	$bans = array();
	$reporters = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$bans[] = $row;
		$reporters[] = $row['id_reporter'];
	}

	$smcFunc['db_free_result']($request);

	if(!empty($reporters))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_member, real_name
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:id_members})',
			array(
				'id_members' => $reporters,
		));

		$context['reporters'] = array();
		while($row = $smcFunc['db_fetch_assoc']($request))
			$context['reporters'][$row['id_member']] = $row['real_name'];
		$smcFunc['db_free_result']($request);
	}
	return $bans;
}

function list_getNumPropBans ()
{
	global $smcFunc;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*) AS num_prop_bans
		FROM {db_prefix}reported_for_ban',
		array(
		)
	);
	list ($numPropBans) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numPropBans;
}

function ReportedBans ()
{
	global $context, $sourcedir, $scripturl, $txt, $modSettings;

	if(isset($_POST['add_to_ban']))
		ReportedBans2();

	$listOptions = array(
		'id' => 'propban_list',
		'items_per_page' => 20,
		'base_href' => $scripturl . '?action=admin;area=ban;sa=propban',
		'default_sort_col' => 'name',
		'default_sort_dir' => 'desc',
		'get_items' => array(
			'function' => 'list_getPropBans',
		),
		'get_count' => array(
			'function' => 'list_getNumPropBans',
		),
		'no_items_label' => $txt['propban_no_entries'],
		'columns' => array(
			'name' => array(
				'header' => array(
					'value' => $txt['username'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=profile;u=\' . $rowData[\'id_member\'] . \'">\' . $rowData[\'real_name\'] . \'</a>\';
					'),
				),
				'sort' => array(
					'default' => 'mem.member_name',
					'reverse' => 'mem.member_name DESC',
				),
			),
			'real_name' => array(
				'header' => array(
					'value' => $txt['name'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=profile;u=\' . $rowData[\'id_member\'] . \'">\' . $rowData[\'real_name\'] . \'</a>\';
					'),
				),
				'sort' => array(
					'default' => 'mem.real_name',
					'reverse' => 'mem.real_name DESC',
				),
			),
			'email' => array(
				'header' => array(
					'value' => $txt['email'],
				),
				'data' => array(
					'db' => 'email_address',
				),
				'sort' => array(
					'default' => 'mem.email_address',
					'reverse' => 'mem.email_address DESC',
				),
			),
			'ip' => array(
				'header' => array(
					'value' => $txt['ip_address'],
				),
				'data' => array(
					'db' => 'member_ip',
				),
				'sort' => array(
					'default' => 'mem.member_ip',
					'reverse' => 'mem.member_ip DESC',
				),
			),
			'ip2' => array(
				'header' => array(
					'value' => $txt['ip_address'] . ' 2',
				),
				'data' => array(
					'db' => 'member_ip2',
				),
				'sort' => array(
					'default' => 'mem.member_ip2',
					'reverse' => 'mem.member_ip2 DESC',
				),
			),
			'reporter' => array(
				'header' => array(
					'value' => $txt['reporter'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context, $scripturl;

						return \'<a href="\' . $scripturl . \'?action=profile;u=\' . $rowData[\'id_reporter\'] . \'">\' . $context[\'reporters\'][$rowData[\'id_reporter\']] . \'</a>\';
					'),
				),
				'sort' => array(
					'default' => 'mem.member_name',
					'reverse' => 'mem.member_name DESC',
				),
			),
			'reason' => array(
				'header' => array(
					'value' => $txt['ban_reason'],
				),
				'data' => array(
					'db' => 'reason',
					'class' => 'smalltext',
				),
				'sort' => array(
					'default' => 'LENGTH(rep.reason) > 0 DESC, rep.reason',
					'reverse' => 'LENGTH(rep.reason) > 0, rep.reason DESC',
				),
			),
			'added' => array(
				'header' => array(
					'value' => $txt['ban_added'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $context;

						return timeformat($rowData[\'added\'], empty($context[\'ban_time_format\']) ? true : $context[\'ban_time_format\']);
					'),
				),
				'sort' => array(
					'default' => 'rep.added',
					'reverse' => 'rep.added DESC',
				),
			),
			'check' => array(
				'header' => array(
					'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check" />',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<input type="checkbox" name="bans[%2$d]" value="%1$d" class="input_check" />',
						'params' => array(
							'id_member' => false,
							'id_report' => false,
						),
					),
					'style' => 'text-align: center',
				),
			),
		),
		'form' => array(
			'href' => $scripturl . '?action=admin;area=ban;sa=propban',
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '
				' . $txt['ban_name'] . '
				<input type="text" name="ban_name" value="' . (!empty($modSettings['reportForBan_ban_name']) ? $modSettings['reportForBan_ban_name'] : '') . '" class="input_text"/>
				' . $txt['ban_time'] . '
				<input title="' . $txt['ban_time_title'] . '" type="text" name="ban_time" class="input_text"/>
				' . $txt['ban_by'] . '
				<select name="ban_type">
				<option value="ban_names">' . $txt['admin_ban_usernames'] . '</option>
				<option value="ban_emails">' . $txt['admin_ban_emails'] . '</option>
				<option value="ban_ips">' . $txt['admin_ban_ips'] . '</option>
				<option value="ban_names_emails">' . $txt['admin_ban_usernames_and_emails'] . '</option>
				<option value="ban_names_ips">' . $txt['admin_ban_usernames_and_ips'] . '</option>
				<option value="ban_emails_ips">' . $txt['admin_ban_emails_and_ips'] . '</option>
				<option value="ban_names_emails_ips">' . $txt['admin_ban_usernames_emails_and_ips'] . '</option>
				<option value="remove_from_list">' . $txt['remove_from_list'] . '</option>
				</select>
				<input type="submit" name="add_to_ban" value="' . $txt['ban_add'] . '" class="button_submit" />',
				'style' => 'text-align: right;',
			),
		),
	);

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'propban_list';
}

function ReportedBans2 ()
{
	global $modSettings, $smcFunc, $txt, $sourcedir, $user_info;
	checkSession('post');

	// Were are we supposed to put all these bans??
	$ban_name = !empty($_POST['ban_name']) ? $smcFunc['htmlspecialchars']($_POST['ban_name'], ENT_QUOTES) : $modSettings['reportForBan_ban_name'];
	if(empty($_POST['ban_name']))
		return ReportedBans();

	if(empty($_POST['bans']))
		fatal_lang_error('no_member_selected', false);

	$id_members = array_unique($_POST['bans']);

	// Clean the input.
	foreach ($id_members as $key => $value)
	{
		$id_members[$key] = (int) $value;
		// Don't delete yourself, idiot.
		if ($value == $user_info['id'])
			unset($id_members[$key]);
	}

	require_once($sourcedir . '/ManageBans.php');

	$id_ban = $smcFunc['db_query']('', '
		SELECT id_ban_group
		FROM {db_prefix}ban_groups
		WHERE name = \'' . $ban_name . '\'
		LIMIT 1',
		array()
	);
	if($smcFunc['db_num_rows']($id_ban)!=0)
		list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
	else
		$ban_group_id = null;

	$smcFunc['db_free_result']($id_ban);

	$members = array();
	$_REQUEST['bg'] = $ban_group_id;

	// Set up an array of bans
	foreach ($id_members as $key => $value)
		if ($value != $user_info['id'])
			// Don't ban yourself, idiot.
			$members[] = (int) $value;

	$remove = false;
	switch ($_POST['ban_type'])
	{
		case 'ban_names':
			$mactions = array('ban_names');
			break;
		case 'ban_emails':
			$mactions = array('ban_mails');
			break;
		case 'ban_ips':
			$mactions = array('ban_ips', 'ban_ips2');
			break;
		case 'ban_names_emails':
			$mactions = array('ban_names', 'ban_mails');
			break;
		case 'ban_names_ips':
			$mactions = array('ban_names', 'ban_ips', 'ban_ips2');
			break;
		case 'ban_emails_ips':
			$mactions = array('ban_mails', 'ban_ips', 'ban_ips2');
			break;
		case 'ban_names_emails_ips':
			$mactions = array('ban_names', 'ban_mails', 'ban_ips', 'ban_ips2');
			break;
		case 'remove_from_list':
			$remove = true;
			break;
		default:
			$mactions = null;
			break;
	}

	foreach ($mactions as $maction) {
		$checkIPs = false;
		switch ($maction) {
			case 'ban_names':
				$what = 'member_name';
				$post_ban = 'user';
				$_POST['ban_suggestion'][] = 'user';
				$_POST['bantype'] = 'user_ban';
				break;
			case 'ban_mails':
				$what = 'email_address';
				$post_ban = 'email';
				$_POST['ban_suggestion'][] = 'email';
				$_POST['bantype'] = 'email_ban';
				break;
			case 'ban_ips':
				$checkIPs = true;
				$what = 'member_ip';
				$post_ban = !empty($ban_group_id) ? 'ip' : 'main_ip';
				$_POST['ban_suggestion'][] = 'main_ip';
				$_POST['bantype'] = 'ip_ban';
				break;
			case 'ban_ips2':
				$checkIPs = true;
				$what = 'member_ip2';
				$post_ban = !empty($ban_group_id) ? 'ip' : 'main_ip';
				$_POST['ban_suggestion'][] = 'main_ip';
				$_POST['bantype'] = 'ip_ban';
				break;
			default:
				return false;
		}
		$request = $smcFunc['db_query']('', '
			SELECT id_member, member_name, ' . $what . '
			FROM {db_prefix}members
			WHERE id_member IN ({array_int:id_members})',
			array(
				'id_members' => $members,
		));

		$_POST['expiration'] = empty($_POST['ban_time']) ? 'never' : 'expire_time';
		$_POST['expire_date'] = !empty($_POST['ban_time']) ? $_POST['ban_time'] : '';
		$_POST['old_expire'] = 0;
		$_POST['full_ban'] = empty($_POST['ban_time']);
		$_POST['reason'] = $ban_name;
		$_POST['ban_name'] = $ban_name;
		$_POST['notes'] = '';

		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if ($checkIPs) {
				$ip_parts = ip2range($row[$what]);
				if (reportForBan_checkExistingTriggerIP($ip_parts, $row[$what]))
					continue;

				$_POST['ip'] = $row[$what];
			}
			$_POST['add_new_trigger'] = !empty($ban_group_id) ? 1 : null;
			$_POST['add_ban'] = empty($ban_group_id) ? 1 : null;
			$_POST[$post_ban] = $row[$what];
			$_REQUEST['u'] = $row['id_member'];

			BanEdit();
			if(empty($ban_group_id)){
				$id_ban = $smcFunc['db_query']('', '
					SELECT id_ban_group
					FROM {db_prefix}ban_groups
					WHERE name = \'' . $ban_name . '\'
					LIMIT 1',
					array()
				);
				if($smcFunc['db_num_rows']($id_ban)!=0)
					list($ban_group_id) = $smcFunc['db_fetch_row']($id_ban);
				else
					$ban_group_id = null;
				$smcFunc['db_free_result']($id_ban);
			}
		}
		$smcFunc['db_free_result']($request);
		$remove = true;
	}

	//Cleanup the reports
	if($remove)
	{
		$to_remove = array();
		foreach($_POST['bans'] as $key => $val)
			$to_remove[] = $key;

		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}reported_for_ban
			WHERE id_report IN ({array_int:id_report})',
			array(
				'id_report' => $to_remove,
		));
	}
	
}

function reportForBan_checkExistingTriggerIP($ip_array, $fullip = '')
{
	global $smcFunc, $user_info;

	if (count($ip_array) == 4)
		$values = array(
			'ip_low1' => $ip_array[0]['low'],
			'ip_high1' => $ip_array[0]['high'],
			'ip_low2' => $ip_array[1]['low'],
			'ip_high2' => $ip_array[1]['high'],
			'ip_low3' => $ip_array[2]['low'],
			'ip_high3' => $ip_array[2]['high'],
			'ip_low4' => $ip_array[3]['low'],
			'ip_high4' => $ip_array[3]['high'],
		);
	else
		return true;

	// Again...don't ban yourself!!
	if (!empty($fullip) && ($user_info['ip'] == $fullip || $user_info['ip2'] == $fullip))
		return true;

	$request = $smcFunc['db_query']('', '
		SELECT bg.id_ban_group, bg.name
		FROM {db_prefix}ban_groups AS bg
		INNER JOIN {db_prefix}ban_items AS bi ON
			(bi.id_ban_group = bg.id_ban_group)
			AND ip_low1 = {int:ip_low1} AND ip_high1 = {int:ip_high1}
			AND ip_low2 = {int:ip_low2} AND ip_high2 = {int:ip_high2}
			AND ip_low3 = {int:ip_low3} AND ip_high3 = {int:ip_high3}
			AND ip_low4 = {int:ip_low4} AND ip_high4 = {int:ip_high4}
		LIMIT 1',
		$values
	);
	if ($smcFunc['db_num_rows']($request) != 0)
		$ret = true;
	else
		$ret = false;
	$smcFunc['db_free_result']($request);

	return $ret;
}

?>