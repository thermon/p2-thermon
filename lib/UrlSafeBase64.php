<?php

// {{{ UrlSafeBase64

/**
 * �G�X�P�[�v������URL�ɖ��ߍ��߂�Base64�ϊ��N���X
 */
class UrlSafeBase64
{
    // {{{ decode()

    /**
     * URL-safe Base64 �f�R�[�h
     *
     * @param string $str
     * @return string
     */
    static public function decode($str)
    {
        $mod = strlen($str) % 4;
        if ($mod) {
            $str .= str_repeat('=', 4 - $mod);
        }
        return base64_decode(strtr($str, '-_', '+/'), true);
    }

    // }}}
    // {{{ encode()

    /**
     * URL-safe Base64 �G���R�[�h
     *
     * @param string $str
     * @return string
     */
    static public function encode($str)
    {
        return strtr(rtrim(base64_encode($str), '='), '+/', '-_');
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
