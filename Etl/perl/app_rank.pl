#! /usr/bin/perl
# Autofetch category rank
#
# Originally by Ben Chatelain (http://benchatelain.com/2009/03/05/scraping-app-store-rankings-around-the-world/)
#
# Modified	2009/05/25	Rob Terrell (http://touchcentric.com)
#	accepts command-line arguments for appId, category, and free/paid
#	returns just the ranking integer or "n/a" to better integrate into workflows
#	caches data for increased speed
#

package itunesRank;

use warnings;
use strict;

my $appName; # now unused
my $appID = shift;
my $categoryName = shift;
my $freePaid = shift;
my $country = shift;

if (!$country) { 
	$country = "United States";
}

if (!$freePaid) {
	$freePaid = "free";
}

if (!$categoryName) {
	$categoryName = "Top Overall";
}

my %categories = (
	"Top Overall"		=> 25204,
	"Books"				=> 25470,
	"Business"			=> 25148,
	"Education"			=> 25156,
	"Entertainment"		=> 25164,
	"Finance"			=> 25172,
	"Healthcare & Fitness"		=> 25188,
	"Lifestyle"			=> 25196,
	"Medical"			=> 26321,
	"Music"				=> 25212,
	"Navigation"		=> 25220,
	"News"				=> 25228,
	"Photography"		=> 25236,
	"Productivity"		=> 25244,
	"Reference"			=> 25252,
	"Social Networking"	=> 25260,
	"Sports"			=> 25268,
	"Travel"			=> 25276,
	"Utilities"			=> 25284,
	"Weather"			=> 25292,
	"All Games"			=> 25180,
	"Games/Action"		=> 26341,
	"Games/Adventure"	=> 26351,
	"Games/Arcade"		=> 26361,
	"Games/Board"		=> 26371,
	"Games/Card"		=> 26381,
	"Games/Casino"		=> 26341,
	"Games/Dice"		=> 26341,
	"Games/Educational"	=> 26411,
	"Games/Family"		=> 26421,
	"Games/Kids"		=> 26431,
	"Games/Music"		=> 26441,
	"Games/Puzzle"		=> 26451,
	"Games/Racing"		=> 26461,
	"Games/Role Playing"=> 26471,
	"Games/Simulation"	=> 26481,
	"Games/Sports"		=> 26491,
	"Games/Strategy"	=> 26501,
	"Games/Trivia"		=> 26511,
	"Games/Word"		=> 26521,
);

my %countryIDs = (
	"United States" => 143441,
	"Argentina" => 143505,
	"Australia" => 143460,
	"Belgium" => 143446,
	"Brazil" => 143503,
	"Canada" => 143455,
	"Chile" => 143483,
	"China" => 143465,
	"Colombia" => 143501,
	"Costa Rica" => 143495,
	"Croatia" => 143494,
	"Czech Republic" => 143489,
	"Denmark" => 143458,
	"Deutschland" => 143443,
	"El Salvador" => 143506,
	"Espana" => 143454,
	"Finland" => 143447,
	"France" => 143442,
	"Greece" => 143448,
	"Guatemala" => 143504,
	"Hong Kong" => 143463,
	"Hungary" => 143482,
	"India" => 143467,
	"Indonesia" => 143476,
	"Ireland" => 143449,
	"Israel" => 143491,
	"Italia" => 143450,
	"Korea" => 143466,
	"Kuwait" => 143493,
	"Lebanon" => 143497,
	"Luxembourg" => 143451,
	"Malaysia" => 143473,
	"Mexico" => 143468,
	"Nederland" => 143452,
	"New Zealand" => 143461,
	"Norway" => 143457,
	"Osterreich" => 143445,
	"Pakistan" => 143477,
	"Panama" => 143485,
	"Peru" => 143507,
	"Phillipines" => 143474,
	"Poland" => 143478,
	"Portugal" => 143453,
	"Qatar" => 143498,
	"Romania" => 143487,
	"Russia" => 143469,
	"Saudi Arabia" => 143479,
	"Schweitz/Suisse" => 143459,
	"Singapore" => 143464,
	"Slovakia" => 143496,
	"Slovenia" => 143499,
	"South Africa" => 143472,
	"Sri Lanka" => 143486,
	"Sweden" => 143456,
	"Taiwan" => 143470,
	"Thailand" => 143475,
	"Turkey" => 143480,
	"United Arab Emirates" => 143481,
	"United Kingdom" => 143444,
	"Venezuela" => 143502,
	"Vietnam" => 143471,
	"Japan" => 143462
);

#print "$appID, $categoryName, $freePaid\n";


if ($country eq "world") {
	getAppRankingInCategoryForWorld($appID, $categories{$categoryName});
} else {
	fetchAppCategoryRankForCountry($appID, $categories{$categoryName}, $country, $countryIDs{$country});
}


#
# Subroutines
#

sub getAppRankingInCategoryForUS {
	my ($appID, $categoryID) = @_;
	my ($country, $storeID);
	$country = "United States";
	$storeID = 143441;
	# $categoryID = 25284;
	fetchAppCategoryRankForCountry($appID, $categoryID, $country, $storeID);
}


sub getAppRankingInCategoryForWorld {
	my ($appID, $categoryID) = @_;
	my %appStore = (
		143441 => "United States",
		143505 => "Argentina",
		143460 => "Australia",
		143446 => "Belgium",
		143503 => "Brazil",
		143455 => "Canada",
		143483 => "Chile",
		143465 => "China",
		143501 => "Colombia",
		143495 => "Costa Rica",
		143494 => "Croatia",
		143489 => "Czech Republic",
		143458 => "Denmark",
		143443 => "Deutschland",
		143506 => "El Salvador",
		143454 => "Espana",
		143447 => "Finland",
		143442 => "France",
		143448 => "Greece",
		143504 => "Guatemala",
		143463 => "Hong Kong",
		143482 => "Hungary",
		143467 => "India",
		143476 => "Indonesia",
		143449 => "Ireland",
		143491 => "Israel",
		143450 => "Italia",
		143466 => "Korea",
		143493 => "Kuwait",
		143497 => "Lebanon",
		143451 => "Luxembourg",
		143473 => "Malaysia",
		143468 => "Mexico",
		143452 => "Nederland",
		143461 => "New Zealand",
		143457 => "Norway",
		143445 => "Osterreich",
		143477 => "Pakistan",
		143485 => "Panama",
		143507 => "Peru",
		143474 => "Phillipines",
		143478 => "Poland",
		143453 => "Portugal",
		143498 => "Qatar",
		143487 => "Romania",
		143469 => "Russia",
		143479 => "Saudi Arabia",
		143459 => "Schweitz/Suisse",
		143464 => "Singapore",
		143496 => "Slovakia",
		143499 => "Slovenia",
		143472 => "South Africa",
		143486 => "Sri Lanka",
		143456 => "Sweden",
		143470 => "Taiwan",
		143475 => "Thailand",
		143480 => "Turkey",
		143481 => "United Arab Emirates",
		143444 => "United Kingdom",
		143502 => "Venezuela",
		143471 => "Vietnam",
		143462 => "Japan"
	);
	while (my ($storeID, $country) = each(%appStore)) {
		print "$country: ";
		fetchAppCategoryRankForCountry($appID, $categoryID, $country, $storeID);
	}
}

sub fetchAppCategoryRankForCountry {
	my ($myAppID, $categoryID, $country, $storeID) = @_;
	my ($rank, $found);
	my @top100= fetchTop100InCategory($storeID, $categoryID);
	for (my $i = 0; $i < $#top100; $i += 3) {
		$rank = $top100[$i];
		my $appID = $top100[$i+1];
		my $title = $top100[$i+2];
	    # print $rank . " " . $title . " " . $appID . "\n";
		if ($appID == $myAppID) {
			$found = 1;
			# print $rank . " " . $title . "\n";
			last;
		}
	}
	if ($found) {
		#print "$country: $rank\n";
		print "$rank\n";
	} else {
		print "n/a\n";
	}
}

sub fetchTop100InCategory {
	my($storeID, $categoryID) = @_;

	# 30 == paid
	# 27 == free
	my $popId;
	if ($freePaid eq 'free') {
		$popId = 27;
	}
	else { 
		$popId = 30;
	}
	
	
	# TODO: cache results
	my $filename = "/tmp/cat-$categoryID-$storeID-$freePaid.html";
	my $fetchcmd;
	if (-e $filename) {	
		$fetchcmd = qq{curl -z $filename -s -A "iTunes/4.2 (Macintosh; U; PPC Mac OS X 10.2" -H "X-Apple-Store-Front: $storeID-1" 'http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewTop?id=$categoryID&popId=$popId' };
	} else {
		$fetchcmd = qq{curl -s -A "iTunes/4.2 (Macintosh; U; PPC Mac OS X 10.2" -H "X-Apple-Store-Front: $storeID-1" 'http://itunes.apple.com/WebObjects/MZStore.woa/wa/viewTop?id=$categoryID&popId=$popId' };
	}
	# Might need to pipe through these...
	# | gunzip
	# | xmllint --format -
	
	my $doc = `$fetchcmd`;
	if ($doc eq '') {
		# curl says used the cached file
		open(FILE, "<", $filename);
		local $/;
		$doc = <FILE>;
		close(FILE);
	} else {
		# cached the data
		open(FILE, ">", $filename);
		print FILE $doc;
		close(FILE);
	}

	my @top100;
	#while ($doc =~ m#(\d+)\.\.*?viewSoftware\?id=(\d+).*?draggingName="([^"]+)"#gs) {
	# Ben's original regex no longer works
	while ($doc =~ m#(\d+)\.<.*?viewSoftware\?id=(\d+).*?draggingName="([^"]+)"#gs) {
		# 1 => rank
		# 2 => app ID
		# 3 => app name
	    # print $1 . " " . $3 . "\n"; #" " . $2 . "\n";
		push(@top100, ($1, $2, $3));
	}

	return @top100;
}