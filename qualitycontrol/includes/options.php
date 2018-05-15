<?php

$GLOBALS['qc_options'] = new scbOptions( 'qc_options', false, array(
	'assigned_perms' => 'protected',
	'lock_site' => false,
	'ticket_status_new' => false,
	'ticket_status_closed' => false,
	'status_colors' => array(),
	'modules' => array(
		'assignment',
		'attachments',
		'categories',
		'changesets',
		'milestones',
		'priorities',
		'tags',
	),
	'repository' => array(
		'type' => false,
		'details' => array(),
	)
) );

