<?php
/*
Template Name:mail-test
*/
?>

<?php
if (mb_send_mail('wakai29@gmail.com', 'TEST SUBJECT', 'TEST BODY')) {
    echo ‘送信完了’;
} else {
    echo ‘送信失敗’;
}
?>
