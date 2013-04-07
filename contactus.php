<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/13/12
 * Time: 5:18 AM
 * To change this template use File | Settings | File Templates.
 */


        $form = array();

        $form = array_map('safehtml', $_POST);

        $mail = new PHPMailer();

        if(PHPMAILER == '3') {
            $mail->IsSMTP(); // set mailer to use SMTP
            $mail->Host = $smtp['host'];  // specify main and backup server
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $smtp['login'];  // SMTP username
            $mail->Password = $smtp['password']; // SMTP password
        }
        elseif(PHPMAILER == '2') {
            $mail->IsSendmail(); // set mailer to use SMTP
        }
        else {
        }

        $mail->From = $conf['general_e_mail'];
        $mail->FromName = $conf['general_e_mail_name'];
        $mail->AddAddress($conf['general_e_mail']);

        // Replace some variables in the subject
        $lang['Mailer_Subject'] = str_replace('{website_name}', $conf['website_name'], $lang['Mailer_Subject']);
        $lang['Mailer_Subject'] = str_replace('{user_e_mail}', $form['e_mail'] , $lang['Mailer_Subject']);

        $mail->Subject = $lang['Mailer_Subject'];

        $mail_message = $form['message'] . ' Name: ' . $form['name'] . ', Address: ' . $form['address'] . ', Phone: ' . $form['phone'];

        $mail->MsgHTML    = $mail_message;
        $mail->AltBody = removehtml($mail_message);

        $mail->Send();


?>