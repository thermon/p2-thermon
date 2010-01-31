<?php
/**
 * rep2 - �X���b�h���X�g���\�[�g����֐��Q
 */

// {{{ �V���\�[�g

/**
 * �V���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum < 0) && ($b->unum < 0)) {
            return ($a->torder > $b->torder) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �V���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_midoku(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $unum = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $unum[] = ($t->unum < 0) ? -1 : $t->unum;
        $torder[] = $t->torder;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $unum,      SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ ���X���\�[�g

/**
 * ���X���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_res($a, $b)
{
    if ($a->rescount == $b->rescount) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return ($a->rescount < $b->rescount) ? 1 : -1;
    }
}

/**
 * ���X���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_res(ThreadList $aThreadList, $reverse = false)
{
    $rescount = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $rescount[] = $t->rescount;
        $torder[] = $t->torder;
    }

    array_multisort($rescount,  SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �^�C�g���\�[�g

/**
 * �^�C�g���\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_title($a, $b)
{
    if ($a->ttitle == $b->ttitle) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return strcmp($a->ttitle, $b->ttitle);
    }
}

/**
 * �^�C�g���\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_title(ThreadList $aThreadList, $reverse = false)
{
    $ttitle = array();
    $torder = array();

    if ($GLOBALS['_conf']['cmp_title_norm']) {
        foreach ($aThreadList->threads as $t) {
            $ttitle[] = strtoupper(mb_convert_kana($t->ttitle, 'KVas'));
            $torder[] = $t->torder;
        }
    } else {
        foreach ($aThreadList->threads as $t) {
            $ttitle[] = $t->ttitle;
            $torder[] = $t->torder;
        }
    }

    array_multisort($ttitle,    SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �\�[�g

/**
 * �\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_ita($a, $b)
{
    if ($a->host != $b->host) {
        return strcmp($a->host, $b->host);
    } else {
        if ($a->itaj != $b->itaj) {
            return strcmp($a->itaj, $b->itaj);
        } else {
            return ($a->torder > $b->torder) ? 1 : -1;
        }
    }
}

/**
 * �\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_ita(ThreadList $aThreadList, $reverse = false)
{
    $host = array();
    $itaj = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $host[] = $t->host;
        $itaj[] = $t->itaj;
        $torder[] = $t->torder;
    }

    array_multisort($host,      SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $itaj,      SORT_STRING,    $reverse ? SORT_DESC : SORT_ASC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ ���C�Ƀ\�[�g

/**
 * ���C�Ƀ\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_fav($a, $b)
{
    if ($a->fav == $b->fav) {
        return ($a->torder > $b->torder) ? 1 : -1;
    } else {
        return ($a->fav < $b->fav) ? 1 : -1;
    }
}

/**
 * ���C�Ƀ\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_fav(ThreadList $aThreadList, $reverse = false)
{
    $fav = array();
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $fav[] = $t->fav;
        $torder[] = $t->torder;
    }

    array_multisort($fav,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �V�����X�D��̐����\�[�g

/**
 * �V�����X�D��̐����\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_dayres_midoku($a, $b)
{
    if ($a->new == $b->new) {
        if (($a->unum == $b->unum) or ($a->unum >= 1) && ($b->unum >= 1)) {
            return ($a->dayres < $b->dayres) ? 1 : -1;
        } else {
            return ($a->unum < $b->unum) ? 1 : -1;
        }
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �V�����X�D��̐����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_dayres_midoku(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $hasu = array();
    $dayres = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $hasu[] = ($t->unum >= 1) ? 1 : $t->unum;
        $dayres[] = $t->dayres;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $hasu,      SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $dayres,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �����\�[�g

/**
 * �����\�[�g (usort�̃R�[���o�b�N�֐�)
 *
 * @param   Thread $a
 * @param   Thread $b
 * @return  int
 */
function p2_cmp_dayres($a, $b)
{
    if ($a->new == $b->new) {
        return ($a->dayres < $b->dayres) ? 1 : -1;
    } else {
        return ($a->new < $b->new) ? 1 : -1;
    }
}

/**
 * �����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_dayres(ThreadList $aThreadList, $reverse = false)
{
    $new = array();
    $dayres = array();

    foreach ($aThreadList->threads as $t) {
        $new[] = $t->new;
        $dayres[] = $t->dayres;
    }

    array_multisort($new,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $dayres,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ key�\�[�g

/**
 * key�\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function p2_cmp_key($a, $b)
{
    return ($a->key < $b->key) ? 1 : -1;
}

/**
 * key�\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_key(ThreadList $aThreadList, $reverse = false)
{
    $key = array();

    foreach ($aThreadList->threads as $t) {
        $key[] = $t->key;
    }

    array_multisort($key,       SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ No.�\�[�g

/**
 * No.�\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function p2_cmp_no($a, $b)
{
    return ($a->torder > $b->torder) ? 1 : -1;
}

/**
 * No.�\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_no(ThreadList $aThreadList, $reverse = false)
{
    $torder = array();

    foreach ($aThreadList->threads as $t) {
        $torder[] = $t->torder;
    }

    array_multisort($torder,    SORT_NUMERIC,   $reverse ? SORT_DESC : SORT_ASC,
                    $aThreadList->threads
                    );
}

// }}}
// {{{ �ގ����\�[�g

/**
 * �ގ����\�[�g (usort�̃R�[���o�b�N�֐�)
 */
function p2_cmp_similarity($a, $b)
{
    if ($a->similarity == $b->similarity) {
        return ($a->key < $b->key) ? 1 : -1;
    } else {
        return ($a->similarity < $b->similarity) ? 1 : -1;
    }
}

/**
 * �ގ����\�[�g (array_multisort��)
 *
 * @param   ThreadList $aThreadList
 * @param   bool $reverse
 * @return  void
 */
function p2_multi_cmp_similarity(ThreadList $aThreadList, $reverse = false)
{
    $similarity = array();
    $key = array();

    foreach ($aThreadList->threads as $t) {
        $similarity[] = $t->similarity;
        $key[] = $t->key;
    }

    array_multisort($similarity,    SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $key,           SORT_NUMERIC,   $reverse ? SORT_ASC : SORT_DESC,
                    $aThreadList->threads
                    );
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
