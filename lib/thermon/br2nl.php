<?php
/**
 * rep2 - <br>�����s�R�[�h�ɋt�ϊ�����
 * ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
 */

// br2nl
function br2nl($str) {
	return preg_replace("/<br\s*(\/)?>/i","\n",$str);
}