<?php

namespace MinderNG\JsonRPC;

class Server extends \JsonRPC\Server {
    public function getResponse(array $data, array $payload = array())
    {
        if (! array_key_exists('id', $payload)) {
            return '';
        }

        $response = array(
            'jsonrpc' => '2.0',
            'id' => $payload['id']
        );

        return array_merge($response, $data);
    }
}