<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>GOD - Game On Devices - new user?</title>
<style type="text/css">
#speakDiv {
  background-color: black;
  color: #aaaaaa;
  float: left;
  border-width: 2px;
  border-style: inset;
  font-family: monospace;
}

#response {
  width: 500px;
  height: 300px;
  overflow: hidden;
}

#speakText {
  border: none;
  width: auto;
  background-color: black;
  font-family: monospace;
  color: #aaaaaa;
}

#info {
  display: none;
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

function auth() {
	function processData(data) {
	  try {
	      var obj = eval('('+data+')');
	      if (obj.authKey && (obj.authKey != '')) {
	        authKey = obj.authKey;
	        get('authDiv').style.display = 'none';
	        get('speakDiv').style.display = 'block';
	        get('prompt').innerHTML = ''+getV('pseudoInGame')+'@'+getSel('game')+'&gt;';
	        get('speakText').focus();
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
	      alert(this.responseText+' ('+this.status+')');
	    }
	  }
	}
	var client = new XMLHttpRequest();
	client.onreadystatechange = handler;
	client.open('GET', '../hello.php?email='+getV('email')+'&password='+getV('password')+'&pseudoInGame='+getV('pseudoInGame')+'&game='+getSel('game')+'&format=json');
	client.send();
}

function keySpeak(event) {
  if (event.which == 13) { // enter
    speak();
  }
  if (event.which == 9) { // tab
    get('response').innerHTML += ''+get('choices').innerHTML+'<br /> ';
    get('response').scrollTop = 99999;
    return false;
  }
  return true;
}

function speak() {
  function processData(data) {
    try {
        var obj = eval('('+data+')');
        if (obj.status) {
          //get('response').value += 'moi: '+getV('speakText')+"\nGame: "+obj.message+"\n";
          get('response').innerHTML += getV('pseudoInGame')+'@'+getSel('game')+'&gt;'+getV('speakText')+'<br /> ';
          get('response').scrollTop = 99999;
          get('speakText').value = '';
          get('choices').innerHTML = escapeHTMLTags(obj.choices.join(' '));
          get('info').innerHTML = escapeHTMLTags(obj.info);
          typeWriter(obj.message);
        } else {
          alert('no obj.authKey: '+obj);
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

function typeWriter(txt) {
  var text = txt;
  var delay = 50;
  var charNb = 0;
  
  function type1char() {
    if (charNb < txt.length) {
      get('response').innerHTML += escapeHTMLTags(txt[charNb]);
      get('response').scrollTop = 99999;
      charNb++;
      setTimeout(type1char, delay);
    } else {
      get('response').innerHTML += '<br />';
      get('response').scrollTop = 99999;
    }
  }
  setTimeout(type1char, delay);  
}


</script>
</head>
<body>
  <h1>GOD - Game On Devices</h1>
  <h2>who are you?</h2>
  <div id="authDiv">
    New user? <a href="newUser.php">Subscribe!</a><br />
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
        $iterator = new DirectoryIterator('../games/');
        foreach ($iterator as $fileinfo) {
        	if ($fileinfo->isDir() && !$fileinfo->isDot() && ($fileinfo->getFilename() != '.svn')) {
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
  <div id="speakDiv" style="display: none;" onclick="get('speakText').focus();">
    <div id="response">
      <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br /> <br />
    </div>
    <span id="prompt"></span><input type="text" id="speakText" value="" onkeydown="return keySpeak(event)" />
    <div id="choices" style="display: none;"></div>
    <div id="info"></div>
  </div>
  <br />Use [TAB] for choices.
</body>
</html>
