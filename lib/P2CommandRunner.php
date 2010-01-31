<?php
/**
 * rep2expack - command runner
 */

// {{{ CONSTANTS

if (!defined('P2_CLI_DIR')) {
    define('P2_CLI_DIR', realpath(dirname(__FILE__) . '/../cli'));
}

// }}}
// {{{ P2CommandRunner

/**
 * �R�}���h���C���c�[���𑖂点�郆�[�e�B���e�B�N���X
 *
 * @static
 */
class P2CommandRunner
{
    // {{{ fetchSubjectTxt()

    /**
     * subject.txt�̕���_�E�����[�h�����s����
     *
     * @param string $mode
     * @param array $_conf
     * @return bool
     */
    static public function fetchSubjectTxt($mode, array $_conf)
    {
        // �R�}���h����
        $args = array(escapeshellarg($_conf['expack.php_cli_path']));
        if ($_conf['expack.dl_pecl_http']) {
            $args[] = '-d';
            $args[] = 'extension=' . escapeshellarg('http.' . PHP_SHLIB_SUFFIX);
        }
        $args[] = escapeshellarg(P2_CLI_DIR . DIRECTORY_SEPARATOR . 'fetch-subject-txt.php');

        switch ($mode) {
        case 'fav':
        case 'recent':
        case 'res_hist':
        case 'merge_favita':
            $args[] = sprintf('--mode=%s', $mode);
            break;
        default:
            return false;
        }

        if ($_conf['expack.misc.multi_favs']) {
            switch ($mode) {
            case 'fav':
                $args[] = sprintf('--set=%d', $_conf['m_favlist_set']);
                break;
            case 'merge_favita':
                $args[] = sprintf('--set=%d', $_conf['m_favita_set']);
                break;
            }
        }

        // �W���G���[�o�͂�W���o�͂Ƀ��_�C���N�g
        $args[] = '2>&1';

        $command = implode(' ', $args);

        //P2Util::pushInfoHtml('<p>' . htmlspecialchars($command, ENT_QUOTES) . '</p>');

        // ���s
        $pipe = popen($command, 'r');
        if (!is_resource($pipe)) {
            p2die('�R�}���h�����s�ł��܂���ł����B', $command);
        }

        $output = '';
        while (!feof($pipe)) {
            $output .= fgets($pipe);
        }

        $status = pclose($pipe);
        if ($status != 0) {
            P2Util::pushInfoHtml(sprintf('<p>%s(): ERROR(%d)</p>', __METHOD__, $status));
        }

        if ($output !== '') {
            if ($status == 2) {
                P2Util::pushInfoHtml($output);
            } else {
                P2Util::pushInfoHtml('<p>' . nl2br(htmlspecialchars($output, ENT_QUOTES)) . '</p>');
            }
        }

        return ($status == 0);
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
