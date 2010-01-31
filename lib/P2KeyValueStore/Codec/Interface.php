<?php

// {{{ P2KeyValueStore_Codec_Interface

/**
 * P2KeyValueStore���g��Codec�̃C���^�[�t�F�C�X��`
 */
interface P2KeyValueStore_Codec_Interface
{
    // {{{ encodeKey()

    /**
     * �L�[��UTF-8 (or US-ASCII) ������ɃG���R�[�h����
     *
     * @param string $key
     * @return string
     */
    public function encodeKey($key);

    // }}}
    // {{{ decodeKey()

    /**
     * �L�[���f�R�[�h����
     *
     * @param string $key
     * @return string
     */
    public function decodeKey($key);

    // }}}
    // {{{ encodeValue()

    /**
     * �l��UTF-8 (or US-ASCII) ������ɃG���R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function encodeValue($value);

    // }}}
    // {{{ decodeValue()

    /**
     * �l���f�R�[�h����
     *
     * @param string $value
     * @return string
     */
    public function decodeValue($value);

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
