<?php
/**
 * ImageCache2 - ���s�t�@�C�������֐�
 */

// {{{ findexec()

/**
 * $search_path������s�t�@�C��$command����������
 * ������΃p�X���G�X�P�[�v���ĕԂ��i$escape���U�Ȃ炻�̂܂ܕԂ��j
 * ������Ȃ����false��Ԃ�
 */
function findexec($command, $search_path = '', $escape = true)
{
    // Windows���A���̑���OS��
    if (P2_OS_WINDOWS) {
        if (strtolower(strrchr($command, '.')) != '.exe') {
            $command .= '.exe';
        }
        $check = function_exists('is_executable') ? 'is_executable' : 'file_exists';
    } else {
        $check = 'is_executable';
    }

    // $search_path����̂Ƃ��͊��ϐ�PATH���猟������
    if ($search_path == '') {
        $search_dirs = explode(PATH_SEPARATOR, getenv('PATH'));
    } else {
        $search_dirs = explode(PATH_SEPARATOR, $search_path);
    }

    // ����
    foreach ($search_dirs as $path) {
        $path = realpath($path);
        if ($path === false || !is_dir($path)) {
            continue;
        }
        if ($check($path . DIRECTORY_SEPARATOR . $command)) {
            return ($escape ? escapeshellarg($command) : $command);
        }
    }

    // ������Ȃ�����
    return false;
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
