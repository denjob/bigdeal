<?php

	try {
		$db = new PDO("mysql:host=localhost;dbname=local;charset=utf8","root","");
		$q = 'SELECT u.fio, u.email,group_concat(o.name) as objs FROM `recyclebin` as rb left join users as u on u.id=rb.uid 
			left join objects as o on o.id=rb.obj_id
			where (UNIX_TIMESTAMP()-rb.date)<=(60*60*24*30) and rb.obj_id not in (select obj_id from buyend as b 
			where (UNIX_TIMESTAMP()-b.date)<=(60*60*24*30) and b.uid=rb.uid)
			group by u.fio, u.email';
		$pr = $db->prepare($q);
		$pr->execute();
		$res = $pr->fetchAll();
		$pr->closeCursor();
		$pr = null;
		foreach($res as $row) {
			$message = 'Добрый день, '.$row['fio'].'! В вашем вишлисте хранятся товары: '.$row['objs'].'.<br>';
			echo $message;
			/*
			@mail($row['email'], 'Сообщение от магазина', $message);
			//либо через PHPMailer 
			require 'PHPMailer/PHPMailerAutoload.php';
			$mail = new PHPMailer;
			$mail->setLanguage('ru', 'PHPMailer/language/');
			$mail->SMTPDebug = 4;                             
			$mail->isSMTP();                                  
			$mail->Host = 'host';                     
			$mail->SMTPAuth = true;                              
			$mail->Username = 'info@magazin.com';           
			$mail->Password = 'pass';
			$mail->SMTPSecure = false;
			$mail->Port = 25;                                   
			$mail->setFrom('info@magazin.com', 'Magazin');
			$mail->addAddress($row['email']); 
			$mail->isHTML(true);
			$mail->Subject = 'Сообщение от магазина';
			$mail->Body    = $message;
			if(!$mail->send())  echo 'Error: ' . $mail->ErrorInfo;
			else  echo $row['fio'].' отправлено';
			*/
		}
	}catch(PDOException $e) {
		die($e);
	}
?>