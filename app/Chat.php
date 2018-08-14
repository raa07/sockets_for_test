<?php
namespace App;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $data = [
                    'user' => $numRecv,
                    'msg' => $msg
                ];
                $client->send(json_encode($data));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        $numRecv = count($this->clients) - 1;
        foreach ($this->clients as $client) {
            $data = [
                'user' => $numRecv,
                'msg' => '<b>deleted from party</b>'
            ];
            $client->send(json_encode($data));
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
       $conn->close();
    }
}