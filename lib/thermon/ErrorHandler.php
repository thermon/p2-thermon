<?php
/**
 * rep2 - エラーハンドラ
 * このファイルは、特に理由の無い限り変更しないこと
 */

// エラーハンドラ
set_error_handler("myErrorHandler");

//ユーザーエラーハンドラ
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
	return false;	//PHPの内部エラーハンドラを実行します
}

/**
バックトレースを表示する
*
* <code>
* //バックトレースを表示
* print_backtrace(debug_backtrace());
* </code>
*
* @param array $backtrace debug_backtraceの返値
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
