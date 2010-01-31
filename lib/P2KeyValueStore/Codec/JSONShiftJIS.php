<?php

// {{{ P2KeyValueStore_Codec_JSONShiftJIS

/**
 * �l��JSON�G���R�[�h�E�f�R�[�h����Codec
 *
 * ������͑Ó���Shift_JIS�V�[�P���X�łȂ���΂Ȃ炸�A
 * �z��̃L�[�͐��l��US-ASCII������ł��邱�Ƃ����҂���B
 */
class P2KeyValueStore_Codec_JSONShiftJIS extends P2KeyValueStore_Codec_JSON
{
    // {{{ encodeValue()

    /**
     * �l��UTF-8�ɕϊ�������AJSON�G���R�[�h����
     *
     * @param mixed $value
     * @return string
     */
    public function encodeValue($value)
    {
        mb_convert_variables('UTF-8', 'Shift_JIS', $value);
        return parent::encodeValue($value);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l��JSON�f�R�[�h������AShift_JIS�ɕϊ�����
     *
     * @param string $value
     * @return mixed
     */
    public function decodeValue($json)
    {
        $value = parent::decodeValue($json);
        mb_convert_variables('SJIS-win', 'UTF-8', $value);
        return $value;
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
