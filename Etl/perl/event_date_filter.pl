#!/usr/bin/perl

$|;

use strict;
use warnings;
use DateTime::Format::Strptime;
use Time::Piece;

my $today = DateTime->now( time_zone => 'local' );
my $diff = 0;
my @cols;
my $parser;
my $event_ts;
my $tdiff;

while (<STDIN>) {
	if (/\'SESSION\'/) {
		print $_;
	}
	else {
	
		@cols = split(",");
		$parser = DateTime::Format::Strptime->new( pattern => "%Y-%m-%d %H:%M:%S" );
		$event_ts = $parser->parse_datetime(substr($cols[6],1,length($cols[6])-2));
		$tdiff = $today - $event_ts;
		$diff=$tdiff->months;

		if ( $diff <= 3 ) {
			print $_;
		}
	}
}
