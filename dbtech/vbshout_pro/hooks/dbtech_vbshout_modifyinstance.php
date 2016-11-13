<?php
	print_description_row($vbphrase['options'], false, 2, 'optiontitle');	
	print_bitfield_row($vbphrase['dbtech_vbshout_shoutboxtabs_descr'], 			'instance[options][shoutboxtabs]', 			$bitfields['shoutboxtabs'], 	$instance['options']['shoutboxtabs']);
	print_yes_no_row($vbphrase['dbtech_vbshout_logging_deep_descr'], 			'instance[options][logging_deep]', 											$instance['options']['logging_deep']);
	print_yes_no_row($vbphrase['dbtech_vbshout_logging_deep_system_descr'], 	'instance[options][logging_deep_system]', 									$instance['options']['logging_deep_system']);
	print_yes_no_row($vbphrase['dbtech_vbshout_enablepms_descr'], 				'instance[options][enablepms]', 											$instance['options']['enablepms']);
	print_yes_no_row($vbphrase['dbtech_vbshout_enablepmnotifs_descr'], 			'instance[options][enablepmnotifs]', 										$instance['options']['enablepmnotifs']);
	print_yes_no_row($vbphrase['dbtech_vbshout_enable_sysmsg_descr'], 			'instance[options][enable_sysmsg]', 										$instance['options']['enable_sysmsg']);
	print_yes_no_row($vbphrase['dbtech_vbshout_sounds_idle_descr'], 			'instance[options][sounds_idle]', 											$instance['options']['sounds_idle']);
	print_yes_no_row($vbphrase['dbtech_vbshout_avatars_normal_descr'], 			'instance[options][avatars_normal]', 										$instance['options']['avatars_normal']);
	print_input_row($vbphrase['dbtech_vbshout_avatar_width_normal_descr'], 		'instance[options][avatar_width_normal]', 									$instance['options']['avatar_width_normal']);
	print_input_row($vbphrase['dbtech_vbshout_avatar_height_normal_descr'], 	'instance[options][avatar_height_normal]', 									$instance['options']['avatar_height_normal']);
	print_yes_no_row($vbphrase['dbtech_vbshout_avatars_full_descr'], 			'instance[options][avatars_full]', 											$instance['options']['avatars_full']);
	print_input_row($vbphrase['dbtech_vbshout_avatar_width_full_descr'], 		'instance[options][avatar_width_full]', 									$instance['options']['avatar_width_full']);
	print_input_row($vbphrase['dbtech_vbshout_avatar_height_full_descr'], 		'instance[options][avatar_height_full]', 									$instance['options']['avatar_height_full']);
	print_input_row($vbphrase['dbtech_vbshout_maxshouts_detached_descr'], 		'instance[options][maxshouts_detached]', 									$instance['options']['maxshouts_detached']);
	print_input_row($vbphrase['dbtech_vbshout_height_detached_descr'], 			'instance[options][height_detached]', 										$instance['options']['height_detached']);
	print_input_row($vbphrase['dbtech_vbshout_refresh_idle_descr'], 			'instance[options][refresh_idle]', 											$instance['options']['refresh_idle']);
	print_input_row($vbphrase['dbtech_vbshout_archive_numtopshouters_descr'], 	'instance[options][archive_numtopshouters]', 								$instance['options']['archive_numtopshouters']);
	print_input_row($vbphrase['dbtech_vbshout_autodelete_descr'], 				'instance[options][autodelete]', 											$instance['options']['autodelete']);
	print_input_row($vbphrase['dbtech_vbshout_minposts_descr'], 				'instance[options][minposts]', 												$instance['options']['minposts']);
	print_input_row($vbphrase['dbtech_vbshout_timeformat_descr'], 				'instance[options][timeformat]', 											$instance['options']['timeformat']);
	print_select_row($vbphrase['dbtech_vbshout_shoutarea_descr'],				'instance[options][shoutarea]', array(
																													'left' 	=> $vbphrase['dbtech_vbshout_left_of_shouts'],
																													'right' => $vbphrase['dbtech_vbshout_right_of_shouts'],
																													'above' => $vbphrase['dbtech_vbshout_above_shouts'],
																													'below' => $vbphrase['dbtech_vbshout_below_shouts'],
																												),											$instance['options']['shoutarea']);
	print_select_row($vbphrase['dbtech_vbshout_archive_link_descr'],			'instance[options][archive_link]', array(
																													0 		=> $vbphrase['dbtech_vbshout_integrated_with_title'],
																													1 		=> $vbphrase['dbtech_vbshout_separate_from_title'],
																												),											$instance['options']['archive_link']);
	
	print_description_row($vbphrase['dbtech_vbshout_forum_milestones'], false, 2, 'optiontitle');	
	print_input_row($vbphrase['dbtech_vbshout_blogping_interval_descr'], 		'instance[options][blogping_interval]', 			$instance['options']['blogping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_shoutping_interval_descr'], 		'instance[options][shoutping_interval]', 			$instance['options']['shoutping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_aptlping_interval_descr'], 		'instance[options][aptlping_interval]', 			$instance['options']['aptlping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_tagping_interval_descr'], 		'instance[options][tagping_interval]', 				$instance['options']['tagping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_mentionping_interval_descr'], 	'instance[options][mentionping_interval]', 			$instance['options']['mentionping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_quoteping_interval_descr'], 		'instance[options][quoteping_interval]', 			$instance['options']['quoteping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_quizmadeping_interval_descr'], 	'instance[options][quizmadeping_interval]', 		$instance['options']['quizmadeping_interval']);
	print_input_row($vbphrase['dbtech_vbshout_quiztakenping_interval_descr'], 	'instance[options][quiztakenping_interval]', 		$instance['options']['quiztakenping_interval']);
?>