<?php
$opcao = file_get_contents("opcao.txt");

header("Location: $opcao");
exit;
?>