

    rep2

    2�����˂�A�܂�BBS�AJBBS@�������BBS �̉{���X�N���v�g
    �ڍ�URL http://akid.s17.xrea.com/


��������F�T�[�o�T�C�h

 PHP4.3.8�ȍ~�BPHP5�ł������܂��B
 OS�́AUNIX�ALinux�AWindows�AMac OS X�ł̓���񍐂���B
 
 ��PHP��PEAR�𗘗p���Ă��܂��B
 ��PHP�́Ambstring ���L���ł���K�v������܂��B
 ��2�����˂�́u�����O�C���v�ɂ�SSL�ʐM�𗘗p����̂ŁAPHP��curl�g�����L�����A�V�X�e����curl�Ƀp�X���ʂ��Ă��Ȃ���dat���������ߋ����O���ǂ߂܂���B cURL��OpenSSL���L���ŃR���p�C������Ă���K�v������_�ɒ��ӂ��Ă��������B
 
��������F�N���C�A���g�T�C�h

 �e��u���E�U�ŉ{���B�g�pOS�A�u���E�U�͓��ɖ��Ȃ��݌v�B�g�щB
 CSS�AJavaScript��ON�ɂ��邱�Ƃ����������B

����������

  1. �T�[�o�𗧂��グ�āAPHP�������悤�ɂ���BPEAR���Y�ꂸ�Ɂi���L�Q�Ɓj
  2. rep2�f�B���N�g�����T�[�o����A�N�Z�X�ł��鏊�i�u~/Sites�v�Ƃ��j�֒u���B
  3. rep2�f�B���N�g���̒��Ƀf�[�^�ۑ��p�̃f�B���N�g�����쐬����B�i�f�t�H���g�ł� "data" �f�B���N�g���j
  4. �f�[�^�ۑ��p�f�B���N�g���̃p�[�~�b�V�������u707�v�i�܂���777�j�ɂ���B
  5. �K�v�ɉ����āA conf/conf_admin.inc.php �Ȃǂ�conf�t�@�C�����e�L�X�g�G�f�B�^�ŊJ���Đݒ�ҏW�B
  6. �u���E�U����A
    http://127.0.0.1/~(���[�U��)/rep2/index.php
   �Ăȋ��rep2�f�B���N�g���փA�N�Z�X�B

 ��PHP���m���ɓ����Ă��邩�ǂ������m���߂������́H
 http://127.0.0.1/~(���[�U��)/rep2/phpinfo.php
 �ĂȂƂ��ɃA�N�Z�X���Ă݂ĉ������B
 ���炸��[����PHP�̊���񂪕\�����ꂽ�Ȃ�΁APHP�͐���ɓ��삵�Ă��܂��B
 �i�m�F���ł��܂�����Aphpinfo.php �͂����K�v�Ȃ��̂ō폜���Ă��\���܂���j

 ��Mac OS X��PHP�������Ȃ��l�i�W�����̂܂܂ł͓����Ȃ��j�́A
 http://homepage1.nifty.com/glass/tom_neko/web/web_cgi_osx.html#php
 ���Q�l��httpd.conf��ҏW���ĉ������B
 ���̌�́A�u�V�X�e�����ݒ�v���u���L�v���u�p�[�\�i��Web���L�v���u�J�n�v�ŉғ����܂��B

 ��Mac OS X�ł́udata�v�f�B���N�g���̃p�[�~�b�V�����̊ȒP�ȕύX���@�F
 Finder�Łudata�v�t�H���_��I����A�u��������v���u���L���ƃA�N�Z�X���v��I�ԁB
 �I�[�i�[�A���̑��̃A�N�Z�X���u�ǂ݁^�����v�\�ɐݒ�B

��PEAR�̃C���X�g�[��

 rep2�� PEAR �� Net_UserAgent_Mobile, PHP_Compat �𗘗p���Ă��܂��B
 PEAR ���A�T�[�o�ɃC���X�g�[������Ă��Ȃ��ꍇ�́A
 pear�R�}���h���g���āA�����ŃT�[�o�ɃC���X�g�[�����邩�A
 rep2�̃f�B���N�g���� includes �f�B���N�g�����쐬���A
 ���̒��Ƀl�b�g����_�E�����[�h���Ă����t�@�C�������Ă���Ă��������B

 pear install �ŃT�[�o�ɃC���X�g�[������ꍇ�ANet_UserAgent_Mobile �͌���beta�Ȃ̂ŁA
 pear install Net_UserAgent_Mobile
 �ŃC���X�g�[���ł��Ȃ����́A
 pear install Net_UserAgent_Mobile-beta
 �ƃR�}���h��łƂ悢�����B

 includes�f�B���N�g���ŗ��p����ꍇ�́A�g���p�b�N����� p2pear �����̂܂܎g���܂��B
 http://moonshine.s32.xrea.com/

���ݒ�ɂ���

 �f�[�^�ۑ��f�B���N�g���ƃZ�L�����e�B�@�\�̐ݒ�́Aconf/conf_admin.inc.php ���e�L�X�g�G�f�B�^�ŕҏW�B
 �i�f�t�H���g�ł́A�w�肳�ꂽ�z�X�g�ȊO�̓A�N�Z�X�ł��Ȃ��Ȃ��Ă��܂��j
 �z�X�g�`�F�b�N�̏ڍאݒ�́Aconf/conf_hostcheck.php ���e�L�X�g�G�f�B�^�ŕҏW�B
 �f�U�C���ݒ�́Aconf/conf_style.inc.php ���e�L�X�g�G�f�B�^�ŕҏW�B
 ���̑��̃��[�U�̐ݒ�́A���O�C����́u�ݒ�Ǘ��v���u���[�U�ݒ�ҏW�v�ŁB

�����O�C�����[�U�ɂ���

 �ŏ��̃��O�C�����̂݁A�V�K���[�U�o�^�ƂȂ�܂��B

 �p�X���[�h��Y�ꂽ�肵�āA�F�؃��[�U�����������������ꍇ�́A
 �f�[�^�ۑ��f�B���N�g���� p2_auth_user.php ���蓮�ō폜���Ă��������B

�����C�Z���X

 X11���C�Z���X�ł��B

���Ɛ�

 rep2�̂��g�p�͎��ȐӔC�ł�낵�����肢���܂��B


(c)aki <akid@s17.xrea.com>
