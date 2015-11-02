<?php

include "list.php";

?><html>
<head>
<title>Test page</title>
</head>
<body>
	<header>
		<h1>List widget demo</h1>
	</header><?php

	/* Procedure style */
	?><h2>Procedure style</h2><?php
	TopList\printWidget();

	/* Procedure style, with configuration */
	?><h2>Procedure style, with config</h2><?php
	$listFilter = array('gender' => 'female', 'region' => 'asia');
	TopList\printWidget( $listFilter );

	/* User url parameters */
	?><h2>User url parameters</h2><?php
	TopList\printWidget( $_GET );

	/* Object oriented style, 1 */
	?><h2>Object oriented style, 1</h2><?php
	$widget = new TopList\Widget();
	$widget->gender = 'female';
	$widget->region = 'asia';
	$widget->printHTML();

	/* Object oriented style, 2 */
	?><h2>Object oriented style, 2</h2><?php
	$widget = new TopList\Widget( $listFilter );
	$widget->printHTML();

	/* Object oriented style, 3 */
	?><h2>Object oriented style, 3</h2><?php
	$widget = new TopList\Widget( );
	$html = $widget->getHTML();
	// do something with html
	echo( $html );

