<?php
	$ip = ip2long($_SERVER['REMOTE_ADDR']);
	$r = 0;
	try {
		$db = new PDO("mysql:host=localhost;dbname=local;charset=utf8","root","");
		$q = 'select count(*) as cnt from rating where uip=:ip';
		$pr = $db->prepare($q);
		$pr->execute(array(':ip'=>$ip));
		$cnt = (int)$pr->fetch(PDO::FETCH_ASSOC)['cnt'];
		$pr->closeCursor();
		$pr = null;
		if(isset($_POST['Stars'])) {
			if((int)$cnt==0) {
				$q = 'insert into rating(obj_id,rating,uip) values(:objid,:rating,:uip)';
				$pr = $db->prepare($q);
				$pr->execute(array(
					':objid' => 2,
					':rating' => (int)$_POST['Stars'],
					':uip' => $ip
				));
				$pr->closeCursor();
				$pr = null;
			}
			header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}else{
			$q = 'SELECT round(avg(rating)) as r FROM rating where obj_id=:objid';
			$pr = $db->prepare($q);
			$pr->execute(array(':objid'=>2));
			$r = (int)$pr->fetch(PDO::FETCH_ASSOC)['r'];
		}
	}catch(PDOException $e) {
		die($e);
	}
?>
<!DOCTYPE>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="css/style.css"></link>
		<title>Рейтинг товара</title>
		<script type="text/javascript" src="js/script.js"></script>
	</head>
	<body>
		<div class="main">
			<form id="form-stars" action="#" method="POST">
				<p class="main__stars">
				<input id="star5" type="radio" name="Stars" value="5" <?= ($r==5?'checked':''); ?>/>
				<label title="" for="star5"></label>

				<input id="star4" type="radio" name="Stars" value="4" <?= ($r==4?'checked':''); ?>/>
				<label title="" for="star4"></label>

				<input id="star3" type="radio" name="Stars" value="3" <?= ($r==3?'checked':''); ?>/>
				<label title="" for="star3"></label>

				<input id="star2" type="radio" name="Stars" value="2" <?= ($r==2?'checked':''); ?>/>
				<label title="" for="star2"></label>

				<input id="star1" type="radio" name="Stars" value="1" <?= ($r==1?'checked':''); ?>/>
				<label title="" for="star1"></label>
				</p>
	
				<p class="main__btn">
					<input type="submit" value="Кнопка"></input>
				</p>
			</form>
		</div>
	</body>
</html>