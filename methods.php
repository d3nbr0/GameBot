<?php
	/*
		Файл методов "GameBot"
		Версия данного бота: 0.01
		Ссылка на GitHub (возможно вышло обновление, проверьте): https://github.com/DenBroShow/GameBot
	*/
	
	include("settings.php");
	include("messages.php");
	include("commands.php");

	function CheckGroup($user_id)
	{
		global $groupID;
		$response = json_decode(file_get_contents("https://api.vk.com/method/groups.isMember?group_id=".$groupID."&user_id=".$user_id."&v=5.0"));
		if($response->response == 1)
			return true;
		else
			return false;
		return false;
	}
	
	function CheckMessage($userdata)
	{
		$message = $userdata->object->body;
		$msg = mb_strtolower($message);
		global $autoMessages;
		$returnMessage = CheckCommand($userdata->object->user_id, $msg);
		
		if($returnMessage != "nullmsg")
			return $returnMessage;
		else
		{
			for($i = 0; $i < count($autoMessages); $i++)
			{
				if(isset($autoMessages[$msg]))
				{
					return $autoMessages[$msg];
					break;
				}
			}
		}
		return "я не знаю как тебе ответить :(";
	}
	
	function SendMessage($user_id, $message)
	{
		global $botToken;
		$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0")); 
		$user_name = $user_info->response[0]->first_name;
		$msg = $user_name.", ".$message;
		$request_params = array( 
			'message' => $msg, 
			'user_id' => $user_id, 
			'access_token' => $botToken, 
			'v' => '5.0' 
		);
		$get_params = http_build_query($request_params); 
		file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
	}
	
	function GetID($ulink)
	{
		$totalSlash = substr_count($ulink, '/');
		for($i = 0; $i < $totalSlash; $i++)
		{
			$ulink = strstr($ulink, '/');
			$ulink = substr($ulink, 1);
		}
		
		$user_info = json_decode(file_get_contents("https://api.vk.com/method/utils.resolveScreenName?screen_name=".$ulink."&v=5.0"));

		if($user_info->response->type == "user")
		{
			return $user_info->response->object_id;
		}
		else
		{
			return "no_user";
		}
	}
?>