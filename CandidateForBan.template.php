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

function template_CandidateForBan()
{
	global $context, $settings, $options, $scripturl, $txt, $scripturl;

	// The main containing header.
	//WE WANT, WE WANT, REASONS.
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span class="ie6_header floatleft"><img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />', $txt['reportforban_this_user'], '</span>
		</h3>
	</div>';
	if (!empty($context['reportforban_errors']))
	{
		echo '
		<div class="errorbox">
			', implode('<br />', $context['reportforban_errors']['messages']), '
		</div>';
	}
	if (!empty($context['already_reported']))
	{
		echo '
		<div id="profile_success">
			', implode('<br />', $context['already_reported']), '
		</div>';
	}

	echo '
	<div id="manage_bans">
		<div class="windowbg">
			<span class="topslice"><span></span></span>
			<div class="content">
				<form action="', $scripturl, '?action=profile;area=report_for_ban;u=', $context['id_member'], '" method="post" accept-charset="', $context['character_set'], '" name="creator">
					<dl class="settings">
						<dt', isset($context['reportforban_errors']['reason']) ? ' class="error"' : '', '>
							<strong>', $txt['ban_reason'], ':</strong><br />
							<span class="smalltext">', $txt['prop_ban_reason_desc'], '</span>
						</dt>
						<dd>
							<input type="text" maxlength="255" size="50" name="reason" ', !empty($context['ban']['reason']) ? 'value="' . $context['ban']['reason'] . '"' : '', ' />
						</dd>
					</dl>
					<br class="clear_right" />';

	echo '
					<div class="righttext">
						<input type="submit" name="request_ban" value="', $txt['save'], '" class="button_submit" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
					</div>';

	echo '
				</form>
			</div>
			<span class="botslice"><span></span></span>
		</div>
	</div>
	<br />';

}

?>