<?php /** @file */


function widget_profile($args) {
	$a = get_app();
	$block = (((get_config('system','block_public')) && (! local_user()) && (! remote_user())) ? true : false);
	return profile_sidebar($a->profile, $block, true);
}

// FIXME The problem with the next widget is that we don't have a search function for webpages that we can send the links to.
// Then we should also provide an option to search webpages and conversations.

function widget_tagcloud($args) {

	$o = '';
	$tab = 0;
	$a = get_app();
	$uid = $a->profile_uid;
	$count = ((x($args,'count')) ? intval($args['count']) : 24);
	$flags = 0;
	$type = TERM_CATEGORY;

	$r = tagadelic($uid,$count,$authors,$flags,ITEM_WEBPAGE,$type);

	if($r) {
		$o = '<div class="tagblock widget"><h3>' . t('Categories') . '</h3><div class="tags" align="center">';
		foreach($r as $rr) {
			$o .= '<span class="tag'.$rr[2].'">'.$rr[0].'</span> ' . "\r\n";
		}
		$o .= '</div></div>';
	}
	return $o;
}

function widget_collections($args) {
	require_once('include/group.php');
	$page = argv(0);
	$gid = $_REQUEST['gid'];

	return group_side($page,$page,true,$_REQUEST['gid'],'',0);

}


function widget_suggestions($arr) {

	require_once('include/socgraph.php');

	$r = suggestion_query(local_user(),get_observer_hash(),0,20);

	if(! $r) {
		return;
	}

	$arr = array();

	// Get two random entries from the top 20 returned.
	// We'll grab the first one and the one immediately following.
	// This will throw some entropy intot he situation so you won't 
	// be looking at the same two mug shots every time the widget runs


	$index = ((count($r) > 2) ? mt_rand(0,count($r) - 2) : 0);
		

	for($x = $index; $x <= ($index+1); $x ++) {

		$rr = $r[$x];
		if(! $rr['xchan_url'])
			break;
		
		$connlnk = z_root() . '/follow/?url=' . $rr['xchan_addr'];

		$arr[] = array(
			'url' => chanlink_url($rr['xchan_url']),
			'profile' => $rr['xchan_url'],
			'name' => $rr['xchan_name'],
			'photo' => $rr['xchan_photo_m'],
			'ignlnk' => z_root() . '/suggest?ignore=' . $rr['xchan_hash'],
			'conntxt' => t('Connect'),
			'connlnk' => $connlnk,
			'ignore' => t('Ignore/Hide')
		);
	}


	$o = replace_macros(get_markup_template('suggest_widget.tpl'),array(
		'$title' => t('Suggestions'),
		'$more' => t('See more...'),
		'$entries' => $arr
	));

	return $o;

}