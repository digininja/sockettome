<!doctype html>
<html lang="en">
	<head>
		<title>SocketToMe</title>
		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
		<script type="text/javascript">
			var conn = new WebSocket('ws://<?=$_SERVER['SERVER_ADDR']?>:8080');

			conn.onopen = function(e) {
				console.log("Connection established!");
			};

			conn.onmessage = function(e) {
				console.log(e.data);
				var receive = document.getElementById("receive");
				receive.value = e.data + "\n" + receive.value;
			};

			function guess_message() {
				var msg = document.getElementById("guess").value.trim();

				if (isNaN(msg)) {
					alert ("Your guess must be a number");
				} else {
					if (msg != "") {
						conn.send("G:"+msg);
					}
				}
			}
			function set_name_message() {
				var msg = document.getElementById("set_name").value.trim();
				if (msg != "") {
					conn.send("N:" + msg);
				}
			}
			function chat_message() {
				var msg = document.getElementById("chat").value.trim();
				if (msg != "") {
					conn.send("C:" + msg);
				}
			}
			function get_users() {
				conn.send("U:");
			}
		</script>
	</head>
	<body>
		<h1>SocketToMe</h1>
		<p>
			Welcome to SocketToMe, where you can while away the hours chatting with friends, playing games and fuzzing sockets.
		</p>
		<p>
			<input type="button" value="Get Users" onclick="get_users()" />
		</p>
		<p>
			Don't be a stranger, tell everyone your name<br />
			<label for="set_name">Set Name:</label> <input id="set_name" type="text" name="set_name" value="" />
			<input type="button" value="Change" onclick="set_name_message()" />
		</p>
		<p>
			I'm thinking of a number between 0 and 999, win adoration from your friends by guessing it.<br />
			<label for="guess">Guess:</label> <input id="guess" type="text" name="guess" value="" />
			<input type="button" value="Send" onclick="guess_message()" />
		</p>
		<p>
			Chat with other players in our exclusive chat room.<br />
			<textarea cols="90" rows="30" disabled="disabled" id="receive" name="receive"></textarea><br />
			<label for="chat">Chat:</label> <input id="chat" type="text" name="chat" value="" />
			<input type="button" value="Send" onclick="chat_message()" />
			<br />
		</p>
		<hr />
		<p>
			Lab created by Robin Wood - <a href="https://digi.ninja">DigiNinja</a>
		</p>
	</body>
</html>
