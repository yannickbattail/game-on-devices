
use Purple;
use LWP::Simple;
use JSON;
use Data::Dumper;

%PLUGIN_INFO = (
	perl_api_version => 2,
	name => "GOD Game-On-Devices",
	version => "0.1",
	summary => "plugin to connect to GOD interface",
	description => "Chat with your game through the GOD",
	author => "Yannick Battail <yannick.attail\@gmail.com>",
	url => "http://code.google.com/p/game-on-devices/",
	load => "plugin_load",
	unload => "plugin_unload"
);

#$base_url = 'http://127.0.0.1/god/';
$base_url = 'http://www.leserieux.fr/god/';

%god_authKeys = {};

%gateway_db = ();

# Accounts
sub account_connecting_cb
{
	my $account = shift;
	Purple::Debug::misc("GOD", "account-connecting (" . $account->get_username() . ")\n");
}

# Buddylist
sub buddy_signed_on
{
	my $buddy = shift;
	#Purple::Debug::misc("GOD", "buddy-signed-on (" . $buddy->get_name() . ")\n");
}

# Connections
sub signed_on
{
	my $conn = shift;
	#Purple::Debug::misc("GOD", "signed-on (" . $conn->get_account()->get_username() . ")\n");
}

# Conversations
sub conv_received_msg
{
	my ($account, $sender, $message, $conv, $flags, $data) = @_;
	Purple::Debug::misc("GOD", "$data (" . $account->get_username() . ", $sender, $message, $flags)\n");
	$im = $conv->get_im_data();
	if ($im) { print "ok.\n"; } else { print "fail.\n"; }
	@u = split(/\//, $sender);
	my $user = @u[0];
	Purple::Debug::misc("GOD", "user :".$user."\n");
	if (not $god_authKeys->{$user}) {
		$god_authKeys->{$user} = god_auth($user);
	}
	if (not $god_authKeys->{$user} or ($god_authKeys->{$user} == '')) {
		$im->send('you did not register your chat address ('.$user.') go to '.$base_url.'/webInterface/manageGames.php');
	}
	$authKey = $god_authKeys->{$user};
	Purple::Debug::misc("GOD", "authKey :".$authKey."\n");
	my $url = $base_url.'speak.php?authKey='.$authKey.'&question='.$message;
	Purple::Debug::misc("GOD", "url :".$url."\n");
	my $content = get $url;
	Purple::Debug::misc("GOD", "text response: ".$content."\n");
	$im->send($content);
}

sub conv_created {
	my $conv = shift;
	Purple::Debug::misc("GOD", "conv_created ".$conv->name."\n");
}

sub god_auth {
	my $username = shift;
	#Purple::Debug::misc("GOD", "god_auth username ".$username."\n");
	$gateway_db = decode_json(get $base_url."gatewayAdmin/gatewayPidgin/imAddressDB.json");
	if (not $gateway_db) {
		Purple::Debug::misc("GOD", "fail to laod db :".$base_url."gatewayAdmin/gatewayPidgin/imAddressDB.json"."\n");
		return '';
	}
	my $info = $gateway_db->{$username};
	#Purple::Debug::misc("GOD", "god_auth info ".Dumper($gateway_db->{$username})."\n");
	if (not $info) {
		Purple::Debug::misc("GOD", "no such user in db\n");
		return '';
	}
	my $url = $base_url.'hello.php?email='.$info->{email}.'&password='.$info->{password}.'&pseudoInGame='.$info->{pseudoInGame}.'&game='.$info->{game};
	Purple::Debug::misc("GOD", "url :".$url."\n");
	my $authKey = get $url;
	Purple::Debug::misc("GOD", "authKey :".$authKey."\n");
	if (not $authKey) {
		return '';
	}
	return $authKey;
}


sub plugin_load
{
	my $plugin = shift;

	# Hook to the signals

	# Accounts
	$act_handle = Purple::Accounts::get_handle();
	Purple::Signal::connect($act_handle, "account-connecting", $plugin, \&account_connecting_cb, 0);

	# Buddy List
	$blist = Purple::BuddyList::get_handle();
	Purple::Signal::connect($blist, "buddy-signed-on", $plugin, \&buddy_signed_on, 0);

	# Connections
	$conn = Purple::Connections::get_handle();
	Purple::Signal::connect($conn, "signed-on", $plugin, \&signed_on, 0);

	# Conversations
	$conv = Purple::Conversations::get_handle();
	#Purple::Signal::connect($conv, "conversation-created", $plugin, \&conv_created);
	Purple::Signal::connect($conv, "received-im-msg", $plugin, \&conv_received_msg, "received im message");
	Purple::Signal::connect($conv, "received-chat-msg", $plugin, \&conv_received_msg, "received chat message");
}

sub plugin_unload
{
	# Nothing to do here for this plugin.
}
