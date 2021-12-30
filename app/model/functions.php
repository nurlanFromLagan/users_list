<?php

//функция делает редирект по указанному пути
function redirect_to ($path) {
    header('Location: ' . $path);
    die();
}

