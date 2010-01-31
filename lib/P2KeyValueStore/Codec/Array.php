<?php

// {{{ P2KeyValueStore_Codec_Array

/**
 * �z����V���A���C�Y�E�A���V���A���C�Y����Codec
 *
 * ���ۂ͔񈳏k�V���A���C�YCodec�Ȃ̂Ŕz��ȊO�ɂ��Ή����Ă���B
 *
 * �V���A���C�Y��̃T�C�Y�����k��K�v�Ƃ���قǑ傫���Ȃ��ꍇ�Ɏg���B
 * �z��̗v�f�ɕ�������܂ޏꍇ�A�Ó���UTF-8�V�[�P���X�łȂ���΂Ȃ�Ȃ��B
 */
class P2KeyValueStore_Codec_Array implements P2KeyValueStore_Codec_Interface
{
    // {{{ encodeKey()

    /**
     * �L�[�͂��̂܂�
     *
     * @param string $key
     * @return string
     */
    public function encodeKey($key)
    {
        return $key;
    }

    // }}}
    // {{{ decodeKey()

    /**
     * �L�[�͂��̂܂�
     *
     * @param string $key
     * @return string
     */
    public function decodeKey($key)
    {
        return $key;
    }

    // }}}
    // {{{ encodeValue()

    /**
     * �l���V���A���C�Y����
     *
     * @param array $array
     * @return string
     */
    public function encodeValue($array)
    {
        return serialize($array);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l���A���V���A���C�Y����
     *
     * @param string $value
     * @return array
     */
    public function decodeValue($value)
    {
        return unserialize($value);
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
