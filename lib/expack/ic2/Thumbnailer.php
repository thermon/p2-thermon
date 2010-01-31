<?php
/**
 * rep2expack - ImageCache2
 */

// {{{ IC2_Thumbnailer

class IC2_Thumbnailer
{
    // {{{ constants

    const SIZE_SOURCE   = 0; // ic2.php �̂��߁A�֋X��ݒ肵�Ă��邪�A�D�܂����Ȃ�
    const SIZE_PC       = 1;
    const SIZE_MOBILE   = 2;
    const SIZE_INTERMD  = 3;

    const SIZE_DEFAULT  = 1;

    // }}}
    // {{{ properties

    public $db;            // @var object  PEAR DB_{phptype}�̃C���X�^���X
    public $ini;           // @var array   ImageCache2�̐ݒ�
    public $mode;          // @var int     �T���l�C���̎��
    public $cachedir;      // @var string  ImageCache2�̃L���b�V���ۑ��f�B���N�g��
    public $sourcedir;     // @var string  �\�[�X�ۑ��f�B���N�g��
    public $thumbdir;      // @var string  �T���l�C���ۑ��f�B���N�g��
    public $driver;        // @var string  �C���[�W�h���C�o�̎��
    public $epeg;          // @var bool    Epeg�����p�\���ۂ�
    public $magick;        // @var string  ImageMagick�̃p�X
    public $max_width;     // @var int     �T���l�C���̍ő啝
    public $max_height;    // @var int     �T���l�C���̍ő卂��
    public $type;          // @var string  �T���l�C���̉摜�`���iJPEG��PNG�j
    public $quality;       // @var int     �T���l�C���̕i��
    public $bgcolor;       // @var mixed   �T���l�C���̔w�i�F
    public $resize;        // @var bolean  �摜�����T�C�Y���邩�ۂ�
    public $rotate;        // @var int     �摜����]����p�x�i��]���Ȃ��Ƃ�0�j
    public $trim;          // @var bolean  �摜���g���~���O���邩�ۂ�
    public $coord;         // @var array   �摜���g���~���O����͈́i�g���~���O���Ȃ��Ƃ�false�j
    public $found;         // @var array   IC2_DataObject_Images�ŃN�G���𑗐M��������
    public $dynamic;       // @var bool    ���I�������邩�ۂ��itrue�̂Ƃ����ʂ��t�@�C���ɕۑ����Ȃ��j
    public $intermd;       // @var string  ���I�����ɗ��p���钆�ԃC���[�W�̃p�X�i�\�[�X���璼�ڐ�������Ƃ�false�j
    public $buf;           // @var string  ���I���������摜�f�[�^
    // @var array $default_options,    ���I�������̃I�v�V����
    public $default_options = array(
        'quality' => null,
        'width'   => null,
        'height'  => null,
        'rotate'  => 0,
        'trim'    => false,
        'intermd' => false,
    );
    // @var array $mimemap, MIME�^�C�v�Ɗg���q�̑Ή��\
    public $mimemap = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @param int $mode
     * @param array $dynamic_options
     */
    public function __construct($mode = self::SIZE_DEFAULT, array $dynamic_options = null)
    {
        if ($dynamic_options) {
            $options = array_merge($this->default_options, $dynamic_options);
            $this->dynamic = true;
            $this->intermd = $options['intermd'];
        } else {
            $options = $this->default_options;
            $this->dynamic = false;
            $this->intermd = false;
        }

        // �ݒ�
        $this->ini = ic2_loadconfig();

        // �f�[�^�x�[�X�ɐڑ�
        $icdb = new IC2_DataObject_Images;
        $this->db = $icdb->getDatabaseConnection();
        if (DB::isError($this->db)) {
            $this->error($this->db->getMessage());
        }

        // �T���l�C�����[�h����
        switch ($mode) {
            case self::SIZE_SOURCE:
            case self::SIZE_PC:
                $this->mode = self::SIZE_PC;
                $setting = $this->ini['Thumb1'];
                break;
            case self::SIZE_MOBILE:
                $this->mode = self::SIZE_MOBILE;
                $setting = $this->ini['Thumb2'];
                break;
            case self::SIZE_INTERMD:
                $this->mode = self::SIZE_INTERMD;
                $setting = $this->ini['Thumb3'];
                break;
            default:
                $this->error('�����ȃT���l�C�����[�h�ł��B');
        }

        // �C���[�W�h���C�o����
        $driver = strtolower($this->ini['General']['driver']);
        $this->driver = $driver;
        switch ($driver) {
            case 'imagemagick6': // ImageMagick6 �� convert �R�}���h
                $this->driver = 'imagemagick';
            case 'imagemagick': // ImageMagick �� convert �R�}���h
                $searchpath = $this->ini['General']['magick'];
                if (!ic2_findexec('convert', $searchpath)) {
                    $this->error('ImageMagick���g���܂���B');
                }
                if ($searchpath) {
                    $this->magick = $searchpath . DIRECTORY_SEPARATOR . 'convert';
                } else {
                    $this->magick = 'convert';
                }
                break;
            case 'gd': // PHP �� GD �g���@�\
            case 'imagick': // PHP �� ImageMagick �g���@�\
            case 'imlib2': // PHP �� Imlib2 �g���@�\
                if (!extension_loaded($driver)) {
                    $this->error($driver . '�G�N�X�e���V�������g���܂���B');
                }
                break;
            default:
                $this->error('�����ȃC���[�W�h���C�o�ł��B');
        }

        // �f�B���N�g���ݒ�
        $this->cachedir   = $this->ini['General']['cachedir'];
        $this->sourcedir  = $this->cachedir . '/' . $this->ini['Source']['name'];
        $this->thumbdir   = $this->cachedir . '/' . $setting['name'];

        // �T���l�C���̉摜�`���E���E�����E��]�p�x�E�i���ݒ�
        $rotate = (int) $options['rotate'];
        if (abs($rotate) < 4) {
            $rotate = $rotate * 90;
        }
        $rotate = ($rotate < 0) ? ($rotate % 360) + 360 : $rotate % 360;
        $this->rotate = ($rotate % 90 == 0) ? $rotate : 0;
        if ($options['width'] >= 1 && $options['height'] >= 1) {
            $setting['width']  = $options['width'];
            $setting['height'] = $options['height'];
        }
        if ($this->rotate % 180 == 90) {
            $this->max_width  = (int) $setting['height'];
            $this->max_height = (int) $setting['width'];
        } else {
            $this->max_width  = (int) $setting['width'];
            $this->max_height = (int) $setting['height'];
        }
        if (is_null($options['quality'])) {
            $this->quality = (int) $setting['quality'];
        } else {
            $this->quality = (int) $options['quality'];
        }
        if (0 < $this->quality && $this->quality <= 100) {
            $this->type = '.jpg';
        } else {
            $this->type = '.png';
            $this->quality = 0;
        }
        $this->trim = (bool) $options['trim'];

        // Epeg�g�p����
        if ($this->ini['General']['epeg'] && extension_loaded('epeg') &&
            !$this->dynamic && $this->type == '.jpg' &&
            $this->quality <= $this->ini['General']['epeg_quality_limit'])
        {
            $this->epeg = true;
        } else {
            $this->epeg = false;
        }

        // �T���l�C���̔w�i�F�ݒ�
        if (preg_match('/^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i', // RGB�e�F2����16�i��
                       $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1]);
            $g = hexdec($c[2]);
            $b = hexdec($c[3]);
        } elseif (preg_match('/^#?([0-9A-F])([0-9A-F])([0-9A-F])$/i', // RGB�e�F1����16�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1] . $c[1]);
            $g = hexdec($c[2] . $c[2]);
            $b = hexdec($c[3] . $c[3]);
        } elseif (preg_match('/^(\d{1,3}),(\d{1,3}),(\d{1,3})$/', // RGB�e�F1�`3����10�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = max(0, min(intval($c[1]), 255));
            $g = max(0, min(intval($c[2]), 255));
            $b = max(0, min(intval($c[3]), 255));
        } else {
            $r = null;
            $g = null;
            $b = null;
        }
        $this->bgcolor = array($r, $g, $b);
    }

    // }}}
    // {{{ convert()

    /**
     * �T���l�C�����쐬
     *
     * @return  string|bool|PEAR_Error
     *          �T���l�C���𐶐��E�ۑ��ɐ��������Ƃ��A�T���l�C���̃p�X
     *          �e���|�����E�T���l�C���̐����ɐ��������Ƃ��Atrue
     *          ���s�����Ƃ� PEAR_Error
     */
    public function convert($size, $md5, $mime, $width, $height, $force = false)
    {
        // �摜
        if (!empty($this->intermd) && file_exists($this->intermd)) {
            $src    = realpath($this->intermd);
            $csize  = getimagesize($this->intermd);
            $width  = $csize[0];
            $height = $csize[1];
        } else {
            $src = $this->srcPath($size, $md5, $mime, true);
        }
        $thumbURL = $this->thumbPath($size, $md5, $mime);
        $thumb = $this->thumbPath($size, $md5, $mime, true);
        if ($src == false) {
            $error = PEAR::raiseError("������MIME�^�C�v�B({$mime})");
            return $error;
        } elseif (!file_exists($src)) {
            $error = PEAR::raiseError("�\�[�X�摜���L���b�V������Ă��܂���B({$src})");
            return $error;
        }
        if (!$force && !$this->dynamic && file_exists($thumb)) {
            return $thumbURL;
        }
        $thumbdir = dirname($thumb);
        if (!is_dir($thumbdir) && !@mkdir($thumbdir)) {
            $error = PEAR::raiseError("�f�B���N�g�����쐬�ł��܂���ł����B({$thumbdir})");
            return $error;
        }

        // �T�C�Y������l�ȉ��ŉ�]�Ȃ��A�摜�`���������Ȃ�΂��̂܂܃R�s�[
        // --- �g�тŕ\���ł��Ȃ����Ƃ�����̂ŕ���A�����ƃT���l�C��������
        /*if ($this->resize == false && $this->rotate == 0 && $this->type == $this->mimemap[$mime]) {
            if (@copy($src, $thumb)) {
                return $thumbURL;
            } else {
                $error = PEAR::raiseError("�摜���R�s�[�ł��܂���ł����B({$src} -&gt; {$thumb})");
                return $error;
            }
        }*/

        // Epeg�ŃT���l�C�����쐬
        if ($mime == 'image/jpeg' && $this->epeg) {
            $dst = ($this->dynamic) ? '' : $thumb;
            $result = epeg_thumbnail_create($src, $dst, $this->max_width, $this->max_height, $this->quality);
            if ($result == false) {
                $error = PEAR::raiseError("�T���l�C�����쐬�ł��܂���ł����B({$src} -&gt; {$dst})");
                return $error;
            }
            if ($this->dynamic) {
                $this->buf = $result;
            }
            return $thumbURL;
        }

        // �o�̓T�C�Y���v�Z
        $size = array('w' => $width, 'h' => $height);
        list($size['tw'], $size['th']) = $this->calc($width, $height, true);
        if (is_array($this->coord)) {
            $size['sx'] = $this->coord['x'][0];
            $size['sy'] = $this->coord['y'][0];
            $size['sw'] = $this->coord['x'][1];
            $size['sh'] = $this->coord['y'][1];
        } else {
            $size['sx'] = 0;
            $size['sy'] = 0;
            $size['sw'] = $width;
            $size['sh'] = $height;
        }

        // �C���[�W�h���C�o�ɃT���l�C���쐬������������
        $convertorClass = 'Thumbnailer_' . ucfirst(strtolower($this->driver));

        $convertor = new $convertorClass();
        $convertor->setBgColor($this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
        $convertor->setHttp(true);
        if ($this->type == '.png') {
            $convertor->setPng(true);
        } else {
            $convertor->setQuality($this->quality);
        }
        $convertor->setResampling($this->resize);
        $convertor->setRotation($this->rotate);
        $convertor->setTrimming($this->trim);
        if ($this->driver == 'imagemagick') {
            $convertor->setImageMagickConvertPath($this->magick);
        }

        if ($this->dynamic) {
            $result = $convertor->capture($src, $size);
            if (is_string($result)) {
                $this->buf = $result;
            }
        } else {
            $result = $convertor->save($src, $thumb, $size);
        }

        if (PEAR::isError($result)) {
            return $result;
        }
        return $thumbURL;
    }

    // }}}
    // {{{ utility methods
    // {{{ calc()

    /**
     * �T���l�C���T�C�Y�v�Z
     */
    public function calc($width, $height, $return_array = false)
    {
        // �f�t�H���g�l�E�t���O��ݒ�
        $t_width  = $width;
        $t_height = $height;
        $this->resize = false;
        $this->coord  = false;

        // �\�[�X���T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�\�[�X�̑傫�������̂܂ܕԂ�
        if ($width <= $this->max_width && $height <= $this->max_height) {
            // ���T�C�Y�E�g���~���O�Ƃ��ɖ���
            if ($return_array) {
                return array((int)$t_width, (int)$t_height);
            } else {
                return sprintf('%dx%d', $t_width, $t_height);
            }
        }

        // �c���ǂ���ɍ��킹�邩�𔻒�i�ő�T�C�Y��艡�� = �����ɍ��킹��j
        if (($width / $height) >= ($this->max_width / $this->max_height)) {
            // ���ɍ��킹��
            $main = $width;
            $sub  = $height;
            $max_main = $this->max_width;
            $max_sub  = $this->max_height;
            $t_main = &$t_width;  // $t_main��$t_sub���T���l�C���T�C�Y��
            $t_sub  = &$t_height; // ���t�@�����X�ɂ��Ă���̂���
            $c_main = 'x';
            $c_sub  = 'y';
        } else {
            // �c�ɍ��킹��
            $main = $height;
            $sub  = $width;
            $max_main = $this->max_height;
            $max_sub  = $this->max_width;
            $t_main = &$t_height;
            $t_sub  = &$t_width;
            $c_main = 'y';
            $c_sub  = 'x';
        }

        // �T���l�C���T�C�Y�ƕϊ��t���O������
        $t_main = $max_main;
        if ($this->trim) {
            // �g���~���O����
            $this->coord = array($c_main => array(0, $main), $c_sub => array(0, $sub));
            $ratio = $t_sub / $max_sub;
            if ($ratio <= 1) {
                // �\�[�X���T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�k�������Ƀg���~���O
                // $t_main == $max_main, $t_sub == $sub
                // ceil($sub * ($t_main / $t_sub)) = ceil($sub * $t_main / $sub) = $t_main = $max_main
                $c_length = $max_main;
            } elseif ($ratio < 1.05) {
                // �k�������ɂ߂ď������Ƃ��A�掿�򉻂�����邽�߂ɏk�������Ƀg���~���O
                $this->coord[$c_sub][0] = floor(($t_sub - $max_sub) / 2);
                $t_sub = $max_sub;
                $c_length = $max_main;
            } else {
                // �T���l�C���T�C�Y�����ς��Ɏ��܂�悤�ɏk�����g���~���O
                $this->resize = true;
                $t_sub = $max_sub;
                $c_length = ceil($sub * ($t_main / $t_sub));
            }
            $this->coord[$c_main] = array(floor(($main - $c_length) / 2), $c_length);
        } else {
            // �A�X�y�N�g����ێ������܂܏k�����A�g���~���O�͂��Ȃ�
            $this->resize = true;
            $t_sub = round($max_main * ($sub / $main));
        }

        // �T���l�C���T�C�Y��Ԃ�
        if ($return_array) {
            return array((int)$t_width, (int)$t_height);
        } else {
            return sprintf('%dx%d', $t_width, $t_height);
        }
    }

    // }}}
    // {{{ srcPath()

    /**
     * �\�[�X�摜�̃p�X���擾
     */
    public function srcPath($size, $md5, $mime, $FSFullPath = false)
    {
        $directory = $this->getSubDir($this->sourcedir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return false;
        }

        $basename = $size . '_' . $md5 . $this->mimemap[$mime];

        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    // }}}
    // {{{ thumbPath()

    /**
     * �T���l�C���̃p�X���擾
     */
    public function thumbPath($size, $md5, $mime, $FSFullPath = false)
    {
        $directory = $this->getSubDir($this->thumbdir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return false;
        }

        $basename = $size . '_' . $md5;
        if ($this->rotate) {
            $basename .= '_' . str_pad($this->rotate, 3, 0, STR_PAD_LEFT);
        }
        if ($this->trim) {
            $basename .= '_tr';
        }
        $basename .= $this->type;

        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    // }}}
    // {{{ getSubDir()

    /**
     * �摜���ۑ������T�u�f�B���N�g���̃p�X���擾
     */
    public function getSubDir($basedir, $size, $md5, $mime, $FSFullPath = false)
    {
        if (!is_dir($basedir)) {
            return false;
        }

        $dirID = $this->dirID($size, $md5, $mime);

        if ($FSFullPath) {
            $directory = realpath($basedir) . DIRECTORY_SEPARATOR . $dirID;
        } else {
            $directory = $basedir . '/' . $dirID;
        }

        return $directory;
    }

    // }}}
    // {{{ dirID()

    /**
     * �摜1000�����ƂɃC���N�������g����f�B���N�g��ID���擾
     */
    public function dirID($size = null, $md5 = null, $mime = null)
    {
        if ($size && $md5 && $mime) {
            $icdb = new IC2_DataObject_Images;
            $icdb->whereAddQUoted('size', '=', $size);
            $icdb->whereAddQuoted('md5',  '=', $md5);
            $icdb->whereAddQUoted('mime', '=', $mime);
            $icdb->orderByArray(array('id' => 'ASC'));
            if ($icdb->find(true)) {
                $this->found = $icdb->toArray();
                return str_pad(ceil($icdb->id / 1000), 5, 0, STR_PAD_LEFT);
            }
        }
        $sql = 'SELECT MAX(' . $this->db->quoteIdentifier('id') . ') + 1 FROM '
             . $this->db->quoteIdentifier($this->ini['General']['table']) . ';';
        $nextid = $this->db->getOne($sql);
        if (DB::isError($nextid) || !$nextid) {
            $nextid = 1;
        }
        return str_pad(ceil($nextid / 1000), 5, 0, STR_PAD_LEFT);
    }

    // }}}
    // }}}
    // {{{ error()

    /**
     * �G���[���b�Z�[�W��\�����ďI��
     */
    public function error($message = '')
    {
        echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$message}</p>
</body>
</html>
EOF;
        exit;
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
