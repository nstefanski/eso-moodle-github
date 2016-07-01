<?php

$r = (empty($_GET['r'])) ? '' : $_GET['r'];
$p = (empty($_GET['p'])) ? '' : $_GET['p'];

if ($r == 'AESCA Boulder Online') {
	header( 'Location: https://docs.google.com/presentation/d/1LEzCsbM7HfL0BlR0sEGCIelKk9r76xKgYaMB_QoJkGs/embed?start=true&loop=true&delayms=10000' ) ;
} elseif ($r == 'AESCA Austin') {
	header( 'Location: https://docs.google.com/presentation/d/1lraRkm0weVaIKb3B2aY77GDSQOMaEpZ970PlrLF5vJY/embed?start=true&loop=true&delayms=10000' ) ;
} elseif ($r == 'AESCA Boulder') {
	header( 'Location: https://docs.google.com/presentation/d/1JWl2oetFH6CvnqP1OlxtFEZlEy0S-xtxMyj0b6xnOsk/embed?start=true&loop=true&delayms=10000' ) ;
} elseif ($r == 'EOICA Online Certificate') {
	header( 'Location: https://docs.google.com/presentation/d/1LEzCsbM7HfL0BlR0sEGCIelKk9r76xKgYaMB_QoJkGs/embed?start=true&loop=true&delayms=10000' ) ;
} elseif ($r == 'EOICA Online Fundamentals') {
	header( 'Location: https://docs.google.com/presentation/d/1VopqNhU8T6jUBcroHt_bS99jEOMIEErGRZ6D_pp_-Fc/embed?start=true&loop=true&delayms=10000' ) ;
} elseif ($p == 'Fundamentals Program') {
	header( 'Location: https://docs.google.com/presentation/d/1VopqNhU8T6jUBcroHt_bS99jEOMIEErGRZ6D_pp_-Fc/embed?start=true&loop=true&delayms=10000' ) ;
}

die();