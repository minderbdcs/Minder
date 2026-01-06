BEGIN { lt=""; ltcnt=0}
{ 	if ($1 == lt) {
		ltcnt+= $2;
	} else {
		print lt, ltcnt;
		lt = $1;
		ltcnt = $2;
	}
}
END {
	print lt, ltcnt;
}
