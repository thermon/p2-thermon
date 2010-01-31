<?php

// {{{ P2KeyValueStore_Codec_Binary

/**
 * �o�C�i���f�[�^�pCodec
 */
class P2KeyValueStore_Codec_Binary implements P2KeyValueStore_Codec_Interface
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
     * �f�[�^��Base64�G���R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function encodeValue($value)
    {
        return base64_encode($value);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �f�[�^��Base64�f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function decodeValue($value)
    {
        return base64_decode($value);
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
