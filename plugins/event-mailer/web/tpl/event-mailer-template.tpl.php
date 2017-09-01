<!--
/*
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
*/
-->
<h2>{label}</h2>

<div id="form" class="event_mailer_template">
	<form action="{thisfile}" method="POST">
	{form}
		<div style="float:left;">
			{subject}
			{body}
		</div>
		<div style="float:left; margin: 0 0 0 15px;">
			<b>{lang_replacements}:</b><br><br>
			<b>{lang_subject}</b><br>
			@@EVENTID@@<br>
			@@DESCRIPTION@@<br><br>
			<b>{lang_body}</b><br>
			@@USERNAME@@<br>
			@@SERVERIP@@<br>
			@@EVENTACTION@@<br>
			@@EVENTTYPE@@<br>
			@@EVENTID@@<br>
			@@EVENTTIME@@<br>
			@@EVENTNAME@@<br>
			@@EVENTSOURCE@@<br>
			@@DESCRIPTION@@<br>
			@@EVENTRESOURCE@@<br>
			@@LINK@@
		</div>
		<div class="floatbreaker">&#160;</div>
		<div id="buttons" style="margin: 10px 0 0 0;">{submit}&#160;{cancel}</div>
	</form>
</div>
