<?php
// {{{ StrSjis

/**
 * SJIS�̂��߂̃N���X�B�X�^�e�B�b�N���\�b�h�ŗ��p����B
 * SJIS������̖��������Ă���̂��C���J�b�g����B
 *
 * @author aki
 * @since  2006/10/02
 * @static
 */
class StrSjis
{
    // {{{ note

    /**
     * �Q�l�f�[�^
     * SJIS 2�o�C�g�̑�1�o�C�g�͈� 129�`159�A224�`239�i0x81�`0x9F�A0xE0�`0xEF�j
     * SJIS 2�o�C�g�̑�2�o�C�g�͈� 64�`126�A128�`252�i0x40�`0x7E�A0x80�`0xFC�j�i��1�o�C�g�͈͂����Ă���j
     * SJIS �p����(ASCII) 33�`126�i0x21�`0x7E�j 32 ��
     * SJIS ���p�J�i161�`223�i0xA1�`0xDF�j(��2�o�C�g�̈�)
     */

    /*
    // SJIS�����������������O�̑�1�o�C�g�����őł�������邩�ǂ����̖ڎ��p�e�X�g�R�[�h
    // ���ł�������邪�A�����݂̂̃`�F�b�N�ł͕s���B�擪���珇��2�o�C�g�̑g�𒲂ׂ�K�v������c�B
    for ($i = 0; $i <= 255; $i++) {
        if (self::isSjis1stByte($i)) {
            for ($j = 0; $j <= 255; $j++) {
                if (self::isSjisCrasherCode($j)) {
                    echo $i . ' '. pack('C*', $i) . pack('C*', $j) . "<br><br>";
                }
            }
        }
    }
    */

    // }}}
    // {{{ fixSjis()

    /**
     * SJIS������̖������A���o�C�g�ł���΁A�^�O������v���ƂȂ�̂ŃJ�b�g����B
     *
     * @access  public
     * @return  string
     */
    static public function fixSjis($str)
    {
        if (strlen($str) == 0) {
            return;
        }

        $un = unpack('C*', $str);

        $on_sjisfirst = false;
        $on_crasher = false;
        foreach ($un as $v) {
            if ($on_sjisfirst) {
                $on_sjisfirst = false;
                $on_crasher = false;
            } else {
                if (self::isSjis1stByte($v)) {
                    $on_sjisfirst = true;
                    $on_crasher = true;
                } elseif (self::isSjisCrasherCode($v)) {
                    $on_crasher = true;
                }
            }
        }

        if ($on_crasher) {
            $str = substr($str, 0, -1);
        }
        return $str;

        /*
        // �����݂̂��`�F�b�N���邽�߂̃R�[�h�B����ł͕s���B
        if (self::isSjisCrasherCode($un[$count]) && !self::isSjis1stByte($un[$count-1])) {
            $str = substr($str, 0, -1);
            return $str;
        }
        */
    }

    // }}}
    // {{{ isSjisCrasherCode()

    /**
     * SJIS�Ŗ����ɂ���Ɓi�����J�n�^�O�Ƃ������āj������������\���̂���R�[�h�͈̔́i10�i���j
     * ��1�o�C�g�͈͂����łȂ���2�o�C�g�͈͂ł�������������R�[�h�͂���
     * 129-159 224-252 �i�ڎ��Œ��ׂ��j
     * �ڎ��p�e�X�g�R�[�h
     * for ($i = 0; $i <= 255; $i++) {
     *    echo $i . ': '. pack('C*', $i) . "<br><br>";
     * }
     * �i�Q�l SJIS 2�o�C�g�R�[�h�͈͂̂�����1�o�C�g�R�[�h�ɓ��Ă͂܂�Ȃ��̂� 128-160 224-252�j
     *
     * @return  boolean  �R�[�h�ԍ������������͈͂ł���� true ��Ԃ�
     */
    static public function isSjisCrasherCode($int)
    {
        if (129 <= $int && $int <= 159 or 224 <= $int && $int <= 252) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ isSjis1stByte()

    /**
     * SJIS 2�o�C�g�̑�1�o�C�g�͈͂��ǂ����𒲂ׂ� 129�`159�A224�`239�i0x81�`0x9F�A0xE0�`0xEF�j
     *
     * @return  boolean  �R�[�h�ԍ�����1�o�C�g�͈͂ł���� true ��Ԃ�
     */
    static public function isSjis1stByte($int)
    {
        if (129 <= $int && $int <= 159 or 224 <= $int && $int <= 239) {
            return true;
        }
        return false;
    }

    // }}}
    // {{{ getSjisRegex()

    /**
     * SJIS�����Ƀ}�b�`���鐳�K�\����Ԃ�
     *
     * @return  string  
     * SJIS 2�o�C�g�̑�1�o�C�g�͈� 129�`159�A224�`239�i0x81�`0x9F�A0xE0�`0xEF�j
     * SJIS 2�o�C�g�̑�2�o�C�g�͈� 64�`126�A128�`252�i0x40�`0x7E�A0x80�`0xFC�j�i��1�o�C�g�͈͂����Ă���j
     */
    static public function getSjisRegex()
    {
        return "(?:[\x81-\x9f\xe0-\xef][\x40-\x7e\x80-\xfc])";
    }

	/* SJIS�R�[�h���܂ސ��K�\���Ō듮����N����������16�i�\�L�ɕϊ� */
	static public function fixSjisRegex ($str,$quotemeta=false) {
//		return preg_replace_callback("/".self::getSjisRegex()."|\C/",'StrSjis::fixSjisRegexChar',$str);
		$regex="[\x81-\x9f\xe0-\xef][\x40-\x7e\x80-\xfc]";
		if ($quotemeta) {
			$regex.="|[.\\+*?\[^\]($)]";
		}
		return preg_replace_callback("/{$regex}/",array('StrSjis','fixSjisRegexChar'),$str);
	}

	static public function fixSjisRegexChar ($chr) {
		if (strlen($chr[0]) > 1) {
			if (strpos('[{|',substr($chr[0],-1)) !== false) {	
				$ary=unpack('C2',$chr[0]);
				$hex=sprintf("\x%x\x%x",$ary[1],$ary[2]);
				//	trigger_error("���K�\�����́u{$chr[0]}�v���u{$hex}�v�ɒu�������܂����B",E_USER_NOTICE);
				return $hex;
			} else {
				return $chr[0];
			}
		} else {
			return '\\'.$chr[0];
		}
	}
    // {{{ getUnicodePattern()

    /**
     * Shift_JIS�̕������܂ސ��K�\���p�^�[����
     * PCRE��Unicode���[�h�p���K�\���p�^�[���ɕϊ�����
     *
     * @param   string  $pattern
     * @return  string
     */
    static public function toUnicodePattern($pattern)
    {
        $sjis_char_class_1st = '[\\x81-\\x9F\\xE0-\\xFC]';
        $sjis_char_class_2nd = '[\\x40-\\x7E\\x80-\\xFC]';
        $sjis_char_regex_1st = '\\\\x(8[1-9A-F]|[9E][0-9A-F]|F[0-9A-C])';
        $sjis_char_regex_2nd = '\\\\x([45689A-E][0-9A-F]|7[0-9A-E]|F[0-9A-C])';

        $pattern = preg_replace_callback("/{$sjis_char_class_1st}{$sjis_char_class_2nd}/",
                                         array(__CLASS__, '_sjisStringToUnicodePatternCb'),
                                         $pattern);
        $pattern = preg_replace_callback("/{$sjis_char_regex_1st}{$sjis_char_regex_2nd}/i",
                                         array(__CLASS__, '_sjisPatternToUnicodePatternCb'),
                                         $pattern);
        return $pattern;
    }

    // }}}
    // {{{ _sjisStringToUnicodePattern()

    /**
     * Shift_JIS��2�o�C�g������
     * Unicode�����Ƀ}�b�`���鐳�K�\���p�^�[���ɕϊ�����
     *
     * @param   array   $m
     * @return  string
     */
    static protected function _sjisStringToUnicodePatternCb($m)
    {
        $u = unpack('C2', mb_convert_encoding($m[0], 'UCS-2BE', 'SJIS-win'));
        return sprintf('\\x{%02X%02X}', $u[1], $u[2]);
    }

    // }}}
    // {{{ _sjisCodeToUnicodePattern()

    /**
     * Shift_JIS��2�o�C�g�����Ƀ}�b�`���鐳�K�\���p�^�[����
     * Unicode�����Ƀ}�b�`���鐳�K�\���p�^�[���ɕϊ�����
     *
     * @param   array   $m
     * @return  string
     */
    static protected function _sjisPatternToUnicodePatternCb($m)
    {
        $s = pack('C2', hexdec($m[1]), hexdec($m[2]));
        return self::_sjisStringToUnicodePatternCb(array($s));
    }

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
