<?php

function template_ReportForBan()
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
		<div class="errorbox">
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
							<span class="smalltext">', $txt['ban_reason_desc'], '</span>
						</dt>
						<dd>
							<textarea name="reason" cols="50" rows="3">', $context['ban']['reason'], '</textarea>
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

function template_ReportedBans ()
{
	echo "EE";

}
?>