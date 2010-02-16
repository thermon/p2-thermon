<?php
/*
	+live - �n�C���C�g���[�h�̃`�F�b�N ../ShowThread.php ���ǂݍ��܂��
*/
// �A���n�C���C�g
if ($_conf['live.highlight_chain'] && $highlight_matches=$this->_getAnchorsFromMsg($msg)) {
	$this->_highlight_chain_nums = array_unique($highlight_matches);

	if (array_intersect($this->_highlight_chain_nums, $this->_highlight_nums)) {
		$ngaborns_hits['highlight_chain']++;
		$type |= $this->_markHighlight($i, self::HIGHLIGHT_CHAIN, true);

	}
}

// �n�C���C�g�l�[���`�F�b�N
if ($this->ngAbornCheck('highlight_name', $name) !== false) {

	$ngaborns_hits['highlight_name']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_NAME, false);
}

// �n�C���C�g���[���`�F�b�N
if ($this->ngAbornCheck('highlight_mail', $mail) !== false) {
	$ngaborns_hits['highlight_mail']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_MAIL, false);
}

// �n�C���C�gID�`�F�b�N
if ($this->ngAbornCheck('highlight_id', $date_id) !== false) {
	$ngaborns_hits['highlight_id']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_ID, false);
}

// �n�C���C�g���b�Z�[�W�`�F�b�N
$a_highlight_msg = $this->ngAbornCheck('highlight_msg', $msg);
if ($a_highlight_msg !== false) {
	$ngaborns_hits['highlight_msg']++;
	$type |= $this->_markHighlight($i, self::HIGHLIGHT_MSG, true);
	$this->_highlight_msgs[] = $a_highlight_msg;

	$this->_highlight_msgs = array_unique($this->_highlight_msgs);
}

?>