<?php

// {{{ BrdMenuIta

/**
* �{�[�h���j���[�N���X
*/
class BrdMenuIta
{
    // {{{ properties

    public $host;
    public $bbs;
    public $itaj;    // ��
    public $itaj_en;    // �����G���R�[�h��������
    public $itaj_ht;    // HTML�ŏo�͂�����i�t�B���^�����O�������́j

    // }}}
    // {{{ setItaj()

    public function setItaj($itaj)
    {
        $this->itaj = $itaj;
        $this->itaj_en = UrlSafeBase64::encode($this->itaj);
        $this->itaj_ht = htmlspecialchars($this->itaj, ENT_QUOTES);
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
