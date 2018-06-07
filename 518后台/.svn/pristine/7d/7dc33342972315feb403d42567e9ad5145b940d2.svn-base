<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
	<?php
		/*
	 * 解密
	 *
	 */
	 function decrypt($txt, $key = 'goapk')
			{
				$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
				$ikey ="-x6g6ZWm2G9dfsaevEE_FWUKL.pOq3kRIxsZ6rm-";
				$knum = 0;$i = 0;
				$tlen = strlen($txt);
				while(isset($key{$i})) $knum +=ord($key{$i++});
				$ch1 = $txt{$knum % $tlen};
				$nh1 = strpos($chars,$ch1);
				$txt = substr_replace($txt,'',$knum % $tlen--,1);
				$ch2 = $txt{$nh1 % $tlen};
				$nh2 = strpos($chars,$ch2);
				$txt = substr_replace($txt,'',$nh1 % $tlen--,1);
				$ch3 = $txt{$nh2 % $tlen};
				$nh3 = strpos($chars,$ch3);
				$txt = substr_replace($txt,'',$nh2 % $tlen--,1);
				$nhnum = $nh1 + $nh2 + $nh3;
				$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
				$tmp = '';
				$j=0; $k = 0;
				$tlen = strlen($txt);
				$klen = strlen($mdKey);
				for ($i=0; $i<$tlen; $i++) {
					$k = $k == $klen ? 0 : $k;
					$j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
					while ($j<0) $j+=64;
					$tmp .= $chars{$j};
				}
				$tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
				return base64_decode($tmp);
			}
	@$str=$_POST['str'];
	 if(!empty($str)){
			echo decrypt($str, $key = 'goapk');
		}
		
	?>
	<form action="" method="post">
		<table>
			<tr><td><input type="text" name="str"></td></tr>
			<tr><td><input type="submit" name="submit" value="提交"></td></tr>
		</table>
		
	</form>

</body>
</html>
