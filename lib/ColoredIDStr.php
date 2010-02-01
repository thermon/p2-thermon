<?php

    /**
     * Merged from http://jiyuwiki.com/index.php?cmd=read&page=rep2%A4%C7%A3%C9%A3%C4%A4%CE%C7%D8%B7%CA%BF%A7%CA%D1%B9%B9&alias%5B%5D=pukiwiki%B4%D8%CF%A2
     *
     * @access  private
     * @return  string
     */
require_once P2_LIB_DIR . '/colorchange.php';
function coloredIdStyle($idstr, $id, $count=0)
{
    global $STYLE;
    static $idcount = array();    
    static $idstyles = array(); 
    static $id_color_used= array() ;

if ($count >= 2) {
        //[$id] >= 2�@�R�R�̐����ŃX���ɉ��ȏ㓯���h�c���o�����ɔw�i�F��ς��邩���܂�
        if (isset($idstyles[$id])) {
            return $idstyles[$id];
        } else {
            //	    	$alpha=0.8;	// �A���t�@�`���l��
            // ID����F�̌��𒊏o

            $coldiv=64; // �F���̕�����
            if (preg_match('/ID:/',$idstr)) { // ID���g����
                $rev_id=strrev(substr($id, 0, 8));
                $raw = base64_decode($rev_id);		// 8�������o�C�i���f�[�^6�������ɕϊ�
                $id_hex = unpack('H12', substr($raw, 0, 6));	// �o�C�i���f�[�^��16�i������ɕϊ�
                $id_bin=base_convert($id_hex[1],16,2);	// �����2�i������ɕϊ�
                while ($id_bin) {
                    $arr[]=base_convert(substr($id_bin,-6),2,10);
                    $id_bin=substr($id_bin,0,-6);
                }

                $colors[0]=$arr[0];// % $coldiv;
                $idstr2=preg_split('/:/',$idstr,2); // �R������ID������𕪊�
                array_shift($idstr2);

                if ($id_color_used[$colors[0]]++) {
                    $colors[1]=$colors[0]+($id_color_used[$colors[0]]-1)+1;
                    $idstr2[1]=substr($idstr2[0],4);
                    $idstr2[0]=substr($idstr2[0],0,4); // �R������ID������𕪊�
                }
            } else { //�V�x���A�^�C�v
                $ip_hex=preg_split('/\\./',$id);
                //var_dump($ip_hex);echo "<br>";
                $colors[1]=$ip_hex[1] % $coldiv;
                $idstr2=preg_split('/:/',$idstr,2); // �R������ID������𕪊�
                $idstr2[0].=':';

                if ($id_color_used[$colors[1]]++) {
                    $colors[2]=$colors[1]+($id_color_used[$colors[1]]-1)+1;
                    $idstr2[2]=".{$ip_hex[2]}.{$ip_hex[3]}";
                    $idstr2[1]="{$ip_hex[0]}.{$ip_hex[1]}"; // �R������ID������𕪊�
                }
            }
            $color_param=array();
            // HLS�F���
            // �F��H�F�l��0�`360�i�p�x�j
            // �P�xL(HLS)�F�l��0�i���j�`0.5�i���F�j�`1�i���j
            // �ʓxS(HLS)�F�l��0�i�D�F�j�`1�i���F�j
            foreach ($colors as $key => $color) {
                //		    		var_dump(array(/*$raw,$id_hex,$arr,$col,*/$id_top,$c1,$c2));echo "<br>";
                $color_param[$key]=array();
                $angle=deg2rad($color*180/$coldiv);
            
                $color_param[$key][0]=$color*360*4/$coldiv;		//H
                while ($color_param[$key][0]>360) {$color_param[$key][0]-=360;}

                $color_param[$key][1]=0.22+sin($angle)*0.08;	//L
                $color_param[$key][2]=0.4+sin($angle)*0.1;	    //S

                // RGB�ɕϊ�
                $color_param[$key]=ColorLib::HLS2RGB($color_param[$key]);
                $color_param[$key]['Y']=(
                                         $color_param[$key][0]*299+
                                         $color_param[$key][1]*587+
                                         $color_param[$key][2]*114
                                        )/1000;
 
            }
            // CSS�ŐF������
            $uline=$STYLE['a_underline_none']==1 ? '' : "text-decoration:underline;";
            if ($count[$id]>=25 ) {     // �K���`�F�b�J�[����
                $uline.="text-decoration:blink;";
            }
            $opacity=''; // "opacity:{$alpha};";
            foreach ($color_param as $area => $param) {
                $r=(int)$color_param[$area][0];
                $g=(int)$color_param[$area][1];
                $b=(int)$color_param[$area][2];
                if ($opacity || !$alpha) {
                    $bcolor[$area]="background-color:rgb({$r},{$g},{$b});";
                } else {
                    $bcolor[$area]="background-color:rgba({$r},{$g},{$b},{$alpha});";
                }

                // �w�i�F�ɂ���ĕ����F��ς���
	            $y1=158;
	            $y2=185; 
                if ($param['Y']>=$y1) {
					// �w�i�F�����邢�ꍇ�A�����F�����₷�����x���ɕύX
                    $y=1-($param['Y']>=$y2 ? $y2 : $y1)/$param['Y'];	//���x��$y1������$y2����������������
                    
                    $r=(int)($r*$y);
                    $g=(int)($g*$y);
                    $b=(int)($b*$y);
                    $bcolor[$area].="color:rgb({$r},{$g},{$b});";
                } else {
					// �w�i�F���Â��ꍇ
                    $y1=140;
                    $y2=160;
                    if ($param['Y']<=255-$y1) {
                        $y=($param['Y']<=255-$y2 ? $y2 : $y1);

                        $r+=(int)((255-$r)*$y/255);
                        $g+=(int)((255-$g)*$y/255);
                        $b+=(int)((255-$b)*$y/255);
                        $bcolor[$area].="color:rgb({$r},{$g},{$b});";
                    } else {
                        $bcolor[$area].="color:#fff;";
                    }
                }
                $idstr2[$area]="<span style=\"{$bcolor[$area]}{$border}{$uline}{$opacity}\">{$idstr2[$area]}</span>";
            }
            $idstr=join('',$idstr2);
            $idstyles[$id] = $bcolor;

        }
    }
    return $idstyles[$id];
}

?>
