<?php
	/*
		Админ-панель для бота "GameBot"
		Версия данного бота: 0.01
		Ссылка на GitHub (возможно вышло обновление, проверьте): https://github.com/DenBroShow/GameBot
	*/
	
	$action = $_POST["act"];
	include("settings.php");
	if($action == "login")
	{
		$authKey = $_POST["secret"];
		if($authKey == $secretKey)
		{
			setcookie("login", $secretKey, time()+60*60*24*30);
		}
	}
	else
	{
		if(!empty($_COOKIE["login"]) && $_COOKIE["login"] != $secretKey)
		{
			setcookie('login', '', time()-3600);
		}
	}
?>

<html>
	<head>
		<meta content="text/html; charset=utf-8" />
		<link href="style.css" rel="stylesheet" />
		<title>GameBot | Панель управления</title>
	</head>
	
	<body>
		<div class="container">
			<?php
				$action = $_POST["act"];
				if(empty($_COOKIE['login']) && empty($action))
				{
					echo '<div class="auth">
							<form method="POST" action="admin.php">
								<input name="act" type="hidden" value="login" hidden>
								<br><p><input class="authinput" type="password" name="secret" placeholder="Введите секретный ключ" value=""></p>
								<input class="button" type="submit" value="Войти">
							</form>
						</div>';
				}
				else
				{
					if($action == "login")
					{
						$authKey = $_POST["secret"];
						if($authKey == $secretKey)
						{
							echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
							echo '<div class="menu">
									<h2>Ссылка на пользователя:</h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="changeuser" hidden>
										<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
										<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
										<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
										<br><input class="button" type="submit" value="Изменить">
									</form>
								</div>';
						}
						else
						{
							echo '<div class="auth">
									<h2 id="error">Вы ввели неверный секретный ключ </h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="login" hidden>
										<br><p><input class="authinput" type="password" name="secret" placeholder="Введите секретный ключ"></p>
										<input class="button" type="submit" value="Войти">
									</form>
								</div>';
						}
					}
					elseif($action == "changeuser")
					{
						$user = $_POST["userlink"];
						$moneys = $_POST["money"];
						$admin = $_POST["adminrights"];
						include("methods.php");
						$user_id = GetID($user);
						if($user_id == "no_user")
						{
							echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
							echo '<div class="menu">
									<h2 id="error">Указанная ссылка не является пользователем ВКонтакте</h2>
									<h2>Ссылка на пользователя:</h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="changeuser" hidden>
										<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
										<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
										<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
										<br><input class="button" type="submit" value="Изменить">
									</form>
								</div>';
							die;
						}
						
						if($admin == "Yes")
							$admin = "admin";
						else
							$admin = "user";
						
						if(!is_numeric($moneys) || $moneys < 0)
						{
							echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
							echo '<div class="menu">
									<h2 id="error">Ошибка в строке "Деньги". Их количество не может быть меньше 0 или в строке присутствуют буквы</h2>
									<h2>Ссылка на пользователя:</h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="changeuser" hidden>
										<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
										<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
										<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
										<br><input class="button" type="submit" value="Изменить">
									</form>
								</div>';
							die;
						}
						
						global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlBase, $startMoney;
						$connect = mysql_connect($mysqlHost, $mysqlUser, $mysqlPass);
						if(!$connect)
						{
							echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
							echo '<div class="menu">
									<h2 id="error">Ошибка подключения к базе данных</h2>
									<h2>Ссылка на пользователя:</h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="changeuser" hidden>
										<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
										<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
										<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
										<br><input class="button" type="submit" value="Изменить">
									</form>
								</div>';
							die;
						}
						else
						{
							mysql_select_db($mysqlBase, $connect);
							$query = "SELECT * FROM `accounts` WHERE `uid` = '".$user_id."'";
							$request = mysql_query($query, $connect);
							$a = mysql_fetch_array($request);
							if($a["uid"] == $user_id)
							{
								$query = "UPDATE `accounts` SET `money` = '".$moneys."', `right` = '".$admin."' WHERE `uid` = '".$user_id."'";
								mysql_query($query, $connect);
								echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
								echo '<div class="menu">
										<h2 id="ok">Данные пользователя успешно изменены</h2>
										<h2>Ссылка на пользователя:</h2>
										<form method="POST" action="admin.php">
											<input name="act" type="hidden" value="changeuser" hidden>
											<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
											<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
											<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
											<br><input class="button" type="submit" value="Изменить">
										</form>
									</div>';
								die;
							}
							else
							{
								echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
								echo '<div class="menu">
										<h2 id="error">Этот пользователь никогда не пользовался ботом</h2>
										<h2>Ссылка на пользователя:</h2>
										<form method="POST" action="admin.php">
											<input name="act" type="hidden" value="changeuser" hidden>
											<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
											<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
											<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
											<br><input class="button" type="submit" value="Изменить">
										</form>
									</div>';
								die;
							}
						}
					}
					else
					{
						if($_COOKIE['login'] == $secretKey)
						{
							echo '<div class="header"><h1>GameBot - Панель Управления</h1></div>';
							echo '<div class="menu">
									<h2>Ссылка на пользователя:</h2>
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="changeuser" hidden>
										<p><input class="authinput" type="text" name="userlink" placeholder="Ссылка/ID"></p>
										<p><h3>Деньги (кол-во): <input class="authinput" type="number" name="money"></h3></p>
										<p><h3>Права администратора: <input type="checkbox" name="adminrights" value="Yes"></h3></p>
										<br><input class="button" type="submit" value="Изменить">
									</form>
								</div>';
						}
						else
						{
							echo '<div class="auth">
									<form method="POST" action="admin.php">
										<input name="act" type="hidden" value="login" hidden>
										<br><p><input class="authinput" type="password" name="secret" placeholder="Введите секретный ключ"></p>
										<input class="button" type="submit" value="Войти">
									</form>
								</div>';
						}
					}
				}
			?>
		</div>
	</body>
</html>