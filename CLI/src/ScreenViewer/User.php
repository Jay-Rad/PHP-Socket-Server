<?php
namespace ScreenViewer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

### Error handling ###
function handleError($errNum, $errText) {
    file_put_contents("./Logs/Errors.txt", date(DATE_ATOM) . "\t" . $errNum . "  " . $errText . "\n", FILE_APPEND);
    return false;
}
set_error_handler(function($errNum, $errText){handleError($errNum, $errText);});

### Logging ###
function writeToLog($text) {
    $msg = date(DATE_ATOM) . "\t" . $text . "\n";
    echo $msg;
    file_put_contents("./Logs/Log.txt", $msg, FILE_APPEND);
}

class User implements MessageComponentInterface {
    protected $Clients;

    public function __construct() {
        $this->Clients = new \SplObjectStorage;
    }
    public function send($msg) {
        writeToLog("Sending to " . $this->SessionID . ": " . $msg);
        $this->Socket->send($msg);
    }
    public function close() {
        $this->Socket->close();
    }
    public function onOpen(ConnectionInterface $conn) {
        // Add the socket connection to the client list.
        $this->Clients->attach($conn); 
        writeToLog("New connection! ({$conn->resourceId})\n");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $partner = null;
        // Find the partner's client, if there is one.
        foreach ($this->Clients as $client) {
            if ($from->PartnerID == $client->ID)
            {
                $partner = $client;
            }
        }
        
        $jsonMessage = json_decode($msg);
        // JSON data received.  Switch based on "Type" property.
        switch ($jsonMessage->Type) {
            case 'ConnectionType':
                // Client is announcing its connection type.  Assign it a session ID.
                $from->ConnectionType = $jsonMessage->ConnectionType; 
                $sessionID = str_pad(rand(0, 999), 3, "0", STR_PAD_LEFT) . " " . str_pad(rand(0, 999), 3, "0", STR_PAD_LEFT);
                $from->ID = str_replace(" ", "", $sessionID);
                $from->send('{ "Type": "SessionID", "SessionID": "' . $sessionID . '" }');
                break;
            case 'Connect':
                $remoteID = str_replace(" ", "", $jsonMessage->SessionID);
                $remoteClient = null;
                // Try to find remote client based on supplied ID.
                foreach ($this->Clients as $client) {
                    if ($client->ID == $remoteID)
                    {
                        $remoteClient = $client;
                    }
                }
                if ($remoteClient != null)
                {
                    if ($remoteClient->PartnerID != null)
                    {
                        // Client already has a partner.
                        $this->send('{ "Type": "Connect", "Status": "AlreadyHasPartner" }');
                    }
                    else
                    {
                        // Successful connection.
                        $from->PartnerID = $remoteID;
                        $remoteClient->PartnerID = $from->ID;
                        $remoteClient->send($msg);
                        $from->send($msg);
                    }
                }
                else
                {
                    // Session ID not found.
                    $from->send('{ "Type": "Connect", "Status": "InvalidID" }');
                }
                break;
            default:
                if ($partner != null)
                {
                    $partner->send($msg);
                }
                else
                {
                    writeToLog("No partner connected.");
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Find and close partner's socket.
        foreach ($this->Clients as $client) {
            if ($client->ID == $conn->PartnerID)
            {
                $client->close();
				return;
            }
        }
        // Remove the connection from the list since it's no longer needed.
        $this->Clients->detach($conn);
        writeToLog("Connection {$conn->resourceId} has disconnected\n");
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        writeToLog("An error has occurred: {$e->getMessage()}\n");
        $conn->close();
    }
}
