<?php
	/*
		Файл обработки сообщений "GameBot"
		Версия данного бота: 0.01
		Ссылка на GitHub (возможно вышло обновление, проверьте): https://github.com/DenBroShow/GameBot
	*/
	
	include("settings.php");
	include("methods.php");
	$keyGet = $_REQUEST["akey"];
	$userdata = json_decode(file_get_contents('php://input'));
	
	if($keyGet == $secretKey)
	{
		switch ($userdata->type) {
			case 'confirmation':
				die($confirmationKey);
				break;
			case 'message_new':
				if($onGroup == 0)
					SendMessage($userdata->object->user_id, CheckMessage($userdata));
				elseif($onGroup == 1)
				{
					if(CheckGroup($userdata->object->user_id))
						SendMessage($userdata->object->user_id, CheckMessage($userdata));
					else
						SendMessage($userdata->object->user_id, "ты не состоишь в нашей группе! Сначала вступи, а затем поговорим :)");
				}
				else
					die(json_encode(array("response" => 0, "error" => array("error_id" => 2, "error_message" => "Unknown 'onGroup' param value"))));
				die("ok");
				break;
		}
	}
	else
	{
		die(json_encode(array("response" => 0, "error" => array("error_id" => 1, "error_message" => "Bad secret key"))));
	}
?>