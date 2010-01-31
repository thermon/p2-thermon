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
	$highlight_msgs = quotemeta(implode('|', $this->_highlight_msgs));
	$highlight_msgs = "(" . $highlight_msgs . ")(?![^<]*>)"; // HTML�^�O���Ƀ}�b�`�����Ȃ�
	if (preg_match("(<(regex:i|i)>)", $highlight_msgs)) {
		$highlight_msgs = preg_replace("(<(regex|regex:i|i)>)", "", $highlight_msgs);
		$msg = mb_eregi_replace("($highlight_msgs)", "<span class=\"live_highlight\">\\1</span>", $msg);
	} else {
		$highlight_msgs = preg_replace("(<(regex|regex:i|i)>)", "", $highlight_msgs);
		$msg = mb_ereg_replace("($highlight_msgs)", "<span class=\"live_highlight\">\\1</span>", $msg);
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