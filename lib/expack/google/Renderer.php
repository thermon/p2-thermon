<?php
require_once 'Pager/Pager.php';

// {{{ Google_Renderer

class Google_Renderer
{
    // {{{ constants

    /**
     * �������ʃu���b�N�̊J�n�^�O
     */
    const OPENER = '<table class="threadlist" cellspacing="0">';

    /**
     * �������ʁE�w�b�_
     */
    const HEADER = '<tr class="tableheader">
    <th class="t">���</th>
    <th class="t">�^�C�g��</th>
    <th class="t">�͈�</th>
    <th class="t">��</th>
</tr>';

    /**
     * �������ʁE�e�A�C�e��
     */
    const BODY = '<tr class="%s">
    <td class="t">%s</td>
    <td class="t">%s</td>
    <td class="tn">%s</td>
    <td class="t">%s</td>
</tr>';

    /**
     * �������ʁE�G���[
     */
    const ERROR = '<tr><td colspan="4" align="center">%s</td></tr>';

    /**
     * �������ʁE�t�b�^
     */
    const FOOTER = '<tr class="tableheader">
    <td class="t" colspan="4" style="text-align:center;">%d-%d / %d hits.</td>
</tr>';

    /**
     * �������ʃu���b�N�̏I���^�O
     */
    const CLOSER = '</table>';

    // }}}
    // {{{ properties

    /**
     * ���݂̍s��
     *
     * @var int
     */
    private $_rows = 0;

    // }}
    // {{{ _getRowClass()

    /**
     * ��s�������s���̎��ʎq��Ԃ�
     */
    private function _getRowClass()
    {
        return (++$this->_rows % 2) ? 'r1' : 'r2';
    }

    // }}}
    // {{{ printSearchResult()

    /**
     * �������ʂ��o�͂���
     *
     * @return void
     */
    public function printSearchResult($result, $word, $perPage, $start, $totalItems)
    {
        echo self::OPENER;
        echo '<thead>';
        $this->printSearchResultHeader();
        echo '</thead><tbody>';
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $id => $val) {
                $this->printSearchResultBody($id, $val, $this->_getRowClass());
            }
        } elseif (is_string($result) && strlen($result) > 0) {
            printf(self::ERROR, $result);
        }
        echo '</tbody><tfoot>';
        $this->printSearchResultFooter($perPage, $start, $totalItems);
        echo '</tfoot>';
        echo self::CLOSER;
    }

    // }}}
    // {{{ printSearchResultHeader()

    /**
     * �������ʂ̃w�b�_���o�͂���
     *
     * @return void
     */
    public function printSearchResultHeader()
    {
        echo self::HEADER;
    }

    // }}}
    // {{{ printSearchResultBody()

    /**
     * �������ʂ̖{�̂��o�͂���
     *
     * @return void
     */
    public function printSearchResultBody($id, $val, $rc)
    {
        $eh = "onmouseover=\"gShowPopUp('s%s',event)\" onmouseout=\"gHidePopUp('s%s')\"";
        $title = "<a class=\"thre_title\" href=\"%s\" {$eh} target=\"%s\" >%s</a>";

        $type_col  = $val['type'];
        $title_col = sprintf($title, $val['url'], $id, $id, $val['target'], $val['title']);
        $range_col = ($val['ls']  !== '') ? $val['ls']  : '&nbsp;';
        $ita_col   = ($val['ita'] !== '') ? $val['ita'] : '&nbsp;';

        printf(self::BODY, $rc, $type_col, $title_col, $range_col, $ita_col);
    }

    // }}}
    // {{{ printSearchResultFooter()

    /**
     * �������ʂ̃t�b�^���o�͂���
     *
     * @return void
     */
    public function printSearchResultFooter($perPage, $start, $totalItems)
    {
        $from = ($totalItems > 0) ? ($start + 1) : 0;
        $to   = min($start + $perPage, $totalItems);

        printf(self::FOOTER, $from, $to, $totalItems);
    }

    // }}}
    // {{{ printPopup()

    /**
     * �|�b�v�A�b�v�p�B���v�f���o�͂���
     *
     * @return void
     */
    public function printPopup($popups)
    {
        if (!is_array($popups) || count($popups) == 0) {
            return;
        }

        $eh = "onmouseover=\"gShowPopUp('s%s',event)\" onmouseout=\"gHidePopUp('s%s')\"";
        $popup = "<div id=\"s%s\" class=\"respopup\" {$eh}>%s</div>\n";

        foreach ($popups as $id => $content) {
            printf($popup, $id, $id, $id, $content);
        }
    }

    // }}}
    // {{{ printPager()

    /**
     * �y�[�W�ړ��p�����N���o�͂���
     *
     * @return void
     */
    public function printPager($perPage, $totalItems)
    {
        if (false !== ($pager = &$this->makePager($perPage, $totalItems))) {
            echo '<table id="sbtoolbar2" class="toolbar" cellspacing="0"><tr><td style="text-align:center;">';
            echo $pager->links;
            echo '</td></tr></table>';
        }
    }

    // }}}
    // {{{ makePager()

    /**
     * �������ʓ��ł̃y�[�W�ړ��p��PEAR::Pager�̃C���X�^���X���쐬����
     *
     * @return object
     */
    public function makePager($perPage, $totalItems)
    {
        if ($totalItems == 0 || $totalItems <= $perPage) {
            $retval = FALSE;
            return $retval;
        }

        $pagerOptions = array(
            'mode'       => 'Sliding',
            'totalItems' => $totalItems,
            'perPage'    => $perPage,
            'delta'      => 5, // �q�b�g�����y�[�W�O��̃����N�����y�[�W��
            'urlVar'     => 'p', // �y�[�WID����肷��GET/POST�̕ϐ����A�f�t�H���g��"PageID"
            'spacesBeforeSeparator' => 1,
            'spacesAfterSeparator'  => 1,
        );

        $pager = Pager::factory($pagerOptions);

        return $pager;
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
