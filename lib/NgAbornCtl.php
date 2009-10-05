<?php
/**
 * rep2 - NG���ځ[��𑀍삷��N���X
 */

// {{{ GLOBALS

$GLOBALS['ngaborns_hits'] = array(
    'aborn_chain'   => 0,
    'aborn_freq'    => 0,
    'aborn_mail'    => 0,
    'aborn_id'      => 0,
    'aborn_msg'     => 0,
    'aborn_name'    => 0,
    'aborn_res'     => 0,
    'ng_chain'      => 0,
    'ng_freq'       => 0,
    'ng_id'         => 0,
    'ng_mail'       => 0,
    'ng_msg'        => 0,
    'ng_name'       => 0,
);

// }}}
// {{{ NgAbornCtl

class NgAbornCtl
{
    // {{{ saveNgAborns()

    /**
     * ���ځ[��&NG���[�h�ݒ��ۑ�����
     */
    static public function saveNgAborns()
    {
        global $ngaborns, $ngaborns_hits;
        global $_conf;

        // HIT�������̂ݍX�V����
        if ($GLOBALS['ngaborns_hits']) {

            $lasttime = date('Y/m/d G:i');
            if ($_conf['ngaborn_daylimit']) {
                $daylimit = time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit'];
            } else {
                $daylimit = 0;
            }
            $errors = '';

            foreach ($ngaborns_hits as $code => $v) {

                if (isset($ngaborns[$code]) && !empty($ngaborns[$code]['data'])) {

                    // �X�V���ԂŃ\�[�g����
                    usort($ngaborns[$code]['data'], array('NgAbornCtl', 'cmpLastTime'));

                    $cont = '';
                    foreach ($ngaborns[$code]['data'] as $a_ngaborn) {

                        if (empty($a_ngaborn['lasttime']) || $a_ngaborn['lasttime'] == '--') {
                            // �Â��f�[�^���폜����s����A���Ɍ��݂̓�����t�^
                            $a_ngaborn['lasttime'] = $lasttime;
                         } else {
                            // �K�v�Ȃ炱���ŌÂ��f�[�^�̓X�L�b�v�i�폜�j����
                            if ($daylimit > 0 && strtotime($a_ngaborn['lasttime']) < $daylimit) {
                                continue;
                            }
                        }

                        $cont .= sprintf("%s\t%s\t%d\n", $a_ngaborn['cond'], $a_ngaborn['lasttime'], $a_ngaborn['hits']);
                    } // foreach

                    /*
                    echo "<pre>";
                    echo $cont;
                    echo "</pre>";
                    */

                    // ��������

                    $fp = @fopen($ngaborns[$code]['file'], 'wb');
                    if (!$fp) {
                        $errors .= "cannot write. ({$ngaborns[$code]['file']})\n";
                    } else {
                        flock($fp, LOCK_EX);
                        fputs($fp, $cont);
                        flock($fp, LOCK_UN);
                        fclose($fp);
                    }

                } // if

            } // foreach

            if ($errors != '') {
                p2die('NG���ځ[��t�@�C�����X�V�ł��܂���ł����B', $errors);
            }
        }
        return true;
    }

    // }}}
    // {{{ cmpLastTime()

    /**
     * NG���ځ[��HIT�L�^���X�V���ԂŃ\�[�g����
     */
    static public function cmpLastTime($a, $b)
    {
        if (empty($a['lasttime']) || empty($b['lasttime'])) {
            return strcmp($a['lasttime'], $b['lasttime']);
        }
        if ($a['lasttime'] == $b['lasttime']) {
            return $b['hits'] - $a['hits'];
        }
        return strtotime($b['lasttime']) - strtotime($a['lasttime']);
    }

    // }}}
    // {{{ loadNgAborns()

    /**
     * ���ځ[��&NG���[�h�ݒ��ǂݍ���
     */
    static public function loadNgAborns()
    {
        $ngaborns = array();

        $ngaborns['aborn_res'] = self::_readNgAbornFromFile('p2_aborn_res.txt'); // ���ꂾ���������i���قȂ�
        $ngaborns['aborn_name'] = self::_readNgAbornFromFile('p2_aborn_name.txt');
        $ngaborns['aborn_mail'] = self::_readNgAbornFromFile('p2_aborn_mail.txt');
        $ngaborns['aborn_msg'] = self::_readNgAbornFromFile('p2_aborn_msg.txt');
        $ngaborns['aborn_id'] = self::_readNgAbornFromFile('p2_aborn_id.txt');
        $ngaborns['ng_name'] = self::_readNgAbornFromFile('p2_ng_name.txt');
        $ngaborns['ng_mail'] = self::_readNgAbornFromFile('p2_ng_mail.txt');
        $ngaborns['ng_msg'] = self::_readNgAbornFromFile('p2_ng_msg.txt');
        $ngaborns['ng_id'] = self::_readNgAbornFromFile('p2_ng_id.txt');
        // +Wiki
        $ngaborns['aborn_be'] = self::_readNgAbornFromFile('p2_aborn_be.txt');
        $ngaborns['ng_be'] = self::_readNgAbornFromFile('p2_ng_be.txt');

        return $ngaborns;
    }

    // }}}
    // {{{ _readNgAbornFromFile()

    /**
     * readNgAbornFromFile
     */
    static private function _readNgAbornFromFile($filename)
    {
        global $_conf;

        $array = array(
            'file' => $_conf['pref_dir'] . '/' . $filename,
            'data' => array(),
        );

        if ($lines = FileCtl::file_read_lines($array['file'])) {
            foreach ($lines as $l) {
                $lar = explode("\t", trim($l));
                if (strlen($lar[0]) == 0) {
                    continue;
                }
                $ar = array(
                    'cond' => $lar[0], // ��������
                    'word' => $lar[0], // �Ώە�����
                    'lasttime' => null, // �Ō��HIT��������
                    'hits' => 0, // HIT��
                );
                isset($lar[1]) && $ar['lasttime'] = $lar[1];
                isset($lar[2]) && $ar['hits'] = (int) $lar[2];
                if ($filename == 'p2_aborn_res.txt') {
                    continue;
                }

                // ����
                if (preg_match('!<bbs>(.+?)</bbs>!', $ar['word'], $matches)) {
                    $ar['bbs'] = explode(',', $matches[1]);
                }
                $ar['word'] = preg_replace('!<bbs>(.*)</bbs>!', '', $ar['word']);

                // �^�C�g������
                if (preg_match('!<title>(.+?)</title>!', $ar['word'], $matches)) {
                    $ar['title'] = $matches[1];
                }
                $ar['word'] = preg_replace('!<title>(.*)</title>!', '', $ar['word']);

                // ���K�\��
                if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $ar['word'], $matches)) {
                    // �}�b�`���O�֐��ƃp�^�[����ݒ�
                    if ($matches[1] == 'regex') {
                        if (P2_MBREGEX_AVAILABLE) {
                            $ar['regex'] = 'mb_ereg';
                            $ar['word'] = $matches[3];
                        } else {
                            $ar['regex'] = 'preg_match';
                            $ar['word'] = '/' . str_replace('/', '\\/', $matches[3]) . '/';
                        }
                    } else {
                        $ar['regex'] = $matches[1];
                        $ar['word'] = $matches[3];
                    }
                    // �啶���������𖳎�
                    if ($matches[2] && strpos($matches[2], 'i') !== false) {
                        if ($ar['regex'] == 'mb_ereg') {
                            $ar['regex'] = 'mb_eregi';
                        } else {
                            $ar['word'] .= 'i';
                        }
                    }
                // �啶���������𖳎�
                } elseif (preg_match('/^<i>(.+)$/', $ar['word'], $matches)) {
                    $ar['word'] = $matches[1];
                    $ar['ignorecase'] = true;
                }

                $array['data'][] = $ar;
            }

        }
        return $array;
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
