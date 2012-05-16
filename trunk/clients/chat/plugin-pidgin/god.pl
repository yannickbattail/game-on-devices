
use Purple;
use LWP::Simple;

%PLUGIN_INFO = (
	perl_api_version => 2,
	name => "GOD Game-On-Devices",
	version => "0.1",
	summary => "plugin to connect to GOD interface",
	description => "Chat with you game through the GOD",
	author => "Yannick Battail <yannick.attail\@gmail.com>",
	url => "http://code.google.com/p/game-on-devices/",
	load => "plugin_load",
	unload => "plugin_unload"
);

$god_email = 'yannick@gmail.com';
$god_pass = 'tot';
$god_game = 'enigme';
$god_pseudoInGame = 'miaou';

$god_authKey = '';

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
	
	my $url = 'http://www.leserieux.fr/god/speak.php?authKey='.$god_authKey.'&question='.$message;
	Purple::Debug::misc("GOD", "url :".$url."\n");
	my $content = get $url;
	Purple::Debug::misc("GOD", "text gotten: ".$content);
	$im->send($content);
}

sub conv_created {
	my $conv = shift;
	Purple::Debug::misc("GOD", "conv_created ".$conv->name."\n");
}


sub plugin_load
{
	my $plugin = shift;

	my $url = 'http://www.leserieux.fr/god/hello.php?email='.$god_email.'&password='.$god_pass.'&pseudoInGame='.$god_pseudoInGame.'&game='.$god_game;
	#Purple::Debug::misc("GOD", "url :".$url."\n");
	$god_authKey = get $url;
	Purple::Debug::misc("GOD", "authKey :".$god_authKey."\n");

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