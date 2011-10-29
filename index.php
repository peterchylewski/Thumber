<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="utf-8">
	<title>Structured Document Editor</title>
	<link rel="stylesheet" href="">
	<style type="text/css">
		body{font-family:"HelveticaNeue-Light"}
		
		.group, 
		.section, 
		.article
		{
			margin-left: 1em;
			padding-left: 1em;

			margin-top: 0.5em;
			padding-top: 0.5em;

			margin-bottom: 0.5em;
			padding-bottom: 0.5em;

			width:1em;
		}
		
		P{
			width:33em;
		}
		
		.article{
			border-left:	1px solid #333;
			border-top:		1px solid #333;
			border-bottom:	1px solid #333;
		}
		.section{
			border-left:	1px solid #666;
			border-top:		1px solid #666;
			border-bottom:	1px solid #666;
		}
		.group{
			border-left:	1px solid #999;
			border-top:		1px solid #999;
			border-bottom:	1px solid #999;
		}
		
	</style>
	<script src=""></script>
	<!-- Created by Adrien Cater on 2011-10-29 -->
</head>

<?php

	$theText = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

?>

<body>
<div class="group 1">
	<div class="section 1">
		<div class="article 1">
			<p contenteditable="true">All of these paragraphs are editable – click on them and start typing!</p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
	
		<div class="article 2">
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>	
	</div>
	
	<div class="section 2">
		<div class="article 3">
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
		
		<div class="article 4">
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
	</div>
</div>
<div class="group 2">
	<div class="section 3">
		<div class="article 5">
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
	
		<div class="article 6">
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
	
		<div class="article 7">
			<p contenteditable="true"><?php echo($theText); ?></p>
			<p contenteditable="true"><?php echo($theText); ?></p>
		</div>
	</div>
</div>

</body>
</html>