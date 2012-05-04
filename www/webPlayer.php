<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
<script type="text/javascript">

var authKey = '';

function get(id) {
  return document.getElementById(id);
}
function getV(id) {
  return get(id).value;
}
function getSel(id) {
  var e = get(id);
  return e.options[e.selectedIndex].value;
}

function auth() {
	function processData(data) {
	  try {
	      var obj = eval('('+data+')');
	      if (obj.authKey && (obj.authKey != '')) {
	        authKey = obj.authKey;
	        get('authDiv').style.display = 'none';
	        get('speakDiv').style.display = 'block';
	        get('response').value = '';
	      } else {
	        alert('no data.authKey: '+data);
	      }
	  } catch (e) {
	    alert('exception: '+e);
      alert('error: '+data.toSource());
	  }
	}
	
	function handler() {
	  if(this.readyState == this.DONE) {
	    if(this.status == 200 && this.responseText != null) {
	      // success!
	      processData(this.responseText);
	      return;
	    }
	  }
	    // something went wrong
	      alert(this.responseText+' ('+this.status+')');
	}
	var client = new XMLHttpRequest();
	client.onreadystatechange = handler;
	client.open('GET', 'hello.php?email='+getV('email')+'&password='+getV('password')+'&pseudoInGame='+getV('pseudoInGame')+'&game='+getSel('game')+'&format=json');
	client.send();
}

function keySpeak(event) {
  if (event.which == 13) {
    speak();
    return false;
  }
}

function speak() {
  function processData(data) {
    try {
        var data = eval('('+data+')');
        if (data.status) {
          get('response').value += 'moi: '+getV('speakText')+"\nGame: "+data.message+"\n";
          get('response').scrollTop = 99999;
          get('speakText').value = '';
          get('info').innerHTML = data.info;
        } else {
          alert('no data.authKey: '+data);
        }
    } catch (e) {
      alert('exception: '+e);
    alert('error: '+data.toSource());
    }
  }
  
  function handler() {
    if(this.readyState == this.DONE) {
      if(this.status == 200 && this.responseText != null) {
        // success!
        processData(this.responseText);
      }
      // something went wrong
      processData(null);
    }
  }
  if (getV('speakText')) {
	  var client = new XMLHttpRequest();
	  client.onreadystatechange = handler;
	  client.open('GET', 'speak.php?authKey='+authKey+'&format=json&question='+getV('speakText')+'');
	  client.send();
  }
}
</script>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>who are you?</h2>
  <div id="authDiv">
    New user? <a href="new.php">Subscribe!</a><br />
    <table>
      <tr>
        <th><label for="email">Email</label></th>
        <td><input type="text" id="email" value="" /></td>
      </tr>
      <tr>
        <th><label for="password">Password</label></th>
        <td><input type="password" id="password" value="" /></td>
      </tr>
      <tr>
        <th><label for="game">Game</label></th>
        <td><select id="game">
        <?php
        $iterator = new DirectoryIterator('./games/');
        foreach ($iterator as $fileinfo) {
        	if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        		echo '<option value="'.$fileinfo->getFilename().'">'.$fileinfo->getFilename().'</option>';
        	}
        }
        ?>
        </select></td>
      </tr>
      <tr>
        <th><label for="pseudoInGame">Pseudo in game</label></th>
        <td><input type="text" id="pseudoInGame" value="" /></td>
      </tr>
      <tr>
        <th>&nbsp;</th>
        <td><input type="submit" name="submit" value="subscribe" onclick="auth()" /></td>
      </tr>
    </table>
  </div>
  <div id="speakDiv" style="display: none;">
    <textarea id="response" cols="100" rows="10"></textarea><br />
    <input type="text" id="speakText" value="" onkeydown="keySpeak(event)" /> <input type="button" name="submit" value="Speak" onclick="speak()" />
    <div id="info"></div>
  </div>
</body>
</html>
