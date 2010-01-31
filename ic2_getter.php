<?php
/**
 * ImageCache2 - �_�E�����[�_
 */

// {{{ p2��{�ݒ�ǂݍ���&�F��

define('P2_OUTPUT_XHTML', 1);

require_once './conf/conf.inc.php';

$_login->authorize();

if (!$_conf['expack.ic2.enabled']) {
    p2die('ImageCache2�͖����ł��B', 'conf/conf_admin_ex.inc.php �̐ݒ��ς��Ă��������B');
}

// }}}
// {{{ ������

// ���C�u�����ǂݍ���
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ObjectFlexy.php';
require_once 'HTML/Template/Flexy.php';
require_once P2EX_LIB_DIR . '/ic2/bootstrap.php';

// �|�b�v�A�b�v�E�C���h�E�H
if (empty($_GET['popup'])) {
    $isPopUp = 0;
    $autoClose = -1;
} else {
    $isPopUp = 1;
    if (array_key_exists('close', $_GET) && is_numeric($_GET['close'])) {
        $autoClose = (float)$_GET['close'] * 1000.0;
        if ($autoClose > 0.0) {
            $autoClose = (int)$autoClose;
            if ($autoClose == 0) {
                $autoClose = 1;
            }
        } else {
            $autoClose = -1;
        }
    } else {
        $autoClose = -1;
    }
}

// }}}
// {{{ config


// �ݒ�t�@�C���ǂݍ���
$ini = ic2_loadconfig();

// �t�H�[���̃f�t�H���g�l
$qf_defaults = array(
    'uri'   => 'http://',
    'ref'   => '',
    'memo'  => '',
    'from'  => 'from',
    'to'    => 'to',
    'padding' => '',
    'popup'   => $isPopUp,
    'close'   => $autoClose,
);

// �t�H�[���̌Œ�l
$qf_constants = array(
    '_hint'       => $_conf['detect_hint'],
    'download'    => '�_�E�����[�h',
    'reset'       => '���Z�b�g',
    'close'       => '����',
);

// �v���r���[�̑傫��
$_preview_size = array(
    IC2_Thumbnailer::SIZE_PC      => $ini['Thumb1']['width'] . '&times;' . $ini['Thumb1']['height'],
    IC2_Thumbnailer::SIZE_MOBILE  => $ini['Thumb2']['width'] . '&times;' . $ini['Thumb2']['height'],
    IC2_Thumbnailer::SIZE_INTERMD => $ini['Thumb3']['width'] . '&times;' . $ini['Thumb3']['height'],
);

// ����
$_attr_uri    = array('size' => 50, 'onchange' => 'checkSerial(this.value)');
$_attr_s_chk  = array('onclick' => 'setSerialAvailable(this.checked)', 'id' => 's_chk');
$_attr_s_from = array('size' => 4, 'id' => 's_from');
$_attr_s_to   = array('size' => 4, 'id' => 's_to');
$_attr_s_pad  = array('size' => 1, 'id' => 's_pad');
$_attr_ref    = array('size' => 50);
$_attr_memo   = array('size' => 50);
$_attr_submit = array();
$_attr_reset  = array();
$_attr_close  = array('onclick' => 'window.close()');


// }}}
// {{{ prepare (Form & Template)


// �摜�_�E�����[�h�p�t�H�[����ݒ�
$_attribures = array('accept-charset' => 'UTF-8,Shift_JIS');
$_target = $isPopUp ? '_self' : 'read';

$qf = new HTML_QuickForm('get', 'get', $_SERVER['SCRIPT_NAME'], $_target, $_attribures);
$qf->setDefaults($qf_defaults);
$qf->setConstants($qf_constants);

// �t�H�[���v�f�̒�`
$qfe = array();

// �B���v�f
$qfe['detect_hint'] = $qf->addElement('hidden', '_hint');
$qfe['popup'] = $qf->addElement('hidden', 'popup');
$qfe['close'] = $qf->addElement('hidden', 'close');

// URL�ƘA�Ԑݒ�
$qfe['uri']     = $qf->addElement('text', 'uri', 'URL', $_attr_uri);
$qfe['serial']  = $qf->addElement('checkbox', 'serial', '�A��', NULL, $_attr_s_chk);
$qfe['from']    = $qf->addElement('text', 'from', 'From', $_attr_s_from);
$qfe['to']      = $qf->addElement('text', 'to', 'To', $_attr_s_to);
$qfe['padding'] = $qf->addElement('text', 'padding', '0�ŋl�߂錅��', $_attr_s_pad);

// ���t�@���ƃ���
$qfe['ref']  = $qf->addElement('text', 'ref', '���t�@��', $_attr_ref);
$qfe['memo'] = $qf->addElement('text', 'memo', '�@�@����', $_attr_memo);

// �v���r���[�̑傫��
$preview_size = array();
foreach ($_preview_size as $value => $lavel) {
    $preview_size[$value] = HTML_QuickForm::createElement('radio', NULL, NULL, $lavel, $value);
}
$qf->addGroup($preview_size, 'preview_size', '�v���r���[', '&nbsp;');
if (!isset($_GET['preview_size'])) {
    $preview_size[1]->updateAttributes('checked="checked"');
}

// ����E���Z�b�g�E����
$qfe['download'] = $qf->addElement('submit', 'download');
$qfe['reset']    = $qf->addElement('reset', 'reset');
$qfe['close']    = $qf->addElement('button', 'close', NULL, $_attr_close);

// Flexy
$_flexy_options = array(
    'locale' => 'ja',
    'charset' => 'cp932',
    'compileDir' => $_conf['compile_dir'] . DIRECTORY_SEPARATOR . 'ic2',
    'templateDir' => P2EX_LIB_DIR . '/ic2/templates',
    'numberFormat' => '', // ",0,'.',','" �Ɠ���
);

$flexy = new HTML_Template_Flexy($_flexy_options);

$flexy->setData('php_self', $_SERVER['SCRIPT_NAME']);
$flexy->setData('skin', $skin_en);
$flexy->setData('isPopUp', $isPopUp);
$flexy->setData('pc', !$_conf['ktai']);
$flexy->setData('iphone', $_conf['iphone']);
$flexy->setData('doctype', $_conf['doctype']);
$flexy->setData('extra_headers',   $_conf['extra_headers_ht']);
$flexy->setData('extra_headers_x', $_conf['extra_headers_xht']);

// }}}
// {{{ validate

$execDL = FALSE;
if ($qf->validate() && ($params = $qf->getSubmitValues()) && isset($params['uri']) && isset($params['download'])) {
    $execDL = TRUE;
    $params = array_map('trim', $params);

    // URL������
    $purl = @parse_url($params['uri']);
    if (!$purl || !preg_match('/^(https?)$/', $purl['scheme']) || empty($purl['host']) || empty($purl['path'])) {
        P2Util::pushInfoHtml('<p>�G���[: �s����URL</p>');
        $execDL = FALSE;
        $isError = TRUE;
    }

    // �v���r���[�̑傫��
    if (isset($params['preview_size']) && in_array($params['preview_size'], array_keys($_preview_size))) {
        $thumb_type = (int)$params['preview_size'];
    } else {
        $thumb_type = 1;
    }

    // ���t�@���ƃ���
    $extra_params = '';
    if (isset($params['ref']) && strlen(trim($params['ref'])) > 0) {
        $extra_params .= '&ref=' . rawurlencode($params['ref']);
    }
    if (isset($params['memo']) && strlen(trim($params['memo'])) > 0) {
        $new_memo = IC2_DataObject_Images::staticUniform($params['memo'], 'CP932');
        $_memo_en = rawurlencode($new_memo);
        // �����_�����O����htmlspecialchars()�����̂ŁA�����ł�&��&amp;�ɂ��Ȃ�
        $extra_params .= '&memo=' . $_memo_en . '&' . $_conf['detect_hint_q_utf8'];
    } else {
        $new_memo = NULL;
    }


    // �A��
    $serial_pattern = '/\\[(\\d+)-(\\d+)\\]/';
    if (!empty($params['serial'])) {

        // �v���[�X�z���_�ƃ��[�U�w��p�����[�^
        if (strpos($params['uri'], '%s') !== false && !preg_match($serial_pattern, $params['uri'], $from_to)) {
            if (strpos(preg_replace('/%s/', ' ', $params['uri'], 1), '%s') !== false) {
                P2Util::pushInfoHtml('<p>�G���[: URL�Ɋ܂߂���v���[�X�z���_�͈�����ł��B</p>');
                $execDL = FALSE;
                $isError = TRUE;
            } elseif (preg_match('/\\D/', $params['from']) || strlen($params['from']) == 0 ||
                      preg_match('/\\D/', $params['to'])   || strlen($params['to'])   == 0 ||
                      preg_match('/\\D/', $params['padding'])
            ) {
                P2Util::pushInfoHtml('<p>�G���[: �A�ԃp�����[�^�Ɍ�肪����܂��B</p>');
                $execDL = FALSE;
                $isError = TRUE;
            } else {
                $serial = array();
                $serial['from'] = (int)$params['from'];
                $serial['to']   = (int)$params['to'];
                if (strlen($params['padding']) == 0) {
                    $serial['pad'] = strlen($serial['to']);
                } else {
                    $serial['pad']  = (int)$params['padding'];
                }
             }

        // [from-to] ��W�J
        } elseif (preg_match($serial_pattern, $params['uri'], $from_to) && strpos($params['uri'], '%s') === false) {
            $params['uri'] = preg_replace($serial_pattern, '%s', $params['uri'], 1);
            if (preg_match($serial_pattern, $params['uri'])) {
                P2Util::pushInfoHtml('<p>�G���[: URL�Ɋ܂߂���A�ԃp�^�[���͈�����ł��B</p>');
                $execDL = FALSE;
                $isError = TRUE;
            } else {
                $serial = array();
                $serial['from'] = (int)$from_to[1];
                $serial['to']   = (int)$from_to[2];
                if (strlen($from_to[1]) == strlen($from_to[2])) {
                    $serial['pad'] = strlen($from_to[2]);
                /*} elseif (strlen($from_to[1]) < strlen($from_to[2]) && strlen($from_to[1]) > 1 && substr($from_to[1]) == '0') {
                    $serial['pad'] = strlen($from_to[1]);*/
                } else {
                    $serial['pad'] = 0;
                }
            }

        // �ǂ�����������A����������
        } else {
            P2Util::pushInfoHtml('<p>�G���[: URL�ɘA�Ԃ̃v���[�X�z���_(<samp>%s</samp>)�܂��̓p�^�[��(<samp>[from-to]</samp>)���܂܂�Ă��Ȃ����A�������܂܂�Ă��܂��B</p>');
            $execDL = FALSE;
            $isError = TRUE;
        }

        // �͈͂�����
        if (isset($serial) && $serial['from'] >= $serial['to']) {
            P2Util::pushInfoHtml('<p>�G���[: �A�Ԃ̏I��̔ԍ��͎n�܂�̔ԍ����傫���Ȃ��Ƃ����܂���B</p>');
            $execDL = FALSE;
            $isError = TRUE;
            $serial = NULL;
        }

    // �A�ԂȂ�
    } else {
        if (strpos($params['uri'], '%s') !== false || preg_match($serial_pattern, $params['uri'], $from_to)) {
            P2Util::pushInfoHtml('<p>�G���[: �A�ԂɃ`�F�b�N�������Ă��܂��񂪁AURL�ɘA�ԃ_�E�����[�h�p�̕����񂪊܂܂�Ă��܂��B</p>');
            $execDL = FALSE;
            $isError = TRUE;
        }
        $qfe['from']->updateAttributes('disabled="disabled"');
        $qfe['to']->updateAttributes('disabled="disabled"');
        $qfe['padding']->updateAttributes('disabled="disabled"');
        $serial = NULL;
    }

} else {
    $qfe['from']->updateAttributes('disabled="disabled"');
    $qfe['to']->updateAttributes('disabled="disabled"');
    $qfe['padding']->updateAttributes('disabled="disabled"');
}


// }}}
// {{{ generate


if ($execDL) {

    if (is_null($serial)) {
        $URLs = array($params['uri']);
    } else {
        $URLs = array();
        for ($i = $serial['from']; $i <= $serial['to']; $i++) {
            // URL�G���R�[�h���ꂽ�������%���܂ނ̂� sprintf() �͎g��Ȃ��B
            // URL�G���R�[�h�̃t�H�[�}�b�g��%+16�i���Ȃ̂�"%s"��u�����Ă��e�����Ȃ��B
            $URLs[] = str_replace('%s', str_pad($i, $serial['pad'], '0', STR_PAD_LEFT), $params['uri']);
        }
    }

    $thumbnailer = new IC2_Thumbnailer($thumb_type);
    $images = array();

    foreach ($URLs as $url) {
        $icdb = new IC2_DataObject_Images;
        $img_title = htmlspecialchars($url, ENT_QUOTES);
        $url_en = rawurlencode($url);
        $src_url = 'ic2.php?r=1&uri=' . $url_en;
        $thumb_url = 'ic2.php?r=1&t=' . $thumb_type . '&uri=' . $url_en;
        $thumb_x = '';
        $thumb_y = '';
        $img_memo = $new_memo;

         // �摜���u���b�N���X�gor�G���[���O�ɂ���Ƃ�
        if (FALSE !== ($errcode = $icdb->ic2_isError($url))) {
            $img_title = "<s>{$img_title}</s>";
            $thumb_url = "./img/{$errcode}.png";

        // ���ɃL���b�V������Ă���Ƃ�
        } elseif ($icdb->get($url)) {
            $_src_url = $thumbnailer->srcPath($icdb->size, $icdb->md5, $icdb->mime);
            if (file_exists($_src_url)) {
                $src_url = $_src_url;
            }
            $_thumb_url = $thumbnailer->thumbPath($icdb->size, $icdb->md5, $icdb->mime);
            if (file_exists($_thumb_url)) {
                $thumb_url = $_thumb_url;
            }
            if (preg_match('/(\d+)x(\d+)/', $thumbnailer->calc($icdb->width, $icdb->height), $thumb_xy)) {
                $thumb_x = $thumb_xy[1];
                $thumb_y = $thumb_xy[2];
            }
            // �������L�^����Ă��Ȃ��Ƃ���DB���X�V
            if (isset($new_memo) && strpos($icdb->memo, $new_memo) === false){
                $update = clone $icdb;
                if (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                    $update->memo = $new_memo . ' ' . $icdb->memo;
                } else {
                    $update->memo = $new_memo;
                }
                $update->update();
                $img_memo = $update->memo;
            } elseif (!is_null($icdb->memo) && strlen($icdb->memo) > 0) {
                $img_memo = $icdb->memo;
            }

        // �L���b�V������Ă��Ȃ��Ƃ�
        } else {
            $src_url .= $extra_params;
            $thumb_url .= $extra_params;
        }

        $img = new stdClass;
        $img->title     = $img_title;
        $img->src_url   = $src_url;
        $img->thumb_url = $thumb_url;
        $img->thumb_x   = $thumb_x;
        $img->thumb_y   = $thumb_y;
        $img->memo      = mb_convert_encoding($img_memo, 'CP932', 'UTF-8');
        $images[] = $img;
    }

    $flexy->setData('images', $images);
    if ($isPopUp) {
        $flexy->setData('showForm', TRUE);
    }
} else {
    if (empty($isError) || $isPopUp) {
        $flexy->setData('showForm', TRUE);
    }
}

// }}}
// {{{ output


// �t�H�[�����e���v���[�g�p�I�u�W�F�N�g�ɕϊ�
$r = new HTML_QuickForm_Renderer_ObjectFlexy($flexy);
//$r->setLabelTemplate('_label.tpl.html');
//$r->setHtmlTemplate('_html.tpl.html');
$qf->accept($r);
$qfObj = $r->toObject();

// ���IJavaScript
$js = $qf->getValidationScript();
$js .= <<<EOS
<script type="text/javascript">
// <![CDATA[
function ic2g_onload()
{
\tsetWinTitle();\n
EOS;
if ($execDL && $autoClose > 0) {
    $js .= "\twindow.setTimeout('window.close();', $autoClose);\n";
}
$js .= <<<EOS
}
// ]]>
</script>
EOS;

// �ϐ���Assign
$flexy->setData('info_msg', P2Util::getInfoHtml());
$flexy->setData('STYLE', $STYLE);
$flexy->setData('js', $js);
$flexy->setData('get', $qfObj);

// �y�[�W��\��
P2Util::header_nocache();
$flexy->compile('ic2g.tpl.html');
$flexy->output();


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
