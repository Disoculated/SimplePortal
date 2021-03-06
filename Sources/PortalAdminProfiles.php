<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2013 SimplePortal Team
 * @license BSD 3-clause 
 *
 * @version 2.4
 */

if (!defined('SMF'))
	die('Hacking attempt...');

function sportal_admin_profiles_main()
{
	global $context, $sourcedir, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_profiles');

	require_once($sourcedir . '/Subs-PortalAdmin.php');

	loadTemplate('PortalAdminProfiles');

	$sub_actions = array(
		'listpermission' => 'sportal_admin_permission_profiles_list',
		'addpermission' => 'sportal_admin_permission_profiles_edit',
		'editpermission' => 'sportal_admin_permission_profiles_edit',
		'deletepermission' => 'sportal_admin_permission_profiles_delete',
		'liststyle' => 'sportal_admin_style_profiles_list',
		'addstyle' => 'sportal_admin_style_profiles_edit',
		'editstyle' => 'sportal_admin_style_profiles_edit',
		'deletestyle' => 'sportal_admin_style_profiles_delete',
		'listvisibility' => 'sportal_admin_visibility_profiles_list',
		'addvisibility' => 'sportal_admin_visibility_profiles_edit',
		'editvisibility' => 'sportal_admin_visibility_profiles_edit',
		'deletevisibility' => 'sportal_admin_visibility_profiles_delete',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($sub_actions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'listpermission';

	$context['sub_action'] = $_REQUEST['sa'];

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp_admin_profiles_title'],
		'help' => 'sp_ProfilesArea',
		'description' => $txt['sp_admin_profiles_desc'],
		'tabs' => array(
			'listpermission' => array(
			),
			'addpermission' => array(
			),
			'liststyle' => array(
			),
			'addstyle' => array(
			),
			'listvisibility' => array(
			),
			'addvisibility' => array(
			),
		),
	);

	$sub_actions[$context['sub_action']]();
}

function sportal_admin_permission_profiles_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $profile_id)
			$_POST['remove'][(int) $index] = (int) $profile_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_profiles
			WHERE id_profile IN ({array_int:profiles})',
			array(
				'profiles' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '35%',
			'label' => $txt['sp_admin_profiles_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'articles' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_articles'],
			'sortable' => false
		),
		'blocks' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_blocks'],
			'sortable' => false
		),
		'categories' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_categories'],
			'sortable' => false
		),
		'pages' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_pages'],
			'sortable' => false
		),
		'shoutboxes' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_shoutboxes'],
			'sortable' => false
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_profiles_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalprofiles;sa=listpermission;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => 1,
		)
	);
	list ($total_profiles) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalprofiles;sa=listpermission;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_profiles, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_profile, name
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'type' => 1,
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['profiles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['profiles'][$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editpermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletepermission;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);
	
	foreach (array('articles', 'blocks', 'categories', 'pages', 'shoutboxes') as $module)
	{
		$request = $smcFunc['db_query']('','
			SELECT permissions, COUNT(*) AS used
			FROM {db_prefix}sp_{raw:module}
			GROUP BY permissions',
			array(
				'module' => $module,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (isset($context['profiles'][$row['permissions']]))
				$context['profiles'][$row['permissions']][$module] = $row['used'];
		}
		$smcFunc['db_free_result']($request);
	}

	$context['sub_template'] = 'permission_profiles_list';
	$context['page_title'] = $txt['sp_admin_permission_profiles_list'];
}

function sportal_admin_permission_profiles_edit()
{
	global $smcFunc, $context, $txt;

	$context['is_new'] = empty($_REQUEST['profile_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_profile_name_empty', false);

		$groups_allowed = $groups_denied = '';

		if (!empty($_POST['membergroups']) && is_array($_POST['membergroups']))
		{
			$groups_allowed = $groups_denied = array();

			foreach ($_POST['membergroups'] as $id => $value)
			{
				if ($value == 1)
					$groups_allowed[] = (int) $id;
				elseif ($value == -1)
					$groups_denied[] = (int) $id;
			}

			$groups_allowed = implode(',', $groups_allowed);
			$groups_denied = implode(',', $groups_denied);
		}

		$fields = array(
			'type' => 'int',
			'name' => 'string',
			'value' => 'string',
		);

		$profile_info = array(
			'id' => (int) $_POST['profile_id'],
			'type' => 1,
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'value' => implode('|', array($groups_allowed, $groups_denied)),
		);

		if ($context['is_new'])
		{
			unset($profile_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_profiles',
				$fields,
				$profile_info,
				array('id_profile')
			);
			$profile_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_profiles', 'id_profile');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_profiles
				SET ' . implode(', ', $update_fields) . '
				WHERE id_profile = {int:id}',
				$profile_info
			);
		}

		redirectexit('action=admin;area=portalprofiles;sa=listpermission');
	}

	if ($context['is_new'])
	{
		$context['profile'] = array(
			'id' => 0,
			'name' => $txt['sp_profiles_default_name'],
			'groups_allowed' => array(),
			'groups_denied' => array(),
		);
	}
	else
	{
		$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
		$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
	}

	$context['profile']['groups'] = sp_load_membergroups();

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
	$context['sub_template'] = 'permission_profiles_edit';
}

function sportal_admin_permission_profiles_delete()
{
	global $smcFunc;

	checkSession('get');

	$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile = {int:id}',
		array(
			'id' => $profile_id,
		)
	);

	redirectexit('action=admin;area=portalprofiles;sa=listpermission');
}

function sportal_admin_style_profiles_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $profile_id)
			$_POST['remove'][(int) $index] = (int) $profile_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_profiles
			WHERE id_profile IN ({array_int:profiles})',
			array(
				'profiles' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '55%',
			'label' => $txt['sp_admin_profiles_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'articles' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_articles'],
			'sortable' => false
		),
		'blocks' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_blocks'],
			'sortable' => false
		),
		'pages' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_pages'],
			'sortable' => false
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_profiles_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalprofiles;sa=liststyle;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => 2,
		)
	);
	list ($total_profiles) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalprofiles;sa=liststyle;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_profiles, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_profile, name
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'type' => 2,
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['profiles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['profiles'][$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editstyle;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletestyle;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);

	foreach (array('articles', 'blocks', 'pages') as $module)
	{
		$request = $smcFunc['db_query']('','
			SELECT styles, COUNT(*) AS used
			FROM {db_prefix}sp_{raw:module}
			GROUP BY styles',
			array(
				'module' => $module,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (isset($context['profiles'][$row['styles']]))
				$context['profiles'][$row['styles']][$module] = $row['used'];
		}
		$smcFunc['db_free_result']($request);
	}

	$context['sub_template'] = 'style_profiles_list';
	$context['page_title'] = $txt['sp_admin_style_profiles_list'];
}

function sportal_admin_style_profiles_edit()
{
	global $smcFunc, $context, $txt;

	$context['is_new'] = empty($_REQUEST['profile_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_profile_name_empty', false);

		$fields = array(
			'type' => 'int',
			'name' => 'string',
			'value' => 'string',
		);

		$profile_info = array(
			'id' => (int) $_POST['profile_id'],
			'type' => 2,
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'value' => sportal_parse_style('implode'),
		);

		if ($context['is_new'])
		{
			unset($profile_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_profiles',
				$fields,
				$profile_info,
				array('id_profile')
			);
			$profile_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_profiles', 'id_profile');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_profiles
				SET ' . implode(', ', $update_fields) . '
				WHERE id_profile = {int:id}',
				$profile_info
			);
		}

		redirectexit('action=admin;area=portalprofiles;sa=liststyle');
	}

	if ($context['is_new'])
	{
		$context['profile'] = array(
			'id' => 0,
			'name' => $txt['sp_profiles_default_name'],
			'title_default_class' => 'catbg',
			'title_custom_class' => '',
			'title_custom_style' => '',
			'body_default_class' => 'windowbg',
			'body_custom_class' => '',
			'body_custom_style' => '',
			'no_title' => false,
			'no_body' => false,
		);
	}
	else
	{
		$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
		$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
	}

	$context['profile']['classes'] = array(
		'title' => array('catbg', 'catbg2', 'catbg3', 'titlebg', 'titlebg2', 'custom'),
		'body' => array('windowbg', 'windowbg2', 'windowbg3', 'information', 'roundframe', 'custom'),
	);

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
	$context['sub_template'] = 'style_profiles_edit';
}

function sportal_admin_style_profiles_delete()
{
	global $smcFunc;

	checkSession('get');

	$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile = {int:id}',
		array(
			'id' => $profile_id,
		)
	);

	redirectexit('action=admin;area=portalprofiles;sa=liststyle');
}

function sportal_admin_visibility_profiles_list()
{
	global $smcFunc, $context, $scripturl, $txt;

	if (!empty($_POST['remove_profiles']) && !empty($_POST['remove']) && is_array($_POST['remove']))
	{
		checkSession();

		foreach ($_POST['remove'] as $index => $profile_id)
			$_POST['remove'][(int) $index] = (int) $profile_id;

		$smcFunc['db_query']('','
			DELETE FROM {db_prefix}sp_profiles
			WHERE id_profile IN ({array_int:profiles})',
			array(
				'profiles' => $_POST['remove'],
			)
		);
	}

	$sort_methods = array(
		'name' =>  array(
			'down' => 'name ASC',
			'up' => 'name DESC'
		),
	);

	$context['columns'] = array(
		'name' => array(
			'width' => '75%',
			'label' => $txt['sp_admin_profiles_col_name'],
			'class' => 'first_th',
			'sortable' => true
		),
		'blocks' => array(
			'width' => '10%',
			'label' => $txt['sp_admin_profiles_col_blocks'],
			'sortable' => false
		),
		'actions' => array(
			'width' => '15%',
			'label' => $txt['sp_admin_profiles_col_actions'],
			'sortable' => false
		),
	);

	if (!isset($_REQUEST['sort']) || !isset($sort_methods[$_REQUEST['sort']]))
		$_REQUEST['sort'] = 'name';

	foreach ($context['columns'] as $col => $dummy)
	{
		$context['columns'][$col]['selected'] = $col == $_REQUEST['sort'];
		$context['columns'][$col]['href'] = $scripturl . '?action=admin;area=portalprofiles;sa=listvisibility;sort=' . $col;

		if (!isset($_REQUEST['desc']) && $col == $_REQUEST['sort'])
			$context['columns'][$col]['href'] .= ';desc';

		$context['columns'][$col]['link'] = '<a href="' . $context['columns'][$col]['href'] . '">' . $context['columns'][$col]['label'] . '</a>';
	}

	$context['sort_by'] = $_REQUEST['sort'];
	$context['sort_direction'] = !isset($_REQUEST['desc']) ? 'down' : 'up';

	$request = $smcFunc['db_query']('','
		SELECT COUNT(*)
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}',
		array(
			'type' => 3,
		)
	);
	list ($total_profiles) =  $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$context['page_index'] = constructPageIndex($scripturl . '?action=admin;area=portalprofiles;sa=listvisibility;sort=' . $_REQUEST['sort'] . (isset($_REQUEST['desc']) ? ';desc' : ''), $_REQUEST['start'], $total_profiles, 20);
	$context['start'] = $_REQUEST['start'];

	$request = $smcFunc['db_query']('','
		SELECT id_profile, name
		FROM {db_prefix}sp_profiles
		WHERE type = {int:type}
		ORDER BY {raw:sort}
		LIMIT {int:start}, {int:limit}',
		array(
			'type' => 3,
			'sort' => $sort_methods[$_REQUEST['sort']][$context['sort_direction']],
			'start' => $context['start'],
			'limit' => 20,
		)
	);
	$context['profiles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$context['profiles'][$row['id_profile']] = array(
			'id' => $row['id_profile'],
			'name' => $row['name'],
			'label' => isset($txt['sp_admin_profiles' . substr($row['name'], 1)]) ? $txt['sp_admin_profiles' . substr($row['name'], 1)] : $row['name'],
			'actions' => array(
				'edit' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=editvisibility;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '">' . sp_embed_image('modify') . '</a>',
				'delete' => '<a href="' . $scripturl . '?action=admin;area=portalprofiles;sa=deletevisibility;profile_id=' . $row['id_profile'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" onclick="return confirm(\'', $txt['sp_admin_profiles_delete_confirm'], '\');">' . sp_embed_image('delete') . '</a>',
			)
		);
	}
	$smcFunc['db_free_result']($request);
	
	foreach (array('blocks') as $module)
	{
		$request = $smcFunc['db_query']('','
			SELECT visibility, COUNT(*) AS used
			FROM {db_prefix}sp_{raw:module}
			GROUP BY visibility',
			array(
				'module' => $module,
			)
		);
		while ($row = $smcFunc['db_fetch_assoc']($request))
		{
			if (isset($context['profiles'][$row['visibility']]))
				$context['profiles'][$row['visibility']][$module] = $row['used'];
		}
		$smcFunc['db_free_result']($request);
	}

	$context['sub_template'] = 'visibility_profiles_list';
	$context['page_title'] = $txt['sp_admin_visibility_profiles_list'];
}

function sportal_admin_visibility_profiles_edit()
{
	global $smcFunc, $context, $txt;

	$context['is_new'] = empty($_REQUEST['profile_id']);

	if (!empty($_POST['submit']))
	{
		checkSession();

		if (!isset($_POST['name']) || $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES)) === '')
			fatal_lang_error('sp_error_profile_name_empty', false);

		$types = array('actions', 'boards', 'pages', 'categories', 'articles');

		$selections = array();

		foreach ($types as $type)
		{
			if (!empty($_POST[$type]) && is_array($_POST[$type]))
			{
				foreach ($_POST[$type] as $item)
				{
					$selections[] = $smcFunc['htmlspecialchars']($item, ENT_QUOTES);
				}
			}
		}

		$query = array();

		if (!empty($_POST['query']))
		{
			$items = explode(',', $_POST['query']);

			foreach ($items as $item)
			{
				$item = $smcFunc['htmltrim']($smcFunc['htmlspecialchars']($item, ENT_QUOTES));

				if ($item !== '')
				{
					$query[] = $item;
				}
			}
		}

		$fields = array(
			'type' => 'int',
			'name' => 'string',
			'value' => 'string',
		);

		$profile_info = array(
			'id' => (int) $_POST['profile_id'],
			'type' => 3,
			'name' => $smcFunc['htmlspecialchars']($_POST['name'], ENT_QUOTES),
			'value' => implode('|', array(implode(',', $selections), implode(',', $query))),
		);

		if ($context['is_new'])
		{
			unset($profile_info['id']);

			$smcFunc['db_insert']('',
				'{db_prefix}sp_profiles',
				$fields,
				$profile_info,
				array('id_profile')
			);
			$profile_info['id'] = $smcFunc['db_insert_id']('{db_prefix}sp_profiles', 'id_profile');
		}
		else
		{
			$update_fields = array();
			foreach ($fields as $name => $type)
				$update_fields[] = $name . ' = {' . $type . ':' . $name . '}';

			$smcFunc['db_query']('','
				UPDATE {db_prefix}sp_profiles
				SET ' . implode(', ', $update_fields) . '
				WHERE id_profile = {int:id}',
				$profile_info
			);
		}

		redirectexit('action=admin;area=portalprofiles;sa=listvisibility');
	}

	if ($context['is_new'])
	{
		$context['profile'] = array(
			'id' => 0,
			'name' => $txt['sp_profiles_default_name'],
			'query' => '',
			'selections' => array(),
		);
	}
	else
	{
		$_REQUEST['profile_id'] = (int) $_REQUEST['profile_id'];
		$context['profile'] = sportal_get_profiles($_REQUEST['profile_id']);
	}

	$context['profile']['actions'] = array(
		'portal' => $txt['sp-portal'],
		'forum' => $txt['sp-forum'],
		'recent' => $txt['recent_posts'],
		'unread' => $txt['unread_topics_visit'],
		'unreadreplies' => $txt['unread_replies'],
		'profile' => $txt['profile'],
		'pm' => $txt['pm_short'],
		'calendar' => $txt['calendar'],
		'admin' =>  $txt['admin'],
		'login' =>  $txt['login'],
		'register' =>  $txt['register'],
		'post' =>  $txt['post'],
		'stats' =>  $txt['forum_stats'],
		'search' =>  $txt['search'],
		'mlist' =>  $txt['members_list'],
		'moderate' =>  $txt['moderate'],
		'help' =>  $txt['help'],
		'who' =>  $txt['who_title'],
	);

	$request = $smcFunc['db_query']('','
		SELECT id_board, name
		FROM {db_prefix}boards
		WHERE redirect = {string:empty}
		ORDER BY name DESC',
		array(
			'empty' => '',
		)
	);
	$context['profile']['boards'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['profile']['boards']['b' . $row['id_board']] = $row['name'];
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('','
		SELECT id_page, title
		FROM {db_prefix}sp_pages
		ORDER BY title DESC'
	);
	$context['profile']['pages'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['profile']['pages']['p' . $row['id_page']] = $row['title'];
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('','
		SELECT id_category, name
		FROM {db_prefix}sp_categories
		ORDER BY name DESC'
	);
	$context['profile']['categories'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['profile']['categories']['c' . $row['id_category']] = $row['name'];
	$smcFunc['db_free_result']($request);

	$request = $smcFunc['db_query']('','
		SELECT id_article, title
		FROM {db_prefix}sp_articles
		ORDER BY title DESC'
	);
	$context['profile']['articles'] = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['profile']['articles']['a' . $row['id_article']] = $row['title'];
	$smcFunc['db_free_result']($request);

	$context['page_title'] = $context['is_new'] ? $txt['sp_admin_profiles_add'] : $txt['sp_admin_profiles_edit'];
	$context['sub_template'] = 'visibility_profiles_edit';
}

function sportal_admin_visibility_profiles_delete()
{
	global $smcFunc;

	checkSession('get');

	$profile_id = !empty($_REQUEST['profile_id']) ? (int) $_REQUEST['profile_id'] : 0;

	$smcFunc['db_query']('','
		DELETE FROM {db_prefix}sp_profiles
		WHERE id_profile = {int:id}',
		array(
			'id' => $profile_id,
		)
	);

	redirectexit('action=admin;area=portalprofiles;sa=listvisibility');
}