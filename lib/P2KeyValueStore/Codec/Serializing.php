<?php

// {{{ P2KeyValueStore_Codec_Serializing

/**
 * �l���V���A���C�Y�E�A���V���A���C�Y����Codec
 */
class P2KeyValueStore_Codec_Serializing extends P2KeyValueStore_Codec_Compressing
{
    // {{{ encodeValue()

    /**
     * �l���V���A���C�Y����
     *
     * @param mixed $value
     * @return string
     */
    public function encodeValue($value)
    {
        return parent::encodeValue(serialize($value));
    }

    // }}}
    // {{{ decodeValue()

    /**
     * �l���A���V���A���C�Y����
     *
     * @param string $value
     * @return mixed
     */
    public function decodeValue($value)
    {
        return unserialize(parent::decodeValue($value));
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
