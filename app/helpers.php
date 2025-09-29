<?php
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        return [];
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) continue; // pula linhas mal formatadas

        [$name, $value] = $parts;
        $env[trim($name)] = trim($value);
    }
    return $env;
}

function setEnvKey($key, $value, $path = '.env') {
    $env = loadEnv($path);
    $env[$key] = $value;
    $lines = [];
    foreach ($env as $k => $v) {
        $lines[] = "$k=$v";
    }
    file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
}

function removeEnvKey($key, $path = '.env') {
    $env = loadEnv($path);
    if (isset($env[$key])) {
        unset($env[$key]);
        $lines = [];
        foreach ($env as $k => $v) {
            $lines[] = "$k=$v";
        }
        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}

function clearEnv($path = '.env') {
    if (file_exists($path)) {
        file_put_contents($path, ""); // apaga todo o conteúdo
    }
}
