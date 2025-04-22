<?php

// necessario ter o o composer instalado
// necessário criar o script em uma pasta, então execute no terminal "composer require guzzlehttp/guzzle"

require 'vendor/autoload.php';

use GuzzleHttp\Client;

if ($argc < 2) {
    echo "Uso: php checkip.php <IP>\n";
    exit(1);
}

$ip = $argv[1];

$client = new Client([
    'base_uri' => 'https://api.abuseipdb.com/api/v2/'
]);

$response = $client->request('GET', 'check', [
    'query' => [
        'ipAddress' => $ip,
        'maxAgeInDays' => '90',
    ],
    'headers' => [
        'Accept' => 'application/json',
        'Key' => '<KEY>' // altere para sua key
    ],
]);

$output = $response->getBody();
$ipDetails = json_decode($output, true);
//print_r($ipDetails);
echo "\nScore: " . $ipDetails['data']['abuseConfidenceScore'] . "\n";
echo "Total Reports: " . $ipDetails['data']['totalReports'] . "\n\n";

if(($ipDetails['data']['totalReports'] > 1000) && ($ipDetails['data']['abuseConfidenceScore'] >= 90)) {

// executa o comando "iptables -A INPUT -s $ip -j DROP" onde $ip é o endereço ip a ser bloqueado
exec("iptables -A INPUT -s $ip -j DROP 2>&1", $output, $resultCode);

// exibe a mensagem de ip bloqueado
echo "IP $ip bloqueado com sucesso.\n";

// sessão comentada está em manutenção

/*
    //bloqueia
        $checkCommand = "iptables-save | grep $ip | cut -d ' ' -f4 | sed 's/...$//'";
        exec($checkCommand, $output, $exists);

        if ($exists === 0) {
            echo "IP $ip já está bloqueado.\n";
        } else {
            exec("iptables -A INPUT -s $ip -j DROP 2>&1", $output, $resultCode);
        if ($resultCode === 0) {
            echo "IP $ip bloqueado com sucesso.\n";
        } else {
            echo "Erro ao bloquear IP $ip:\n";
            print_r($output);
        }
    }
*/

}
