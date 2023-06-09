<?php

echo '<style>';
echo 'body { background-color: black; color: white; }';
echo '</style>';

// URL
$url = 'http://USA_URL/api_jsonrpc.php';



// Dados do corpo da requisição
$data = array(
    'jsonrpc' => '2.0',
    'method' => 'user.login',
    'params' => array(
        'username' => 'username',
        'password' => 'password'
    ),
    'id' => 1
);

// Inicializar a sessão CURL
$ch = curl_init();

// Configurar as opções do CURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: application/json'
));

// Executar a requisição CURL
$response = json_decode(curl_exec($ch));

// Verificar erros
if (curl_errno($ch)) {
    echo 'Erro CURL: ' . curl_error($ch);
    echo 'Não foi possivel continuar o codigo';
    exit;
}

// Fechar a sessão CURL
curl_close($ch);

// Exibir a resposta
$token = $response->result;
echo 'Key de login: ' . $token . '<br><br>';



$MALHA = [
    [
        'name' => 'Nome do host',
        'interfaces' => [
            'Interface1',
            'Interface2'
        ]
    ]
];

$dates = [
    [
        'time_from' => '2022-06-01 00:00:01',
        'time_till' => '2022-06-30 23:59:59'
    ],
    [
        'time_from' => '2022-07-01 00:00:01',
        'time_till' => '2022-07-30 23:59:59'
    ],
    [
        'time_from' => '2022-08-01 00:00:01',
        'time_till' => '2022-08-30 23:59:59'
    ],
    [
        'time_from' => '2022-09-01 00:00:01',
        'time_till' => '2022-09-30 23:59:59'
    ],
    [
        'time_from' => '2022-10-01 00:00:01',
        'time_till' => '2022-10-30 23:59:59'
    ],
    [
        'time_from' => '2022-11-01 00:00:01',
        'time_till' => '2022-11-30 23:59:59'
    ],
    [
        'time_from' => '2022-12-01 00:00:01',
        'time_till' => '2022-12-30 23:59:59'
    ],
    [
        'time_from' => '2022-06-01 00:00:01',
        'time_till' => '2022-12-30 23:59:59'
    ],
];

foreach ($dates as $date) {
    echo '<br>';
    echo 'Periodo: ' . $date['time_from'] . ' até ' . $date['time_till'] . '<br>';
    foreach ($MALHA as $host) {

        #Função retorna o hostid baseado no nome do host.
        $hostid = getHostId($host['name'], $token, $url);

        # -----------------------

        foreach ($host['interfaces'] as $interface) {
            $item_in = getItem($hostid, $interface, $token, $url, 'net.if.in[ifHCInOctets');
            $item_out = getItem($hostid, $interface, $token, $url, 'net.if.out[ifHCOutOctets');
            $history_in = getTrend($item_in['id'], strtotime($date['time_from']), strtotime($date['time_till']), $token, $url);
            $history_out = getTrend($item_out['id'], strtotime($date['time_from']), strtotime($date['time_till']), $token, $url);

            // Exibir a resposta

            $total_in = 0;
            foreach ($history_in as $item) {
                $total_in += (int) $item->value_avg;
            }

            $gb_in = $total_in / pow(1024, 3);
            $tb_in = $total_in / pow(1024, 4);

            $total_out = 0;
            foreach ($history_out as $item) {
                $total_out += (int) $item->value_avg;
            }

            $gb_out = $total_out / pow(1024, 3);
            $tb_out = $total_out / pow(1024, 4);
        

            echo '<br>';
            echo 'Host: ' . $host['name'] . '<br>';
            echo 'Interface: ' . $interface . '<br>';
            echo '------------ Dados ------------ <br>';
            echo 'Description: ' . $item_in['description'] . '<br>';
            echo 'Dados totais enviados (EM bytes): ' . $total_in . '<br>';
            echo 'Dados totais enviados (EM GB): ' . number_format($gb_in, 4) . '<br>';
            echo 'Dados totais enviados (EM TB): ' . number_format($tb_in, 4) . '<br>';
            echo '<br>';
            echo 'Description: ' . $item_out['description'] . '<br>';
            echo 'Dados totais recebidos (EM bytes): ' . $total_out . '<br>';
            echo 'Dados totais recebidos (EM GB): ' . number_format($gb_out, 4) . '<br>';
            echo 'Dados totais recebidos (EM TB): ' . number_format($tb_out, 4) . '<br>';
            echo '<br>';
            echo 'Dados totais somados (EM GB) '. number_format($gb_in + $gb_out, 4) . '<br>';
            echo 'Dados totais somados (EM TB) '. number_format($tb_in + $tb_out, 4) . '<br>';
            echo '<br>';
        }
    }
}

function getHostId(string $host_name, $token, $url): int
{
    $data_get_host = array(
        'jsonrpc' => '2.0',
        'method' => 'host.get',
        'params' => array(
            'search' => array(
                'name' => $host_name
            ),
            'limit' => 1
        ),
        'auth' => $token,
        'id' => 1
    );

    $ch = curl_init();

    // Configurar as opções do CURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_get_host));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    // Executar a requisição CURL
    $response = curl_exec($ch);

    // Verificar erros
    if (curl_errno($ch)) {
        echo 'Erro CURL: ' . curl_error($ch);
        echo 'Não foi possivel continuar o codigo';
        exit;
    }


    $response = json_decode($response);
    $hostid = $response->result[0]->hostid;

    return $hostid;
}

function getItem($hostid, $interface, $token, $url, $key_): array
{
    $data = array(
        'jsonrpc' => '2.0',
        'method' => 'item.get',
        'params' => array(
            'output' => ['itemid', 'name'],
            'hostids' => $hostid,
            'search' => array(
                'name' => $interface,
                'key_' => $key_
            ),
            'sortfield' => 'name',
            'limit' => 1
        ),
        'id' => 1,
        'auth' => $token
    );

    // Inicializar a sessão CURL
    $ch = curl_init();

    // Configurar as opções do CURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    // Executar a requisição CURL
    $response = json_decode(curl_exec($ch));

    // Verificar erros
    if (curl_errno($ch)) {
        echo '<br> Erro CURL: ' . curl_error($ch);
    }

    // Fechar a sessão CURL
    curl_close($ch);

    $itemid = $response->result[0]->itemid;
    $description = $response->result[0]->name;

    return [
        'id' => $itemid,
        'description' => $description
    ];
}

function getHistory($itemid, $time_from, $time_till, $token, $url): array
{
    $data = array(
        'jsonrpc' => '2.0',
        'method' => 'history.get',
        'params' => array(
            'output' => 'extend',
            'history' => 3,
            'itemids' => $itemid,
            'time_from' => $time_from,
            'time_till' => $time_till
        ),
        'auth' => $token,
        'id' => 1
    );

    // Inicializar a sessão CURL
    $ch = curl_init();

    // Configurar as opções do CURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    // Executar a requisição CURL
    $response = json_decode(curl_exec($ch));
    // Verificar erros
    if (curl_errno($ch)) {
        echo 'Erro CURL: ' . curl_error($ch);
        exit;
    }

    // Fechar a sessão CURL
    curl_close($ch);
    return $response->result;
}

function getTrend($itemid, $time_from, $time_till, $token, $url): array
{
    $data = array(
        'jsonrpc' => '2.0',
        'method' => 'trend.get',
        'params' => array(
            'output' => 'extend',
            'itemids' => $itemid,
            'time_from' => $time_from,
            'time_till' => $time_till
        ),
        'auth' => $token,
        'id' => 1
    );

    // Inicializar a sessão CURL
    $ch = curl_init();

    // Configurar as opções do CURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    // Executar a requisição CURL
    $response = json_decode(curl_exec($ch));
    // Verificar erros
    if (curl_errno($ch)) {
        echo 'Erro CURL: ' . curl_error($ch);
        exit;
    }

    // Fechar a sessão CURL
    curl_close($ch);
    return $response->result;
}
