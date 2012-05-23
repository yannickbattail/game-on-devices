<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
<style type="text/css">
.meText {
  color: #000088;
}

.gameText {
  color: #880000;
}

#response {
  width: 500px;
  height: 300px;
  overflow: scroll;
  background-color: #ffffee;
  border-width: 2px;
  border-style: inset;
  font-family: monospace;
}
</style>
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

function escapeHTMLTags(str) {
	return str.replace(new RegExp('&', 'gm'), '&amp;').replace(new RegExp('<', 'mg'), '&lt;').replace(new RegExp('>', 'mg'), '&gt;').replace(new RegExp('\n', 'mg'), '<br />');
}

function loadGameList() {
	function processData(data) {
	  try {
	    get('game').innerHTML = data;
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
	    } else {
          // something went wrong
	      alert('oups '+this.responseText+' ('+this.status+')');
	    }
	  }
	}
	var client = new XMLHttpRequest();
	client.onreadystatechange = handler;
	client.open('GET', '../gamesList.php?format=html');
	client.send();
}

function auth() {
  function processData(data) {
    try {
        var obj = eval('('+data+')');
        if (obj.authKey && (obj.authKey != '')) {
          authKey = obj.authKey;
          get('authDiv').style.display = 'none';
          get('speakDiv').style.display = 'block';
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
      } else {
          // something went wrong
        alert('oups '+this.responseText+' ('+this.status+')');
      }
    }
  }
  var client = new XMLHttpRequest();
  client.onreadystatechange = handler;
  client.open('GET', '../hello.php?email='+getV('email')+'&password='+getV('password')+'&pseudoInGame='+getV('pseudoInGame')+'&game='+getSel('game')+'&format=json');
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
          //get('response').value += 'moi: '+getV('speakText')+"\nGame: "+data.message+"\n";
          get('response').innerHTML += '<div class="meText">Me: '+getV('speakText')+'</div><div class="gameText">Game: '+escapeHTMLTags(data.message)+'</div>';
          get('response').scrollTop = 99999;
          get('speakText').value = '';
          get('info').innerHTML = escapeHTMLTags(data.info);
          var choices = data.choices.join(' | ');
          get('choices').innerHTML = 'Possible choices: '+escapeHTMLTags(choices);
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
      } else {
	    // something went wrong
        alert('oups '+this.responseText+' ('+this.status+')');
      }
    }
  }
  if (getV('speakText')) {
	  var client = new XMLHttpRequest();
	  client.onreadystatechange = handler;
	  client.open('GET', '../speak.php?authKey='+authKey+'&format=json&question='+getV('speakText')+'');
	  client.send();
  }
}

</script>
</head>
<body onload="loadGameList()">
  <h1>GOD - Game On Devices</h1>
  <h2>who are you?</h2>
  <div id="authDiv">
    New user? <a href="newUser.php">Subscribe here!</a><br />
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
        <td><select id="game"></select></td>
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
    <div id="response"></div>
    <input type="text" id="speakText" value="" onkeydown="keySpeak(event)" /> <input type="button" name="submit" value="Speak" onclick="speak()" />
    <div id="choices">Possible choices:</div>
    <div id="info"></div>
  </div>
</body>
</html>
