<?php
	/*
		Файл команд (обработчик) "GameBot"
		Версия данного бота: 0.01
		Ссылка на GitHub (возможно вышло обновление, проверьте): https://github.com/DenBroShow/GameBot
	*/
	
	function CheckCommand($user_id, $message)
	{
		$args = preg_split("/[\s,]+/", $message);
		$stats = GetUserStats($user_id);
		$msg;
		
		if($args[0] == "баланс" || $args[0] == "банк" || $args[0] == "счет" || $args[0] == "счёт")
			$msg = "добро пожаловать в банк! &#128182;<br>Ваш баланс составляет: ".$stats["money"]." монет";
		elseif($args[0] == "игра")
			$msg = Game($user_id, $args[1]);
		elseif($args[0] == "рандом")
			$msg = Random($args);
		elseif($args[0] == "вероятность")
			$msg = "вероятность этого события равна ".rand(0, 100)."%";
		elseif($args[0] == "профиль")
			$msg = GetProfile($user_id);
		elseif($args[0] == "перевод" || $args[0] == "переведи" || $args[0] == "отправь" || $args[0] == "отправить")
			$msg = SendMoney($user_id, $args);
		else
			$msg = "nullmsg";
		return $msg;
	}
	
	function SendMoney($user_id, $args)
	{
		$totalSlash = substr_count($args[1], '/');
		for($i = 0; $i < $totalSlash; $i++)
		{
			$args[1] = strstr($args[1], '/');
			$args[1] = substr($args[1], 1);
		}	
		$user_info = json_decode(file_get_contents("https://api.vk.com/method/utils.resolveScreenName?screen_name=".$args[1]."&v=5.0"));
		$userd = "no_user";
		if($user_info->response->type == "user")
		{
			$userd = $user_info->response->object_id;
		}
		else
		{
			$userd = "no_user";
		}
		
		if(count($args) == 3 && is_numeric($args[2]) && $userd != "no_user")
		{
			$targetUser = GetUserStats($userd);
			$user = GetUserStats($user_id);
			if(!empty($targetUser["uid"]))
			{
				if($user["money"] >= $args[2])
				{
					if($args[2] > 0)
					{
						LostMoney($user_id, $args[2]);
						AddMoney($userd, $args[2]);
						$user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$userd}&v=5.0")); 
						$user_name = $user_info->response[0]->first_name;
						return "вы отправили пользователю ".$user_name." ".$args[2]." монет &#9989;";
					}
					else
						return "вы не можете отправить меньше 0 монет &#10060;";
				}
				else
					return "у вас недостаточно монет для перевода &#10060;";
			}
			else
				return "этот пользователь никогда не пользовался ботом &#10060;";
		}
		else
			return "ошибка в аргументах команды (используйте: перевод [ссылка] [сумма]) &#10060;";
	}
	
	function GetProfile($user_id)
	{
		$a = GetUserStats($user_id);
		if($a["right"] == "admin")
			$ar = "имеются";
		else
			$ar = "не имеются";
		return "твой профиль &#9749;<br>Права администратора: ".$ar."<br>Общаешься с ботом с: ".date('d.m.Y H:i:s', $a["firstMessage"]);
	}
	
	function GetUserStats($user_id)
	{
		global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlBase, $startMoney;
		$connect = mysql_connect($mysqlHost, $mysqlUser, $mysqlPass);
		if(!$connect)
			die(json_encode(array("response" => 0, "error" => array("error_id" => 3, "error_message" => "Can't connect to MySQL server"))));
		else
		{
			mysql_select_db($mysqlBase, $connect);
			$query = "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'";
			$request = mysql_query($query, $connect);
			$a = mysql_fetch_array($request);
			if($a["uid"] == $user_id)
				return $a;
			else
			{
				$query = "INSERT INTO `accounts` (`uid`, `money`, `firstMessage`) VALUES ('".$user_id."', '".$startMoney."', '".time()."')";
				mysql_query($query, $connect);
				$query = "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'";
				$request = mysql_query($query, $connect);
				$a = mysql_fetch_array($request);
				return $a;
			}
		}
	}
	
	function Random($args)
	{
		if(count($args) == 3 && is_numeric($args[1]) && is_numeric($args[2]))
			return "мне нравится число ".rand($args[1], $args[2]);
		else
			return "мне нравится число ".rand(0, 100);
	}
	
	function Game($user_id, $money)
	{
		$stats = GetUserStats($user_id);
		$msg;
		if($money <= $stats["money"])
		{
			if($money > 10 && $money < 10000)
			{
				$bool = rand(0, 100);
				if($bool <= 50)
				{
					$win = $money*2;
					AddMoney($user_id, $win);
					$msg = "вы выигрываете и ваша ставка удваивается! Добавлено: ".$win." монет &#9989;";
				}
				else
				{
					LostMoney($user_id, $money);
					$msg = "вы проиграли и ваша ставка обнулилась. Убрано: ".$money." монет &#127920;";
				}
			}
			else
				$msg = "ставка не должна быть меньше 10 или больше 10000 монет &#10060;";
		}
		else
			$msg = "у вас недостаточно монет на счету &#10060;";
		return $msg;
	}
	
	function AddMoney($user_id, $money)
	{
		$stats = GetUserStats($user_id);
		$toAdd = $stats["money"]+$money;
		$query = "UPDATE `accounts` SET `money` = '".$toAdd."' WHERE `uid` = '".$user_id."'";
		global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlBase;
		$connect = mysql_connect($mysqlHost, $mysqlUser, $mysqlPass);
		mysql_query($query, $connect);
	}
	
	function LostMoney($user_id, $money)
	{
		$stats = GetUserStats($user_id);
		$toAdd = $stats["money"]-$money;
		$query = "UPDATE `accounts` SET `money` = '".$toAdd."' WHERE `uid` = '".$user_id."'";
		global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlBase;
		$connect = mysql_connect($mysqlHost, $mysqlUser, $mysqlPass);
		mysql_query($query, $connect);
	}
?>