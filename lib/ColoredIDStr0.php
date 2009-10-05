<?php

    // {{{ coloredIdStyle()

    /**
     * ID�����񂩂�J���[�X�^�C�����Z�o���ĕԂ�
     *
     * @param   string  $id     xxxxxxxxxx
     * @param   string  $count  ID�o����
     * @return  array(style1, style2 [, debug])
     */
    function coloredIdStyle($id, $count)
    {
        global $_conf, $STYLE;

        // Version.20081215
        //   ID�{�̂Ƃ͕ʂɁAID:�̕����ɕʂ̔w�i�F��K�p
        //   �F�ϊ�������colorchange.php�ɂ܂Ƃ߂��B
        //   ���̑��A���܂��܂Ƃ����C��
        // Version.20081216
        //   HSV,HLS�ɉ����AL*C*h�\�F�n�ɂ��Ή��i���C�u�������C���j
        // Version.20081224 �ݒ�ŕϊ��O�ƕϊ���̃J���[�R�[�h��\���ł���悤�ɂ����i�f�o�b�O�p�H�j
        // Version.20081228 �F�p�����[�^�ݒ��F��ԕʂɌʉ�
        require_once P2_LIB_DIR . '/colorchange.php';

        // ID����F�̌��𒊏o
        $coldiv=360; // �F���̕�����
        $arr1 = unpack('N', pack('H', 0) .
            base64_decode(str_replace('.', '+', substr($id, 0, 4))));
        $arr2 = unpack('N', pack('H', 0) .
            base64_decode(str_replace('.', '+', substr($id, 4, 4))));
        $color=$arr1[1] % $coldiv;
        $color2=$arr2[1] % $coldiv;


        // HSV�i�܂���HLS�j�p�����[�^�ݒ�
        // ���X����������قǁA�F���Z���A�Â��Ȃ�

        // �F��H�F�l��0�`360�i�p�x�j
        $h= $color*360/$coldiv;
        $h2=$color2*360/$coldiv;

        $colorMode=2;       // 0:HSV,1:HSL,2:L*C*h
        switch ($colorMode) {
        case 0:     // HSV�F���
            // �ʓxS(HSV)�F�l��0�i�W���j�`1�i�Z��)
            $S=$count*0.05;
            if ($S>1) {$S=1;}

            // ���xV(HSV)�F�l��0�i�Â��j�`1�i���邢�j
            $V=1   -$count*0.025;
            if ($L<0.1) {$L=0.1;}

            $color_param=array(
                array($h,$S,$V,$colorMode), // �w�i�F�iID�{�́j
                array($h2,1,0.6,$colorMode)  // �w�i�F�iID:�����j
            );
            break;
        case 1:  // HLS�F���
            // �P�xL(HLS)�F�l��0�i���j�`0.5�i���F�j�`1�i���j
            $L=0.95   -$count*0.025;
            if ($L<0.1) {$L=0.1;}

            // �ʓxS(HLS)�F�l��0�i�D�F�j�`1�i���F�j
            $S=$count*0.05;
            if ($S>1) {$S=1;}

            $color_param=array(
                array($h,$L,$S,$colorMode), // �w�i�F�iID�{�́j
                array($h2,0.6,0.5,$colorMode)  // �w�i�F�iID:�����j
            );
            break;
        case 2:  // L*C*h�F���
            // ���xL*(L*C*h)�F�l��0�i���j�`50�i���F�j�`100�i���j
            $L=100   -$count*2.5;
            if ($L<10) {$L=10;}

            // �ʓxC*(L*C*h)�F�l��0�i�D�F�j�`100�i���F�j
            $C=floor(40*sin(deg2rad($count*180/50)) + 8);
            if ($C<0) {$C=0;}
            $C += (30 - $L > 0) ? 30 - $L : 0;

            $color_param=array(
                array($L,$C,$h,$colorMode), // �w�i�F�iID�{�́j
                array(50,60,$h2,$colorMode)  // �w�i�F�iID:�����j
            );
            break;
        }

        // �F��ԂɊւ���Q�l����
        // HSV,HLS�F��� http://tt.sakura.ne.jp/~hiropon/lecture/color.html
        // L*C*h�\�F�n http://konicaminolta.jp/instruments/colorknowledge/part1/08.html
        // L*a*b*�\�F�n http://konicaminolta.jp/instruments/colorknowledge/part1/07.html
        // RGB�ɕϊ�
        $rgb=array();
        for($key=0;$key<count($color_param);$key++) {
            $colorMode=$color_param[$key][3];
            if ($colorMode==2) {
                array_push($rgb,LCh2RGB($color_param[$key]));
            } else {
                array_push($rgb,$colorMode 
                    ? HLS2RGB($color_param[$key])
                    : HSV2RGB($color_param[$key])
                );
                //  unset($color_param[$key]);
            }
        }

        // CSS�ŐF������
        $idstr2=preg_split('/:/',$idstr,2); // �R������ID������𕪊�
        $idstr2[0].=':';
        $uline=$STYLE['a_underline_none']==1 ? '' : "text-decoration:underline;";
        $bcolor=array();
        $LCh=array();
        for ($i=0;$i<count($rgb);$i++) {
            if ($rgb['type']=='L*C*h') {
                $LCh[$i]=$color_param[$i];
            } else {
                $LCh[$i]=RGB2LCh($rgb[$i]);
               /*  if ($LCh[$i][0]<70 && $LCh[$i][0]>40) {
                  $LCh[$i][0]-=30;
                  $rgb[$i]=LCh2RGB($LCh[$i]);
               }*/
            }
            $colorcode=$rgb[$i]['color'];
            $bcolor[$i]="background-color:{$colorcode};";
            //    $border="border-width:thin;border-style:solid;";

            if      ($LCh[$i][0]>60) {$bcolor[$i].="color:#000;";}
            else //if ($LCh[$i][0]<40) 
            {$bcolor[$i].="color:#fff;";}
        }

        if ($_conf['coloredid.rate.hissi.times'] > 0 && $count>=$_conf['coloredid.rate.hissi.times']) {     // �K���`�F�b�J�[����
            $uline.="text-decoration:blink;";
        }

        //       $colorprint=1;      // 1�ɂ���ƁA�F�̕ϊ����ʂ��\�������
        if ($colorprint) {
            $debug = '';
            for ($i=0;$i<1;$i++) {
                switch ($rgb[$i]['type']) {
                case 'L*C*h' :
                    $debug.= "(L*={$rgb[$i][9]},C*={$rgb[$i][10]},h={$rgb[$i][11]})";
                    $X=$rgb[$i][3];
                    $Y=$rgb[$i][4];
                    $Z=$rgb[$i][5];
                    if ($X>0.9504 || $X<0) {$X="<span style=\"color:#F00\">{$X}</span>";}
                    if ($Y>1 || $Y<0) {$Y="<span style=\"color:#F00\">{$Y}</span>";}
                    if ($Z>1.0889 || $Z<0) {$Z="<span style=\"color:#F00\">{$Z}</span>";}
                    $debug.= ",(X={$X},Y={$Y},Z={$Z})";

                    break;
                case 'HSV' :$debug.= "(H={$rgb[$i][3]},S={$rgb[$i][4]},V={$rgb[$i][5]})";
                    break;
                case 'HLS' :$debug.= "(H={$rgb[$i][3]},L={$rgb[$i][4]},S={$rgb[$i][5]})";
                    break;
                }

                $R=$rgb[$i][0];
                $G=$rgb[$i][1];
                $B=$rgb[$i][2];
                if ($R>255 || $R<0) {$R="<span style=\"color:#F00\">{$R}</span>";}
                if ($G>255 || $G<0) {$G="<span style=\"color:#F00\">{$G}</span>";}
                if ($B>255 || $B<0) {$B="<span style=\"color:#F00\">{$B}</span>";}
                $debug.= ",(R={$R},G={$G},B={$B}),{$rgb[$i]['color']}";
            }
            //  $idstr2[1].= join(",",$rgb[0]);
            return array(
                (isset($rgb[1]) ? "{$bcolor[1]}{$border}{$uline}" : ''),
                "{$bcolor[0]}{$border}{$uline}",
                $debug);
        } else {
            return array(
                (isset($rgb[1]) ? "{$bcolor[1]}{$border}{$uline}" : ''),
                "{$bcolor[0]}{$border}{$uline}");
        }
    }

    // }}}
?>
