<meta charset="utf-8">
<script src="jquery.min.js?v=2.1.4"></script>
<textarea id="content"></textarea>
<button id="send">发送</button>
<ul id="history">
    <li>消息示例</li>
</ul>
<?php
$key = 'auto2017';
    //加密函数
function passport_encrypt($txt,$key = 'auto2017') {
	srand((double)microtime() * 1000000);
	$encrypt_key = md5(rand(0, 32000));
	$ctr = 0;
	$tmp = '';
	for($i = 0;$i < strlen($txt); $i++) {
	   $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
	   $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
	}
	return base64_encode(passport_key($tmp, $key));
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
	$userId = rand(1,10);
$sid = passport_encrypt(json_encode(array('user'=>$userId,'type'=>1)));	
echo "我是用户".$userId."<br>";

?>
<script>
console.log(<?=$userId?>);
    var wsServer = 'ws://192.168.88.128:9501?sid=<?=$sid?>';
    var websocket = new WebSocket(wsServer);
    websocket.onopen = function (evt) {
        console.log("建立连接成功.");
    };

    websocket.onclose = function (evt) {
        console.log("关闭");
    };

    websocket.onmessage = function (evt) {
        $('#history').append("<li>"+evt.data+"</li>");
        console.log('Retrieved data from server: ' + evt.data);
    };

    websocket.onerror = function (evt, e) {
        console.log('Error occured: ' + evt.data);
    };

    $("#send").click(function(){
        var content = $('#content').val();
        if(!content){
            return false;
        }
		var data={data:content}
		var string_json = JSON.stringify(data)
		console.log(string_json);
        $('#history').append("<li><strong>我</strong>："+content+"</li>");
		websocket.send(string_json);
        //websocket.send( content );
    })



</script>