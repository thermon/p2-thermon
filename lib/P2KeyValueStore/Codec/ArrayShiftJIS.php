<?php

// {{{ P2KeyValueStore_Codec_ArrayShiftJIS

/**
 * �z����V���A���C�Y�E�A���V���A���C�Y����Codec
 *
 * ���ۂ͔񈳏k�V���A���C�YCodec�Ȃ̂Ŕz��ȊO�ɂ��Ή����Ă���B
 *
 * �V���A���C�Y��̃T�C�Y�����k��K�v�Ƃ���قǑ傫���Ȃ��ꍇ�Ɏg���B
 * �z��̗v�f�Ɋ܂܂�镶����͑Ó���Shift_JIS�V�[�P���X�łȂ���΂Ȃ炸�A
 * �L�[�͐��l��US-ASCII������ł��邱�Ƃ����҂���B
 */
class P2KeyValueStore_Codec_ArrayShiftJIS extends P2KeyValueStore_Codec_Array
{
    // {{{ encodeValue()

    /**
     * �l��UTF-8�ɕϊ�������A�V���A���C�Y����
     *
     * @param array $array
     * @return string
     */
    public function encodeValue($array)
    {
        mb_convert_variables('UTF-8', 'Shift_JIS', $array);
        return parent::encodeValue($array);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l���A���V���A���C�Y������AShift_JIS�ɕϊ�����
     *
     * @param string $value
     * @return array
     */
    public function decodeValue($value)
    {
        $array = parent::decodeValue($value);
        mb_convert_variables('SJIS-win', 'UTF-8', $array);
        return $array;
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
