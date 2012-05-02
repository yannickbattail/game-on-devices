<?php

function ActionCut($nouns)
{
	switch ($nouns[0])
	{
		case "tree":
			echo "you cut the tree.";
			break;
		case "power":
			echo "you shut down the power.";
			break;
	}
}

function ActionPlant($nouns)
{
	if ($nouns[0] == "tree")
		echo "you planted a tree.";
}