<?php
/**
 * rep2 - ���X�������݃t�H�[���̋@�\�ǂݍ���
 */

$js = array();

$fake_time = -10; // time ��10���O�ɋU��
$time = time() - 9*60*60;
$time = $time + $fake_time * 60;

$csrfid = P2Util::getCsrfId('post' . $host . $bbs . $key);

$hd['FROM'] = '';
$hd['mail'] = '';
$hd['MESSAGE'] = '';
$hd['subject'] = '';
$hd['beres_checked'] = '';
$hd['p2res_checked'] = '';

$htm['beres'] = '';
$htm['p2res'] = '';
$htm['sage_cb'] = '';
$htm['maru_post'] = '';
$htm['block_submit'] = '';
$htm['src_fix'] = '';
$htm['options'] = '';
$htm['options_k'] = '';
$htm['subject'] = '';
$htm['resform_ttitle'] = '';

$htm['disable_js'] = <<<EOP
<script type="text/javascript">
//<![CDATA[
// Thanks naoya <http://d.hatena.ne.jp/naoya/20050804/1123152230>

function isNetFront() {
  var ua = navigator.userAgent;
  if (ua.indexOf("NetFront") != -1 || ua.indexOf("AVEFront/") != -1 || ua.indexOf("AVE-Front/") != -1) {
    return true;
  } else {
    return false;
  }
}

function disableSubmit(form) {

  // 2006/02/15 NetFront�Ƃ͑����������ł܂�炵���̂Ŕ�����
  if (isNetFront()) {
    return;
  }

  var elements = form.elements;
  for (var i = 0; i < elements.length; i++) {
    if (elements[i].type == 'submit') {
      elements[i].disabled = true;
    }
  }
}

function setHiddenValue(button) {

  // 2006/02/15 NetFront�Ƃ͑����������ł܂�炵���̂Ŕ�����
  if (isNetFront()) {
    return;
  }

  if (button.name) {
    var q = document.createElement('input');
    q.type = 'hidden';
    q.name = button.name;
    q.value = button.value;
    button.form.appendChild(q);
  }
}

//]]>
</script>\n
EOP;

// {{{ key.idx���疼�O�ƃ��[����Ǎ���

if ($lines = FileCtl::file_read_lines($key_idx, FILE_IGNORE_NEW_LINES)) {
    $line = explode('<>', $lines[0]);
    $hd['FROM'] = htmlspecialchars($line[7], ENT_QUOTES);
    $hd['mail'] = htmlspecialchars($line[8], ENT_QUOTES);
}

// }}}
// {{{ �f�[�^�x�[�X����O���POST���s�f�[�^��beres/p2res�̐ݒ��Ǎ���
$post_backup_key = PostDataStore::getKeyForBackup($host, $bbs, $key, !empty($_REQUEST['newthread']));
$post_config_key = PostDataStore::getKeyForConfig($host, $bbs);

// �O���POST���s�f�[�^
if ($post_backup = PostDataStore::get($post_backup_key)) {
    $hd['FROM'] = htmlspecialchars($post_backup['FROM'], ENT_QUOTES, 'Shift_JIS');
    $hd['mail'] = htmlspecialchars($post_backup['mail'], ENT_QUOTES, 'Shift_JIS');
    $hd['MESSAGE'] = htmlspecialchars($post_backup['MESSAGE'], ENT_QUOTES, 'Shift_JIS');
    $hd['subject'] = htmlspecialchars($post_backup['subject'], ENT_QUOTES, 'Shift_JIS');
}

// beres/p2res
if ($post_config = PostDataStore::get($post_config_key)) {
    if ($post_config['beres']) {
        $hd['beres_checked'] = ' checked';
    }
    if ($post_config['p2res']) {
        $hd['p2res_checked'] = ' checked';
    }
}

// }}}
// {{{ ���O�ƃ��[���̍ŏI����

// �󔒂̓��[�U�ݒ�l�ɕϊ�
if ($hd['FROM'] === '') {
    $hd['FROM'] = htmlspecialchars($_conf['my_FROM'], ENT_QUOTES, 'Shift_JIS');
}
if ($hd['mail'] === '') {
    $hd['mail'] = htmlspecialchars($_conf['my_mail'], ENT_QUOTES, 'Shift_JIS');
}

// P2NULL�͋󔒂ɕϊ�
if ($hd['FROM'] === 'P2NULL') {
    $hd['FROM'] = '';
}
if ($hd['mail'] === 'P2NULL') {
    $hd['mail'] = '';
}

// }}}
// {{{ textarea�̑���

// �Q�l �N���V�b�N COLS='60' ROWS='8'
$mobile = Net_UserAgent_Mobile::singleton();
$wrap_at = ''; // wrap������W3C HTML 4.01�d�l�ɑ��݂��Ȃ�
// PC
if (!$_conf['ktai']) {
    $name_size_at = ' size="19"';
    $mail_size_at = ' size="19"';
    $msg_cols_at = ' cols="' . $STYLE['post_msg_cols'] . '"';
// willcom
} elseif($mobile->isAirHPhone()) {
    $msg_cols_at = ' cols="' . $STYLE['post_msg_cols'] . '"';
// �g��
} else {
    $STYLE['post_msg_rows'] = 5;
    $msg_cols_at = '';
    $wrap_at = ' wrap="soft"';
}

// {{{ PC�p sage �`�F�b�N�{�b�N�X

if (!$_conf['ktai']) {
    $on_check_sage = ' onchange="checkSage();"';
    $htm['sage_cb'] = <<<EOP
<input id="sage" type="checkbox" onclick="mailSage()"><label for="sage">sage</label>
EOP;
} else {
    $on_check_sage = '';
}

// }}}
// {{{ ��/Be/����p2 �������� �`�F�b�N�{�b�N�X

//  2ch����������
if (P2Util::isHost2chs($host) and file_exists($_conf['sid2ch_php'])) {
    $htm['maru_post'] = '<span title="2ch��ID�̎g�p"><input type="checkbox" id="maru" name="maru" value="1">'
                      . '<label for="maru">��</label></span>';
}

// Be
if (P2Util::isHost2chs($host) and $_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
    $htm['beres'] = '<input type="checkbox" id="beres" name="beres" value="1"'. $hd['beres_checked'] . '>'
                  . '<label for="beres">Be�ŏ�������</label>';
}

// ����p2
if ((P2Util::isHost2chs($host) || P2Util::isHostMachiBbs($host)) &&
    $_conf['p2_2ch_mail'] && $_conf['p2_2ch_pass'])
{
    $htm['p2res'] = '<input type="checkbox" id="p2res" name="p2res" value="1"'. $hd['p2res_checked'] . '>'
                  . '<label for="p2res">����p2�ŏ�������</label>';
}

// }}}
// {{{ �������݃u���b�N�p�`�F�b�N�{�b�N�X

if (!$_conf['ktai']) {
    $htm['block_submit'] = <<<EOP
<input type="checkbox" id="block_submit" onclick="switchBlockSubmit(this.checked)"><label for="block_submit">block</label>
EOP;
}

// }}}
// {{{ �\�[�X�R�[�h�␳�p�`�F�b�N�{�b�N�X

if (!$_conf['ktai']) {
    if ($_conf['editor_srcfix'] == 1 || ($_conf['editor_srcfix'] == 2 && preg_match('/pc\\d+\\.2ch\\.net/', $host))) {
        $htm['src_fix'] = <<<EOP
<input type="checkbox" id="fix_source" name="fix_source" value="1"><label for="fix_source">src</label>
EOP;
    }
}

// }}}
// {{{ �������݃v���r���[

$htm['dpreview_onoff'] = '';
$htm['dpreview_amona'] = '';
$htm['dpreview']  = '';
$htm['dpreview2'] = '';
if (!$_conf['ktai'] && $_conf['expack.editor.dpreview']) {
    $_dpreview_noname = 'null';
    if (P2Util::isHost2chs($host)) {
        $_dpreview_st = new SettingTxt($host, $bbs);
        $_dpreview_st->setSettingArray();
        if (!empty($_dpreview_st->setting_array['BBS_NONAME_NAME'])) {
            $_dpreview_noname = $_dpreview_st->setting_array['BBS_NONAME_NAME'];
            $_dpreview_noname = '"' . StrCtl::toJavaScript($_dpreview_noname) . '"';
        }
    }
    $_dpreview_hide = 'false';
    if ($_conf['expack.editor.dpreview'] == 2) {
        if (P2Util::isBrowserSafariGroup() && basename($_SERVER['SCRIPT_NAME']) != 'post_form.php') {
            $_dpreview_hide = 'true';
        }
        $_dpreview_pos = 'dpreview2';
    } else {
        $_dpreview_pos = 'dpreview';
    }
    $htm[$_dpreview_pos] = <<<EOP
<script type="text/javascript" src="js/strutil.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript" src="js/dpreview.js?{$_conf['p2_version_id']}"></script>
<script type="text/javascript">
//<![CDATA[
var dpreview_use = true;
var dpreview_on = false;
var dpreview_hide = {$_dpreview_hide};
var noname_name = {$_dpreview_noname};
//]]>
</script>
<fieldset id="dpreview" style="display:none;">
<legend>preview</legend>
<div>
    <span class="prvw_resnum">?</span>
    �F<span class="prvw_name"><b id="dp_name"></b><span id="dp_trip"></span></span>
    �F<span id="dp_mail" class="prvw_mail"></span>
    �F<span class="prvw_dateid"><span id="dp_date"></span> ID:<span id="dp_id">???</span></span>
</div>
<div id="dp_msg" class="prvw_msg"></div>
<!-- <div id="dp_empty" class="prvw_msg">(empty)</div> -->
</fieldset>
EOP;
    $htm['dpreview_onoff'] = <<<EOP
<input type="checkbox" id="dp_onoff" onclick="DPShowHide(this.checked)"><label for="dp_onoff">preview</label>
EOP;
    if ($_conf['expack.editor.dpreview_chkaa']) {
        $htm['dpreview_amona'] = <<<EOP
<input type="checkbox" id="dp_mona" disabled><label for="dp_mona">mona</label>
EOP;
    }
}

// }}}
// {{{ �����Ƀ��X

$htm['orig_msg'] = '';
if ((basename($_SERVER['SCRIPT_NAME']) == 'post_form.php' || !empty($_GET['inyou'])) && !empty($_GET['resnum'])) {
    $q_resnum = $_GET['resnum'];
    $hd['MESSAGE'] = "&gt;&gt;" . $q_resnum . "\r\n";
    if (!empty($_GET['inyou'])) {
        $aThread = new ThreadRead;
        $aThread->setThreadPathInfo($host, $bbs, $key);
        $aThread->readDat($aThread->keydat);
        $q_resar = $aThread->explodeDatLine($aThread->datlines[$q_resnum-1]);
        $q_resar = array_map('trim', $q_resar);
        $q_resar[3] = strip_tags($q_resar[3], '<br>');
        if ($_GET['inyou'] == 1 || $_GET['inyou'] == 3) {
            $hd['MESSAGE'] .= '&gt; ';
            $hd['MESSAGE'] .= preg_replace('/\\s*<br>\\s*/',"\r\n&gt; ", $q_resar[3]);
            $hd['MESSAGE'] .= "\r\n";
        }
        if ($_GET['inyou'] == 2 || $_GET['inyou'] == 3) {
            if (!$_conf['ktai'] || $_conf['iphone']) {
                $htm['orig_msg'] = <<<EOM
<fieldset id="original_msg">
<legend>Original Message:</legend>
    <div>
        <span class="prvw_resnum">{$q_resnum}</span>
        �F<b class="prvw_name">{$q_resar[0]}</b>
        �F<span class="prvw_mail">{$q_resar[1]}</span>
        �F<span class="prvw_dateid">{$q_resar[2]}</span>
    </div>
    <div id="orig_msg" class="prvw_msg">{$q_resar[3]}</div>
</fieldset>
EOM;
            } else {
                $htm['orig_msg'] = <<<EOM
<div><i>Original Message:</i>
[{$q_resnum}]: <b>{$q_resar[0]}</b>: {$q_resar[1]}: {$q_resar[2]}<br>
{$q_resar[3]}</div>
EOM;
            }
        }
    }
}

// }}}
// {{{ �{������̂Ƃ���sage�ĂȂ��Ƃ��ɑ��M���悤�Ƃ���ƒ��ӂ���

$onsubmit_at = '';

if (!$_conf['ktai']) {
    if (!preg_match('{NetFront|AVE-?Front/}', $_SERVER['HTTP_USER_AGENT'])) {
        $onsubmit_at = sprintf(' onsubmit="if (validateAll(%s,%s)) { switchBlockSubmit(true); return true; } else { return false }"',
            (($_conf['expack.editor.check_message']) ? 'true' : 'false'),
            (($_conf['expack.editor.check_sage'])    ? 'true' : 'false'));
    }
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
