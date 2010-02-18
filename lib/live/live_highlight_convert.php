<?php
/*
	+live - �n�C���C�g���[�h�̕ϊ� ../ShowThreadPc.php ���ǂݍ��܂��
*/

// �A���n�C���C�g�ϊ�
if ($ng_type & self::HIGHLIGHT_CHAIN) {
	$highlight_chain_nums = implode('|', array_intersect($this->_highlight_chain_nums, $this->_highlight_nums));
	$highlight_chain_nums = "(" . $highlight_chain_nums . ")(?!\d)(?![^<]*>)"; // �������ꕔ����Ă��܂��A���J�[��HTML�^�O���Ƀ}�b�`�����Ȃ�
	$msg = preg_replace("(((?:&gt;|��|-)+)($highlight_chain_nums))", "<span class=\"live_highlight_chain\">$1$2</span>", $msg);
}

// �n�C���C�g���b�Z�[�W�ϊ�
if ($ng_type & self::HIGHLIGHT_MSG) {
	$highlight_pattern = array();
	$highlight_pattern_i = array();
	foreach ($this->_highlight_msgs as $highlight_word) {
		preg_match("(^(?:<(?:(regex)(:i)?|(i))>)?(.*))", $highlight_word,$match_word);
		$match_word[4] = StrSjis::fixSjisRegex($match_word[4],!$match_word[1]);
//		var_export($match_word);echo "<br>";

		if ($match_word[2] || $match_word[3]) {
			$highlight_pattern_i[] = $match_word[4];
		} else {
			$highlight_pattern[] = $match_word[4];
		}
	}
	if (count($highlight_pattern)) {
		$highlight_word = "((" . implode('|',$highlight_pattern) . ")(?![^<]*>))"; // HTML�^�O���Ƀ}�b�`�����Ȃ�
		if (P2_MBREGEX_AVAILABLE) {
			$match_method = 'mb_ereg_replace';
		} else {
			$match_method = 'preg_replace';

		}
		$msg = $match_method($highlight_word, "<span class=\"live_highlight\">\\1</span>", $msg);
	}
	if (count($highlight_pattern_i)) {
		$highlight_word_i = "((" . implode('|',$highlight_pattern_i) . ")(?![^<]*>))"; // HTML�^�O���Ƀ}�b�`�����Ȃ�

		if (P2_MBREGEX_AVAILABLE) {
			$match_method = 'mb_eregi_replace';
		} else {
			$match_method = 'preg_replace';
		}
		$msg = $match_method($highlight_word_i, "<span class=\"live_highlight\">\\1</span>", $msg);
	}
}

// �n�C���C�g�l�[���ϊ�
if ($ng_type & self::HIGHLIGHT_NAME) {
	$name = preg_replace("(</?b>)", "", $name);	
	$name = "</b><span class=\"live_highlight\">$name</span><b>";
}

// �n�C���C�g���[���ϊ�
if ($ng_type & self::HIGHLIGHT_MAIL) {
	$mail = "<span class=\"live_highlight\">$mail</span>";
}

// �n�C���C�gID�ϊ�
if ($ng_type & self::HIGHLIGHT_ID) {
	$date_id = preg_replace("((ID:))", "<span class=\"live_highlight\">$1", $date_id ."</span>");
}

?>