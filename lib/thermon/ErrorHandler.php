<?php
/**
 * rep2 - �G���[�n���h��
 * ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
 */

// �G���[�n���h��
set_error_handler("myErrorHandler");

//���[�U�[�G���[�n���h��
function myErrorHandler($errno,$errstr,$errfile,$errline) {
	switch ($errno) {
		case E_USER_ERROR:
		case E_ERROR;
			echo"<b>Fatal error</b>:".$errstr." in <b>".$errfile."</b> on line <b>".$errline;
//			print_backtrace(debug_backtrace());
			exit(1);
			break;

		case E_USER_WARNING:
		case E_WARNING:
			break;

		case E_USER_NOTICE:
			break;
	}
	return false;	//PHP�̓����G���[�n���h�������s���܂�
}

/**
�o�b�N�g���[�X��\������
*
* <code>
* //�o�b�N�g���[�X��\��
* print_backtrace(debug_backtrace());
* </code>
*
* @param array $backtrace debug_backtrace�̕Ԓl
* @return void
*/
function print_backtrace($backtrace){
	echo "<table border=\"1\" cellpadding=\"3\">";
	echo "<tr align=\"center\"><td>#</td><td>call</td><td>path</td></tr>";
	foreach ($backtrace as $key => $val){
		echo "<tr><td>".$key."</td>";
		$args="  ".var_export($val['args'],true);
		$args=preg_replace("/\n/","\n  ",$args);
		echo "<td><pre>".$val['function']."(\n{$args}\n)</pre></td>";
		echo "<td>".$val['file']." on line ".$val['line']."</td></tr>";
	}
	echo "</table>";
}
