<?php
/**
 * rep2 - <br>を改行コードに逆変換する
 * このファイルは、特に理由の無い限り変更しないこと
 */

// br2nl
function br2nl($str) {
	return preg_replace("/<br\s*(\/)?>/i","\n",$str);
}