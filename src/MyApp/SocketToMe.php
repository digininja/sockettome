<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Client {
	public $name;
	public $gets_debug;
	public $connection;
		
	public function __construct() {
		$this->name = "Unknown";
		$this->gets_debug = false;
		$this->connection = null;
	}

	public function send($msg) {
		$this->connection->send($msg);
	}
}

class SocketToMe implements MessageComponentInterface {
	protected $clients;
	private $secret_number;

	public function __construct() {
	    $this->clients = Array();
		$this->debug ("SocketToMe is starting up");
		$this->debug ("");
		$this->set_secret_number();
	}

	private function debug ($message) {
		print $message . "\n";
		foreach ($this->clients as $client) {
			if ($client->gets_debug) {
				$client->send("Debug: " . $message);
			}
		}
	}

	private function set_secret_number() {
		$this->secret_number = rand(1,100);
		$this->debug ("Ssssh, the secret number is " . $this->secret_number);
	}

	public function onOpen(ConnectionInterface $conn) {
	    // Store the new connection to send messages to later
		$client = new Client();
		$client->name = "User " . $conn->resourceId;
		$client->gets_debug = false;
		$client->connection = $conn;

	    $this->clients[$conn->resourceId] = $client;

	    echo "New connection! ({$conn->resourceId})\n";
		# This needs to come from Host not user
		$this->onMessage($conn, "New User Joined");
	}

	public function onMessage(ConnectionInterface $from, $msg) {
	    $this->debug ("New message: " . $msg);

		if (array_key_exists ($from->resourceId, $this->clients)) {
			$user = $this->clients[$from->resourceId];
		} else {
			$this->debug ("Message in from unknown connection: " . $from->resourceId);
			return;
		}

		if (preg_match ("/^([A-Z]):(.*)/", $msg, $matches)) {
			$command = $matches[1];
			$arg = $matches[2];

			switch ($command) {
				case "U":
					$this->debug("Request for current user list");
					$current_user_names = array();
					foreach ($this->clients as $client) {
						if ($from !== $client->connection) {
							$current_user_names[] = $client->name;
						}
					}
					#$current_user_names[] = "<script>alert(1)</script>xss";
					$json = json_encode ($current_user_names);
				#	var_dump ($json);
				#	var_dump ($current_user_names);
					$from->send($json);
					break;
				case "G":
					if (is_numeric ($arg)) {
						$guess = intval ($arg);
						if ($guess == $this->secret_number) {
							$this->debug ("Winner, Winner, Chicken Dinner");
							$this->debug ("Winning user: " . $user->name . " (" . $user->resourceId . ")");

							$this->debug ("Resetting to start again");
							$this->set_secret_number();

							foreach ($this->clients as $client) {
								if ($from !== $client->connection) {
									$client->send("Host: " . $user->name . " guessed the winning number");
								} else {
									$client->send("Host: Congratulations, you guessed the winning number");
								}
								$client->send("Host: A new secret number is ready, start your guesses");
							}
						} else {
							$from->send("Host: Sorry, try again");
						}
					} else {
						$this->debug ("Invalid guess");
					}
					break;
				case "C":
					$numRecv = count($this->clients) - 1;
					$this->debug (sprintf('Message from %s (%d) sending message "%s" to %d other connection%s' , $user->name, $from->resourceId, $arg, $numRecv, $numRecv == 1 ? '' : 's'));
					foreach ($this->clients as $client) {
						if ($from !== $client->connection) {
							// The sender is not the receiver, send to each client connected
							$client->send("User " . $user->name . ": " . $arg);
						}
					}
					break;
				case "D":
					if ($arg == "on") {
						$user->gets_debug = true;
						$this->debug ("Debug now enabled for " . $user->name);
						$from->send ("Debug: Debugging is now enabled");
					} elseif ($arg == "off") {
						$user->gets_debug = false;
						$this->debug ("Debug now disabled for " . $user->name);
						$from->send ("Debug: Debugging is now disabled");
					} else {
						$from->send ("Debug: Please specify either on or off");
					}
					break;
				case "E":
					$user->send("Echo: " . $arg);
					break;
				case "N":
					$this->debug ("User " . $user->name . " (" . $from->resourceId . ") is now known as " . $arg);
					$user->name = $arg;
					$user->send("Name: You are now known as " . $arg);
					break;
				default:
					$this->debug ("Unknown command: " . $msg);
			}
		}
	}

	public function onClose(ConnectionInterface $conn) {
	    // The connection is closed, remove it, as we can no longer send it messages
	    unset ($this->clients[$conn->resourceId]);

	    echo "Connection {$conn->resourceId} has disconnected\n";
	}

	public function onError(ConnectionInterface $conn, \Exception $e) {
	    echo "An error has occurred: {$e->getMessage()}\n";

	    $conn->close();
	}
}
