<?php
	//busca no banco todos gcmIds
	function getGcmIds($link, $id) {
		$query = "SELECT user_gcm_id FROM device_user WHERE user_id = '$id'";
		$resultado = mysqli_query($link, $query) or die("Erro na consulta: " . mysqli_error($link));
		$gcId = mysqli_fetch_object($resultado);
		return $gcId -> user_gcm_id;
	}
	
	//generic php function to send GCM push notification
	function sendPushNotificationToGCM($registatoin_ids, $message) {
		//Google cloud messaging GCM-API url
        $url = 'https://android.googleapis.com/gcm/send';
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
   		// Google Cloud Messaging GCM API Key
	    define("GOOGLE_API_KEY", "google api key aqui");    
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);       
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        } 
        curl_close($ch);
        return $result;
    }
?>
<?php
	//conexao com banco por mysqli
	$link = mysqli_connect("localhost", "root", "", "gcm") or die("Error " . mysqli_errno($link));
	//this block is to post message to GCM on-click
	$pushStatus = ""; 
	$gcmRegID = array();
	if(!empty($_GET["push"])) {
		if(isset($_POST['checkId'])){
			for($i = 0; $i<count($_POST['checkId']); $i++){
				$idCompara = $_POST['checkId'][$i];
				$gcmRegID[]  = getGcmIds($link, $idCompara);
			}
		}
		if (isset($gcmRegID)) {
			if(isset($_POST['eventoRadio'])){
				$titulo = $_POST['titulo'];
				$desc = $_POST['desc'];
				$local = $_POST['local'];
				if((isset($titulo)) && (isset($desc)) && (isset($local))){
					$titulo = utf8_encode($titulo);
					$desc = utf8_encode($desc);
					$local = utf8_encode($local);
					$message = array("titulo" => $titulo, "desc" => $desc, "local" => $local);
					$pushStatus = sendPushNotificationToGCM($gcmRegID, $message);
				}
			}else if(isset($_POST['messageRadio'])){
				$pushMessage = $_POST["message"]; 
				if(isset($pushMessage)) {
					$pushMessage = utf8_encode($pushMessage);
					$message = array("m" => $pushMessage); 
					$pushStatus = sendPushNotificationToGCM($gcmRegID, $message);
				}
			}else if(isset($_POST['linkRadio'])){
				$link = $_POST['link'];
				if(isset($link)){
					$message = array("link" => $link);
					$pushStatus = sendPushNotificationToGCM($gcmRegID, $message);
				}
			}
		}
	}
	
	//this block is to receive the GCM regId from external (mobile apps)
	//if(!empty($_GET["shareRegId"])) {
	if(isset($_POST['method'])){
		$gcmRegID  = $_POST['gcmId'];
		$userName = $_POST['userName'];
		$userMail = $_POST['userMail'];
		
		$querySelect = "SELECT user_gcm_id FROM device_user";
		$resultSelect = mysqli_query($link, $querySelect) or die("Erro no select: " . mysqli_error($link));
		$existeIgual = FALSE;		
		while($rowR = mysqli_fetch_array($resultSelect)){
			if($rowR['user_gcm_id'] == $gcmRegID) $existeIgual = TRUE;
		}
		if($existeIgual){
			echo "EXISTE IGUAL";
		}else{
			$queryInsert = "INSERT INTO device_user (user_id, user_name, user_mail, user_gcm_id) VALUES (NULL, '$userName', '$userMail', '$gcmRegID')";
			$resultInsert = mysqli_query($link, $queryInsert) or die("Erro na consulta: " . mysqli_error($link));
			echo "Ok!";
		}
		exit();
	} 
?>
<html>
    <head>
        <title>Google Cloud Messaging</title>
	<link type="text/css" rel="stylesheet" href="stylesheet.css" />
    </head>
	<body>
      <script type="text/javascript" src="functions.js"></script>
		<div id="header">
			<h1>(GCM) Server in PHP</h1> 
		</div>
        <div id="center">
            <form method="post" action="?push=1" name="f1">                                      
                <div class="left">                                
                    <label for="message">Mensagem</label><br>
                    <textarea rows="3" name="message" id="message" cols="23" placeholder="Message to transmit via GCM" ></textarea><br>
                    <label for="titulo">Evento</label><br>
                    <input type="text" name="titulo" id="titulo" placeholder="T&iacute;tulo" disabled><br>
                    <textarea rows="2" name="desc" id="desc" cols="23" placeholder="Descri&ccedil;&atilde;o" disabled></textarea><br>
                    <input type="text" name="local" id="local" placeholder="Local" disabled><br>
                    <label for="link">Link</label><br>
                    <input type="text" name="link" id="link" placeholder="Link" disabled><br>
                    <ul>
                        <li><input type="radio" name="messageRadio" id="messageRadio" value="mensagem" onChange="javascript:desHabilita(messageRadio)" checked><label for="messageRadio">Mensagem</label></li>
                        <li><input type="radio" name="eventoRadio" id="eventoRadio" value="evento" onChange="javascript:desHabilita(eventoRadio)"><label for="eventoRadio">Evento</label></li>
                        <li><input type="radio" name="linkRadio" id="linkRadio" value="downXml" onChange="javascript:desHabilita(linkRadio)"><label for="linkRadio">Enviar Link</label></li>
                    </ul>
                    <input type="submit" id="btnSend" value="Enviar" />
                </div>
                <div class="right">
                    <table width="100%" border="1">
                        <thead>
                            <tr>
                                <th>
                                    <a href="javascript:selecionar_tudo()">Todos</a><br>
                                    <a href="javascript:deselecionar_tudo()">Nenhum</a>
                                </th>
                                <th>ID</th>
                                <th>User Name</th>
                                <th>User Mail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $query = "SELECT * FROM device_user";
                                $result = mysqli_query($link, $query) or die("Erro na consulta: " . mysqli_error($link));
                                $idCheck = 0;
                                while($row = mysqli_fetch_array($result)){
                                    $idCheck++;
                                    $id = $row['user_id'];
                                    $userName = $row['user_name'];
                                    $userMail = $row['user_mail'];
                            ?>
                            <tr>
                                <td><input type="checkbox" name="checkId[]" id="checkBox[]" value="<?php echo $id ?>"></td>
                                <td style="text-align: center;"><?php echo $id ?></td>
                                <td><?php echo $userName ?></td>
                                <td><?php echo $userMail ?></td>
                            </tr>
                            <?php
                                }
                            ?>
                            <tr>
                                <td colspan="4" id="lasttd"><?php print_r($gcmRegID); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <h3><?php echo $pushStatus; ?></h3>	
                </div>
            </form>
    	</div>
    </body>
</html>
