<?php
namespace Libs;


class SafeCode{

	protected $key;

	public function __construct($key = '123')
    {
        $this->key = $key;
    }

    //加密函数
	 function passport_encrypt($txt) {
		srand((double)microtime() * 1000000);
		$encrypt_key = md5(rand(0, 32000));
		$ctr = 0;
		$tmp = '';
		for($i = 0;$i < strlen($txt); $i++) {
		   $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		   $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
		}
		return base64_encode($this->passport_key($tmp, $this->key));
	}

	//解密函数
    function passport_decrypt($txt) {
		$txt = $this->passport_key(base64_decode($txt), $this->key);
		$tmp = '';
		for($i = 0;$i < strlen($txt); $i++) {
		   $md5 = $txt[$i];
		   $tmp .= @$txt[++$i] ^ $md5;
		}
		return $tmp;
	}

	function passport_key($txt, $encrypt_key) {
		$encrypt_key = md5($encrypt_key);
		$ctr = 0;
		$tmp = '';
		for($i = 0; $i < strlen($txt); $i++) {
		   $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
		   $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
		}
		return $tmp;
	}
}

