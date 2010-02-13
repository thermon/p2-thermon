<?php
    /**
     * 色変換サブルーチン
     */
    // 変換式参考資料　http://image-d.isp.jp/commentary/color_cformula/index.html
    // Version.20081215 初版
    // Version.20081216 L*C*h表色系の変換関数を追加
    // Version.20081216.1 バグフィックス。
    //   HSV2RGB,HSL2RGB,LCh2RGB,RGB2LChの戻り値に変換前および変換途中のパラメータを追加。
    // Version.20081224 Lab2RGB,RGB2Lab追加
    // Version.20081226 16進でカラーコードを生成

class ColorLib{
	function RGB2ColorCode ($r,$g,$b) {
	    return sprintf("#%02X%02X%02X",
			max(0,min($r,255)),
			max(0,min($g,255)),
			max(0,min($b,255))
		); 
	}

	function HLS2RGB ($hls) {
	    // HLS→RGB変換
	    list($h,$l,$s)=$hls;

		$s=max(0,min(1,$s));
		$l=max(0,min(1,$l));
        while ($h<0)    {$h+=360;}
        while ($h>=360) {$h-=360;}

	    $max=($l<=0.5) ? $l*(1+$s) : $l*(1-$s)+$s;
	    $min=2*$l-$max;
	    if ($s==0) {
	        $l2=floor($l*255);
	        return array($l2,$l2,$l2,$h,$l,$s,"type"=>"HLS",'color'=>self::RGB2ColorCode($l2,$l2,$l2));
	    }   else {
	        $rgb=array();
	        foreach (array($h+120,$h,$h-120) as $h1) {
	            if ($h1<0)    {$h1+=360;}
	            if ($h1>=360) {$h1-=360;}

	            if ($h1<60)         {$R=$min+($max-$min)*$h1/60;}
	            else if ($h1<180)   {$R=$max;}
	            else if ($h1<240)   {$R=$min+($max-$min)*(240-$h1)/60;}
	            else                {$R=$min;}

	            array_push($rgb,floor($R*255));
	        }
	        array_push($rgb,$h,$l,$s);
	        $rgb['type']='HLS';
	        $rgb{'color'}=self::RGB2ColorCode($rgb[0],$rgb[1],$rgb[2]);
	        return $rgb;

	    }
	}

	function HSV2RGB ($hsv) {
	    // HSV→RGB変換
	    list($h,$s,$v)=$hsv;
		$s=max(0,min(1,$s));
		$v=max(0,min(1,$v));
        while ($h<0)    {$h+=360;}
        while ($h>=360) {$h-=360;}
 
	    $hi=floor($h/60) % 6;
	    $f=$h/60-$hi;
	    $p=$v*(1-$s);
	    $q=$v*(1-$f*$s);
	    $t=$v*(1-(1-$f)*$s);

	    switch ($hi) {
	    case 0: $R=$v; $G=$t; $B=$p; break;
	    case 1: $R=$q; $G=$v; $B=$p; break;
	    case 2: $R=$p; $G=$v; $B=$t; break;
	    case 3: $R=$p; $G=$q; $B=$v; break;
	    case 4: $R=$t; $G=$p; $B=$v; break;
	    case 5: $R=$v; $G=$p; $B=$q; break;
	    }
	    $rgb=array(floor($R*255),floor($G*255),floor($B*255),$h,$s,$v,'type'=>'HSV');
	    $rgb{'color'}=self::RGB2ColorCode($rgb[0],$rgb[1],$rgb[2]);
	    return $rgb;
	}
 
	function Lab2RGB ($Lab) {
	    $xyz=self::Lab2XYZ($Lab);
	    $rgb=self::XYZ2RGB($xyz);
	    $rgb=array_merge($rgb,$xyz,$Lab);
	    $rgb['type']='L*a*b*';
	    return $rgb;
	}
	function LCh2RGB ($LCh) {
	    list($L,$C,$h)=$LCh;
		$L=max(0,min(100,$L));
	    //     if ($C>100) {$C=100;}
	    $C=max(0,$C);
        while ($h<0)    {$h+=360;}
        while ($h>=360) {$h-=360;}
	    $LCh[0]=$L;
	    $LCh[1]=$C;
	    $LCh[2]=$h;

	    $Lab=self::LCh2Lab($LCh);
	    $rgb=self::Lab2RGB($Lab);
	    $rgb=array_merge($rgb,$LCh);
	    $rgb['type']='L*C*h';
	    return  $rgb;
	}
	function RGB2Lab ($rgb) {
	    $xyz=self::RGB2XYZ($rgb);
	    $Lab=self::XYZ2Lab($xyz);
	    return array_merge($Lab,$xyz,$rgb);
	}
	function RGB2LCh ($rgb) {
	    $Lab=self::RGB2Lab($rgb);
	    $LCh=self::Lab2LCh($Lab);
	    return array_merge($LCh,$Lab);
	}
	function RGB2XYZ ($rgb) {
	    $linearRGB=array();
	    foreach ($rgb as $c) {
			$c=max(0,min(1,$c/255));
	        if ($c<=0.04045) {$c/=12.92;}
	        else {$c=pow(($c+0.055)/(1+0.055),2.4);}
	        array_push($linearRGB,$c);
	    }
	    list($r,$g,$b)=$linearRGB;

	    $x=0.412453*$r+0.35758 *$g+0.180423*$b;
	    $y=0.212671*$r+0.71516 *$g+0.072169*$b;
	    $z=0.019334*$r+0.119193*$g+0.950227*$b;
	    return array($x,$y,$z);
	}
 
	function XYZ2Lab ($xyz) {
	    // D65光源補正
	    $xyz[0]/=0.95045;
	    $xyz[2]/=1.08892;

	    $f=array();
	    foreach ($xyz as $c) {
			$c=max(0,min(1,$c));
	        array_push($f,($c>0.008856) ? pow($c,1/3) : (903.3*$c+16)/116);
	    }
	    list($x,$y,$z)=$f;
	    $L=116*$y-16;
	    $a=500*(($x/0.95045)-$y);
	    $b=200*($y-($z/1.08892));

	    return array($L,$a,$b);     // L:[0..100],a:[-134..220],b:[-140..122]
	}

	function Lab2XYZ ($Lab) {
	    //  if ($Lab[0]>=100) {$fy=1;}
	    if ($Lab[0]<7.9996) {
	        $fy=$Lab[0]/903.3;
	        $fx=$fy+$Lab[1]/3893.5;
	        $fz=$fy-$Lab[2]/1557.4;	
		    $xyz=array($fx,$fy,$fz);
	    } else {
	        $fy=($Lab[0]+16)/116;
	        $fx=$fy+$Lab[1]/500;
	        $fz=$fy-$Lab[2]/200;
		    $xyz=array_map(create_function('$x','return pow($x,3);'),array($fx,$fy,$fz));
	    }

	    // D65光源補正
	    $xyz[0]*=0.95045;
	    $xyz[2]*=1.08892;
/*	    for ($i=0;$i<3;$i++) {
	        $xyz[$i]=floor($xyz[$i]*10000)/10000;
	    }*/

	    return $xyz;
	}  
	function XYZ2RGB ($xyz) {
	    list($x,$y,$z)=array_map(create_function('$x','return $x=max(0,min(1,$x));'),$xyz);

	    if ($y>=1) {$r=$g=$b=1;}
	    else {
	        $r= 3.240479*$x -1.53715 *$y -0.498535*$z;
	        $g=-0.969256*$x +1.875991*$y +0.041556*$z;
	        $b= 0.055648*$x -0.204043*$y +1.057311*$z;
	    }

	    $rgb=array();
	    foreach (array($r,$g,$b) as $c) {
	        if ($c<=0.0031308) {$c*=12.92;}
	        else {$c=pow($c,1/2.4)*(1+0.055)-0.055;}
	        $c*=255;

	        array_push($rgb,floor($c));
	    }
	    $rgb{'color'}=self::RGB2ColorCode($rgb[0],$rgb[1],$rgb[2]);

	    return $rgb;
	}
	function Lab2LCh ($Lab) {
	    list($L,$a,$b)=$Lab;
		$L=max(0,min(100,$L));
		$a=max(-100,min(100,$a));
		$b=max(-100,min(100,$b));

	    $C=sqrt(pow($a,2)+pow($b,2));
	    $h=rad2deg(atan2($b,$a));
        while ($h<0)    {$h+=360;}
        while ($h>=360) {$h-=360;}
	    return array($L,$C,$h);
	}
	function LCh2Lab ($LCh) {
	    list($L,$C,$h)=$LCh;

	    $h2=deg2rad($h);
	    $a=$C*cos($h2);
	    $b=$C*sin($h2);
	    return array($L,$a,$b);
	}
}
?>
