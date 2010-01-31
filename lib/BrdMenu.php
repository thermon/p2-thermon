<?php

// {{{ BrdMenu

/**
 * rep2 - �{�[�h���j���[�N���X for menu.php
 */
class BrdMenu
{
    // {{{ properties

    public $categories;    // �N���X BrdMenuCate �̃I�u�W�F�N�g���i�[����z��
    public $num;           // �i�[���ꂽ BrdMenuCate �I�u�W�F�N�g�̐�
    public $format;        // html�`�����Abrd�`����("html", "brd")
    public $cate_match;    // �J�e�S���[�}�b�`�`��
    public $ita_match;     // �}�b�`�`��
    public $matches;       // �}�b�`���� BrdMenuIta �I�u�W�F�N�g���i�[����z��

    // }}}
    // {{{ constructor

    public function __construct()
    {
        $this->categories = array();
        $this->num = 0; 
        $this->matches = array();
    }

    // }}}
    // {{{ addBrdMenuCate()

    /**
     * �J�e�S���[��ǉ�����
     */
    public function addBrdMenuCate(BrdMenuCate $aBrdMenuCate)
    {
        $this->categories[] = $aBrdMenuCate;
        $this->num++;
    }

    // }}}
    // {{{ setBrdMatch()

    /**
    * �p�^�[���}�b�`�̌`����o�^����
    */
    public function setBrdMatch($brdName)
    {
        // html�`��
        if (preg_match('/(html?|cgi)$/', $brdName)) {
            $this->format = 'html';
            $this->cate_match = '{<B>(.+)</B><BR>.*$}i';
            $this->ita_match = '{^<A HREF="?(http://(.+)/([^/]+)/([^/]+\\.html?)?)"?( target="?_blank"?)?>(.+)</A>(<br>)?$}i';
        // brd�`��
        } else {
            $this->format = 'brd';
            $this->cate_match = "/^(.+)\t([0-9])\$/";
            $this->ita_match = "/^\t?(.+)\t(.+)\t(.+)\$/";
        }
    }

    // }}}
    // {{{ setBrdList()

    /**
    * �f�[�^��ǂݍ���ŁA�J�e�S���Ɣ�o�^����
    */
    public function setBrdList($data)
    {
        global $_conf;

        if (empty($data)) { return false; }

        $do_filtering = !empty($GLOBALS['words_fm']);

        // ���OURL���X�g
        $not_bbs_list = array("http://members.tripod.co.jp/Backy/del_2ch/");

        foreach ($data as $v) {
            $v = rtrim($v);

            // �J�e�S����T��
            if (preg_match($this->cate_match, $v, $matches)) {
                $aBrdMenuCate = new BrdMenuCate($matches[1]);
                if ($this->format == 'brd') {
                    $aBrdMenuCate->is_open = $matches[2];
                }
                $this->addBrdMenuCate($aBrdMenuCate);

            // ��T��
            } elseif (preg_match($this->ita_match, $v, $matches)) {
                // html�`���Ȃ珜�OURL���O��
                if ($this->format == 'html') {
                    foreach ($not_bbs_list as $not_a_bbs) {
                        if ($not_a_bbs == $matches[1]) { continue 2; }
                    }
                }
                $aBrdMenuIta = new BrdMenuIta();
                // html�`��
                if ($this->format == 'html') {
                    $aBrdMenuIta->host = $matches[2];
                    $aBrdMenuIta->bbs = $matches[3];
                    $itaj_match = $matches[6];
                // brd�`��
                } else {
                    $aBrdMenuIta->host = $matches[1];
                    $aBrdMenuIta->bbs = $matches[2];
                    $itaj_match = $matches[3];
                }
                $aBrdMenuIta->setItaj(rtrim($itaj_match));

                // {{{ �����}�b�`

                // and����
                if ($do_filtering) {

                    $no_match = false;

                    foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                        $target = $aBrdMenuIta->itaj."\t".$aBrdMenuIta->bbs;
                        if (!StrCtl::filterMatch($word_fm_ao, $target)) {
                            $no_match = true;
                        }
                    }

                    if (!$no_match) {
                        $this->categories[$this->num-1]->ita_match_num++;
                        $GLOBALS['ita_mikke']['num']++;
                        $GLOBALS['ita_mikke']['host'] = $aBrdMenuIta->host;
                        $GLOBALS['ita_mikke']['bbs'] = $aBrdMenuIta->bbs;
                        $GLOBALS['ita_mikke']['itaj_en'] = $aBrdMenuIta->itaj_en;

                        // �}�[�L���O
                        if ($_conf['ktai'] && is_string($_conf['k_filter_marker'])) {
                            $aBrdMenuIta->itaj_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aBrdMenuIta->itaj, $_conf['k_filter_marker']);
                        } else {
                            $aBrdMenuIta->itaj_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aBrdMenuIta->itaj);
                        }

                        // �}�b�`�}�[�L���O�Ȃ���΁ibbs�Ń}�b�`�����Ƃ��j�A�S���}�[�L���O
                        if ($aBrdMenuIta->itaj_ht == $aBrdMenuIta->itaj) {
                            $aBrdMenuIta->itaj_ht = '<b class="filtering">'.$aBrdMenuIta->itaj_ht.'</b>';
                        }

                        $this->matches[] = $aBrdMenuIta;

                    // ������������Ȃ��āA����Ɍg�т̎�
                    } else {
                        if ($_conf['ktai']) {
                            continue;
                        }
                    }
                }

                // }}}

                if ($this->num) {
                    $this->categories[$this->num-1]->addBrdMenuIta($aBrdMenuIta);
                }
            }
        }
    }

    // }}}
    // {{{ makeBrdFile()

    /**
    * brd�t�@�C���𐶐�����
    *
    * @return    string    brd�t�@�C���̃p�X
    */
    public function makeBrdFile($cachefile)
    {
        global $_conf, $word;

        $p2brdfile = $cachefile.".p2.brd";
        FileCtl::make_datafile($p2brdfile, $_conf['p2_perm']);
        $data = FileCtl::file_read_lines($cachefile);
        $this->setBrdMatch($cachefile); // �p�^�[���}�b�`�`����o�^
        $this->setBrdList($data);       // �J�e�S���[�Ɣ��Z�b�g
        if ($this->categories) {
            foreach ($this->categories as $cate) {
                if ($cate->num > 0) {
                    $cont .= $cate->name."\t0\n";
                    foreach ($cate->menuitas as $mita) {
                        $cont .= "\t{$mita->host}\t{$mita->bbs}\t{$mita->itaj}\n";
                    }
                }
            }
        }

        if ($cont) {
            if (FileCtl::file_write_contents($p2brdfile, $cont) === false) {
                p2die("{$p2brdfile} ���X�V�ł��܂���ł���");
            }
            return $p2brdfile;
        } else {
            if (!$word) {
                P2Util::pushInfoHtml("<p>p2 error: {$cachefile} ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>");
            }
            return false;
        }
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
