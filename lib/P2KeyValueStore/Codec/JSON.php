<?php

// {{{ P2KeyValueStore_Codec_JSON

/**
 * �l��JSON�G���R�[�h�E�f�R�[�h����Codec
 *
 * ������͑Ó���UTF-8�V�[�P���X�łȂ���΂Ȃ�Ȃ��B
 */
class P2KeyValueStore_Codec_JSON implements P2KeyValueStore_Codec_Interface
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
     * �l��JSON�G���R�[�h����
     *
     * @param mixed $value
     * @return string
     */
    public function encodeValue($value)
    {
        return json_encode($value);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l��JSON�f�R�[�h����
     *
     * JSON�̃I�u�W�F�N�g��stdClass�I�u�W�F�N�g�ł͂Ȃ�
     * �A�z�z��ɕϊ�����
     *
     * @param string $json
     * @return mixed
     */
    public function decodeValue($json)
    {
        return json_decode($json, true);
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
