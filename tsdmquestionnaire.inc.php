<?php
/*
        问卷调查插件 for Discuz X2
        初音家二小姐@天使动漫论坛
*/

//check

if(!defined('IN_DISCUZ')) {
		exit('Access Denied');
}
//var_dump($_G['isHTTPS']);
switch ($_G['gp_action']){
	case 'showpaper':
		$paperid = intval($_G['gp_paperid']);
		$myans = DB::result_array('SELECT * FROM '.DB::table('plugin_questionnaire_answers')." WHERE paperid=$paperid AND authorid=$_G[uid]");
		$myansindex = array();
		$myanstextindex = array();
		foreach($myans as $row){
			$myansindex[$row['questid']] = $row['choices'];
			$myanstextindex[$row['questid']] = $row['content'];
		}
		
		//var_dump($myansindex);
		$qslist = DB::result_array('SELECT * FROM '.DB::table('plugin_questionnaire_questions')." WHERE paperid=$paperid");
		$qscount = 0;
		foreach($qslist as &$row){
			$row['optarr'] = explode("\t", $row['options']);
			$row['order'] = $qscount + 1;
			++$qscount;
		}
		include template("tsdmquestionnaire:tsdmquestionnaire");
		break;
	case 'editpaper':
		
		break;
	case 'submitanswer':
		$_G['inajax'] = true;
		$returnarray = array('status' => 0, 'message' => '');
		$paperid = intval($_G['gp_paperid']);
		$questid = intval($_G['gp_questid']);
		//
		$mychoice = intval($_G['gp_choices']);
		if(!isset($_G['gp_optcontent'])){
			$_G['gp_optcontent'] = '';
		}
		$optcontent = $_G['gp_optcontent'];
		if(!$paperid || !$questid || (!$mychoice && !$optcontent)){
			$returnarray['status'] = -1;
			$returnarray['message'] = 'err_illegal_submit';
			echo json_encode($returnarray);
			break;
		}

		$paperinfo = DB::fetch_first('SELECT * FROM '.DB::table('plugin_questionnaire_index')." WHERE paperid=$paperid");
		$questinfo = DB::fetch_first('SELECT * FROM '.DB::table('plugin_questionnaire_questions')." WHERE questid=$questid");
		$myresult = DB::result_array('SELECT * FROM '.DB::table('plugin_questionnaire_answers')." WHERE questid=$questid AND authorid=$_G[uid]");
		if(is_array($myresult)){
			DB::delete('plugin_questionnaire_answers', "questid=$questid AND authorid=$_G[uid]");
		}

		if($paperinfo['expires'] > TIMESTAMP){
			DB::insert('plugin_questionnaire_answers', array(
				'paperid' => $paperid,
				'questid' => $questid,
				'choices' => $mychoice,
				'content' => $optcontent,
				'authorid' => $_G['uid'],
				'author' => $_G['username']
			));
		}else{
			$returnarray['status'] = -1;
			$returnarray['message'] = 'err_expired';
			echo json_encode($returnarray);
			break;
		}
		$returnarray['message'] = 'succ_submit_good';
		echo json_encode($returnarray);
		break;
	
	default:
		//do nothing.
}



?>