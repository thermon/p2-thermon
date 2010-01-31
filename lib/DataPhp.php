<?php
/**
 * 2006/02/24 aki DataPhp�`���͂����g��Ȃ��������B�g���q .cgi ���ւƂ���
 *
 * �f�[�^�t�@�C����Web���璼�ڃA�N�Z�X����Ă������݂��Ȃ��悤��php�`���̃t�@�C���Ńf�[�^����舵���N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����B�t�@�C���̕ۑ��`���́A�ȉ��̂悤�Ȋ����B
 *
 * ���Hphp �^*
 * �f�[�^
 * *�^ �H��
 */

// {{{ DataPhp

/**
 * @static
 */
class DataPhp
{
    // {{{ getPre()

    static public function getPre()
    {
        return "<?php /*\n";
    }

    // }}}
    // {{{ getHip()

    static public function getHip()
    {
        return "\n*/ ?>";
    }

    // }}}
    // {{{ getDataPhpCont()

    /**
     * ���f�[�^php�`���̃t�@�C����ǂݍ���
     *
     * ������̃A���G�X�P�[�v���s��
     */
    static public function getDataPhpCont($data_php)
    {
        if (!$cont = FileCtl::file_read_contents($data_php)) {
            // �ǂݍ��݃G���[�Ȃ�false�A����ۂȂ�""��Ԃ�
            return $cont;

        } else {
            $pre_quote = preg_quote(DataPhp::getPre());
            $hip_quote = preg_quote(DataPhp::getHip());
            // �擪���Ɩ������폜
            if (preg_match("{".$pre_quote."(.*?)".$hip_quote.".*}s", $cont, $m)) {
                $cont = $m[1];
            } else {
                return false;
            }

            // �A���G�X�P�[�v����
            $cont = DataPhp::unescapeDataPhp($cont);

            return $cont;
        }
    }

    // }}}
    // {{{ fileDataPhp()

    /**
     * ���f�[�^php�`���̃t�@�C�������C���œǂݍ���
     *
     * ������̃A���G�X�P�[�v���s��
     */
    static public function fileDataPhp($data_php)
    {
        if (!$cont = DataPhp::getDataPhpCont($data_php)) {
            // �ǂݍ��݃G���[�Ȃ�false�A����ۂȂ��z���Ԃ�
            if ($cont === false) {
                return false;
            } else {
                return array();
            }
        } else {
            // �s�f�[�^�ɕϊ�
            $lines = array();

            $lines = explode("\n", $cont);
            $count = count($lines);

            $i = 1;
            foreach ($lines as $l) {
                if ($i != $count) {
                    $newlines[] = $l."\n";
                // �ŏI�s�Ȃ�
                } else {
                    // ����ۂłȂ���Βǉ�
                    if ($l !== "") {
                        $newlines[] = $l;
                    }
                    break;
                }
                $i++;
            }

            /*
            if ($lines) {
                // �����̋�s�͓��ʂɍ폜����
                $count = count($lines);
                if (rtrim($lines[$count-1]) == "") {
                    array_pop($lines);
                }
            }
            */

            return $newlines;
        }
    }

    // }}}
    // {{{ writeDataPhp()

    /**
     * �f�[�^php�`���̃t�@�C���Ƀf�[�^���L�^����
     *
     * ������̃G�X�P�[�v���s��
     * @param srting $cont �L�^����f�[�^������B
     */
    static public function writeDataPhp($data_php, $cont, $perm = 0606)
    {
        // &<>/ �� &xxx; �ɃG�X�P�[�v����
        $new_cont = DataPhp::escapeDataPhp($cont);

        // �擪���Ɩ�����ǉ�
        $new_cont = DataPhp::getPre() . $new_cont . DataPhp::getHip();

        // �t�@�C�����Ȃ���ΐ���
        FileCtl::make_datafile($data_php, $perm);
        // ��������
        $fp = @fopen($data_php, 'wb') or p2die("{$data_php} ���X�V�ł��܂���ł���");
        flock($fp, LOCK_EX);
        $last = ignore_user_abort(1);
        ftruncate($fp, 0);
        fwrite($fp, $new_cont);
        ignore_user_abort($last);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    // }}}
    // {{{ putDataPhp()

    /**
     * �f�[�^php�`���̃t�@�C���ŁA�����Ƀf�[�^��ǉ�����
     */
    static public function putDataPhp($data_php, $cont, $perm = 0606, $ncheck = false)
    {
        if ($cont === "") {
            return true;
        }

        $pre_quote = preg_quote(DataPhp::getPre());
        $hip_quote = preg_quote(DataPhp::getHip());

        $cont_esc = DataPhp::escapeDataPhp($cont);

        $old_cont = FileCtl::file_read_contents($data_php);
        if ($old_cont) {
            // �t�@�C�����A�f�[�^php�`���ȊO�̏ꍇ�́A����������false��Ԃ�
            if (!preg_match("/^\s*<\?php\s\/\*/", $old_cont)) {
                trigger_error('putDataPhp() file is broken.', E_USER_WARNING);
                return false;
            }

            $old_cut = preg_replace('{'.$hip_quote.'.*$}s', '', $old_cont);

            // �w��ɉ����āA�Â����e�̖��������s�łȂ���΁A���s��ǉ�����
            if ($ncheck) {
                if (substr($old_cut, -1) != "\n") {
                    $old_cut .= "\n";
                }
            }

            $new_cont = $old_cut . $cont_esc .DataPhp::getHip();

        // �f�[�^���e���܂��Ȃ���΁A�V�K�f�[�^php
        } else {
            $new_cont = DataPhp::getPre().$cont_esc.DataPhp::getHip();
        }

        // �t�@�C�����Ȃ���ΐ���
        FileCtl::make_datafile($data_php, $perm);
        // ��������
        $fp = @fopen($data_php, 'wb') or p2die("{$data_php} ���X�V�ł��܂���ł���");
        flock($fp, LOCK_EX);
        $last = ignore_user_abort(1);
        ftruncate($fp, 0);
        fwrite($fp, $new_cont);
        ignore_user_abort($last);
        flock($fp, LOCK_UN);
        fclose($fp);

        return true;
    }

    // }}}
    // {{{ escapeDataPhp()

    /**
     * ���f�[�^php�`���̃f�[�^���G�X�P�[�v����
     */
    static public function escapeDataPhp($str)
    {
        // &<>/ �� &xxx; �̃G�X�P�[�v������
        $str = str_replace("&", "&amp;", $str);
        $str = str_replace("<", "&lt;", $str);
        $str = str_replace(">", "&gt;", $str);
        $str = str_replace("/", "&frasl;", $str);
        return $str;
    }

    // }}}
    // {{{ unescapeDataPhp()

    /**
     * ���f�[�^php�`���̃f�[�^���A���G�X�P�[�v����
     */
    static public function unescapeDataPhp($str)
    {
        // &<>/ �� &xxx; �̃G�X�P�[�v�����ɖ߂�
        $str = str_replace('&lt;', '<', $str);
        $str = str_replace('&gt;', '>', $str);
        $str = str_replace('&frasl;', '/', $str);
        $str = str_replace('&amp;', '&', $str);
        return $str;
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
