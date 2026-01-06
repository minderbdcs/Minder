BEGIN { lt=""; ltcnt=0}
{ 	if ($5 == lt) {
		ltcnt++;
	} else {
		print lt, ltcnt;
		lt = $5;
		ltcnt = 1;
	}
}
END {
	print lt, ltcnt;
}
