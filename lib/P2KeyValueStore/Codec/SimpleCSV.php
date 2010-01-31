<?php

// {{{ P2KeyValueStore_Codec_SimpleCSV

/**
 * �z���CSV�̑��ݕϊ�������Codec
 *
 * �P�ɒl���J���}�� implode()/explode() ���邾���ŁA�f�[�^�̌��؂͂��Ȃ��B
 * �z��̑S�v�f��UTF-8 (�܂���US-ASCII) �̕�����łȂ���΂Ȃ�Ȃ��B
 */
class P2KeyValueStore_Codec_SimpleCSV implements P2KeyValueStore_Codec_Interface
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
     * �l����������
     *
     * @param array $array
     * @return string
     */
    public function encodeValue($array)
    {
        return implode(',', $array);
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l�𕪊�����
     *
     * @param string $value
     * @return array
     */
    public function decodeValue($value)
    {
        return explode(',', $value);
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
