<?php

namespace Alexc\ProyectoAgustin\Core;

class Tokenizer {
    public static function generarClaveApi()
    {
        return md5(microtime() . rand());
    }
}
