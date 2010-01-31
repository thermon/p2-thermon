<?php
/**
 * rep2 - �������ݗ����̃N���X
 */

require_once 'Pager/Pager.php';

// {{{ ResHist

/**
 * �������݃��O�̃N���X
 */
class ResHist
{
    // {{{ properties

    public $articles; // �N���X ResArticle �̃I�u�W�F�N�g���i�[����z��
    public $num; // �i�[���ꂽ BrdMenuCate �I�u�W�F�N�g�̐�

    public $resrange; // array( 'start' => i, 'to' => i, 'nofirst' => bool )

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     */
    public function __construct()
    {
        $this->articles = array();
        $this->num = 0;
    }

    // }}}
    // {{{ readLines()

    /**
     * �������݃��O�� lines ���p�[�X���ēǂݍ���
     *
     * @param  array    $lines
     * @return void
     */
    public function readLines(array $lines)
    {
        $n = 1;

        foreach ($lines as $aline) {
            $aResArticle = new ResArticle();

            $resar = explode('<>', $aline);
            $aResArticle->name      = $resar[0];
            $aResArticle->mail      = $resar[1];
            $aResArticle->daytime   = $resar[2];
            $aResArticle->msg       = $resar[3];
            $aResArticle->ttitle    = $resar[4];
            $aResArticle->host      = $resar[5];
            $aResArticle->bbs       = $resar[6];
            if (!($aResArticle->itaj = P2Util::getItaName($aResArticle->host, $aResArticle->bbs))) {
                $aResArticle->itaj  = $aResArticle->bbs;
            }
            $aResArticle->key       = $resar[7];
            $aResArticle->resnum    = $resar[8];

            $aResArticle->order = $n;

            $this->addRes($aResArticle);

            $n++;
        }
    }

    // }}}
    // {{{ addRes()

    /**
     * ���X��ǉ�����
     *
     * @return void
     */
    public function addRes(ResArticle $aResArticle)
    {
        $this->articles[] = $aResArticle;
        $this->num++;
    }

    // }}}
    // {{{ showArticles()

    /**
     * ���X�L����\������ PC�p
     *
     * @return void
     */
    public function showArticles()
    {
        global $_conf, $STYLE;

        // Pager ����
        $perPage = 100;
        $params = array(
            'mode'       => 'Jumping',
            'itemData'   => $this->articles,
            'perPage'    => $perPage,
            'delta'      => 10,
            'clearIfVoid' => true,
            'prevImg' => "�O��{$perPage}��",
            'nextImg' => "����{$perPage}��",
            //'separator' => '|',
            //'expanded' => true,
            'spacesBeforeSeparator' => 2,
            'spacesAfterSeparator' => 0,
        );

        $pager = Pager::factory($params);
        $links = $pager->getLinks();
        $data  = $pager->getPageData();

        if ($pager->links) {
            echo "<div>{$pager->links}</div>";
        }

        echo "<div class=\"thread\">\n";

        foreach ($data as $a_res) {
            $hd['daytime'] = htmlspecialchars($a_res->daytime, ENT_QUOTES);
            $hd['ttitle'] = htmlspecialchars(html_entity_decode($a_res->ttitle, ENT_COMPAT, 'Shift_JIS'), ENT_QUOTES);
            $hd['itaj'] = htmlspecialchars($a_res->itaj, ENT_QUOTES);

            $href_ht = "";
            if ($a_res->key) {
                if (empty($a_res->resnum) || $a_res->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $a_res->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = "{$_conf['read_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}{$ls_q}{$_conf['k_at_a']}&amp;nt={$time}{$footer_q}";
            }
            $info_view_ht = <<<EOP
        <a href="info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}{$_conf['k_at_a']}" target="_self" onclick="return OpenSubWin('info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}&amp;popup=1',{$STYLE['info_pop_size']},0,0)">���</a>
EOP;

            $res_ht = "<div class=\"res\">\n";
            $res_ht .= "<div class=\"res-header\"><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$a_res->order},,,,{$hd['daytime']}\"> ";
            $res_ht .= "{$a_res->order} �F"; // �ԍ�
            $res_ht .= '<span class="name"><b>' . htmlspecialchars($a_res->name, ENT_QUOTES) . '</b></span> �F'; // ���O
            // ���[��
            if ($a_res->mail) {
                $res_ht .= htmlspecialchars($a_res->mail, ENT_QUOTES) . ' �F';
            }
            $res_ht .= "{$hd['daytime']}</div>\n"; // ���t��ID
            // ��
            $res_ht .= "<div class=\"res-hist-board\"><a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$_conf['k_at_a']}\" target=\"subject\">{$hd['itaj']}</a> / ";
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\"><b>{$hd['ttitle']}</b></a> - {$info_view_ht}";
            } elseif ($hd['ttitle']) {
                $res_ht .= "<b>{$hd['ttitle']}</b>";
            }
            $res_ht .= "</div>\n";
            $res_ht .= "<div class=\"message\">{$a_res->msg}</div>\n"; // ���e
            $res_ht .= "</div>\n";

            echo $res_ht;
            flush();
        }

        echo "</div>\n";

        if ($pager->links) {
            echo "<div>{$pager->links}</div>";
        }
    }

    // }}}
    // {{{ showNaviK()

    /**
     * �g�їp�i�r��\������
     * �\���͈͂��Z�b�g�����
     */
    public function showNaviK($position)
    {
        global $_conf;

        // �\��������
        $list_disp_all_num = $this->num;
        $list_disp_range = $_conf['mobile.rnum_range'];

        if ($_GET['from']) {
            $list_disp_from = $_GET['from'];
            if ($_GET['end']) {
                $list_disp_range = $_GET['end'] - $list_disp_from + 1;
                if ($list_disp_range < 1) {
                    $list_disp_range = 1;
                }
            }
        } else {
            $list_disp_from = 1;
            /*
            $list_disp_from = $this->num - $list_disp_range + 1;
            if ($list_disp_from < 1) {
                $list_disp_from = 1;
            }
            */
        }
        $disp_navi = P2Util::getListNaviRange($list_disp_from, $list_disp_range, $list_disp_all_num);

        $this->resrange['start'] = $disp_navi['from'];
        $this->resrange['to'] = $disp_navi['end'];
        $this->resrange['nofirst'] = false;

        if ($disp_navi['from'] > 1) {
            if ($position == 'footer') {
                $mae_ht = <<<EOP
<a href="read_res_hist.php?from={$disp_navi['mae_from']}{$_conf['k_at_a']}"{$_conf['k_accesskey_at']['prev']}>{$_conf['k_accesskey_st']['prev']}�O</a>
EOP;
            } else {
                $mae_ht = <<<EOP
<a href="read_res_hist.php?from={$disp_navi['mae_from']}{$_conf['k_at_a']}">�O</a>
EOP;
            }
        }
        if ($disp_navi['end'] < $list_disp_all_num) {
            if ($position == 'footer') {
                $tugi_ht = <<<EOP
<a href="read_res_hist.php?from={$disp_navi['tugi_from']}{$_conf['k_at_a']}"{$_conf['k_accesskey_at']['next']}>{$_conf['k_accesskey_st']['next']}��</a>
EOP;
            } else {
                $tugi_ht = <<<EOP
<a href="read_res_hist.php?from={$disp_navi['tugi_from']}{$_conf['k_at_a']}">��</a>
EOP;
            }
        }

        if (!$disp_navi['all_once']) {
            echo "<div class=\"navi\">{$disp_navi['range_st']} {$mae_ht} {$tugi_ht}</div>\n";
        }
    }

    // }}}
    // {{{ showArticlesK()

    /**
     * ���X�L����\�����郁�\�b�h �g�їp
     *
     * @return void
     */
    public function showArticlesK()
    {
        global $_conf;

        foreach ($this->articles as $a_res) {
            $hd['daytime'] = htmlspecialchars($a_res->daytime, ENT_QUOTES);
            $hd['ttitle'] = htmlspecialchars(html_entity_decode($a_res->ttitle, ENT_COMPAT, 'Shift_JIS'), ENT_QUOTES);
            $hd['itaj'] = htmlspecialchars($a_res->itaj, ENT_QUOTES);

            if ($a_res->order < $this->resrange['start'] or $a_res->order > $this->resrange['to']) {
                continue;
            }

            $href_ht = "";
            if ($a_res->key) {
                if (empty($a_res->resnum) || $a_res->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $a_res->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = $_conf['read_php'] . "?host=" . $a_res->host . "&amp;bbs=" . $a_res->bbs . "&amp;key=" . $a_res->key . $ls_q . "{$_conf['k_at_a']}&amp;nt={$time}={$footer_q}";
            }

            // �傫������
            if (!$_GET['k_continue']) {
                $msg = $a_res->msg;
                if (strlen($msg) > $_conf['mobile.res_size']) {
                    $msg = substr($msg, 0, $_conf['mobile.ryaku_size']);

                    // ������<br>������Ύ�菜��
                    if (substr($msg, -1) == ">") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "r") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "b") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "<") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }

                    $msg = $msg."  ";
                    $a_res->msg = $msg."<a href=\"read_res_hist?from={$a_res->order}&amp;end={$a_res->order}&amp;k_continue=1{$_conf['k_at_a']}\">��</a>";
                }
            }

            $res_ht = "[$a_res->order]"; // �ԍ�
            $res_ht .= htmlspecialchars($a_res->name, ENT_QUOTES) . ':'; // ���O
            // ���[��
            if ($a_res->mail) {
                $res_ht .= htmlspecialchars($a_res->mail, ENT_QUOTES) . ':';
            }
            $res_ht .= "{$hd['daytime']}<br>\n"; // ���t��ID
            $res_ht .= "<a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$_conf['k_at_a']}\">{$hd['itaj']}</a> / ";
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\">{$hd['ttitle']}</a>\n";
            } elseif ($hd['ttitle']) {
                $res_ht .= "{$hd['ttitle']}\n";
            }

            // �폜
            //$res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$a_res->order},,,,{$hd['daytime']}\"> ";
            $from_q = isset($_GET['from']) ? '&amp;from=' . $_GET['from'] : '';
            $dele_ht = "[<a href=\"read_res_hist.php?checked_hists[]={$a_res->order},,,," . rawurlencode($a_res->daytime) . "{$from_q}{$_conf['k_at_a']}\">�폜</a>]";
            $res_ht .= $dele_ht;

            $res_ht .= '<br>';
            $res_ht .= "{$a_res->msg}<hr>\n"; // ���e

            echo $res_ht;
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
