import string
import re


def split_len(seq, maxlength):
	return [seq[i:i+maxlength] for i in range(0, len(seq), maxlength)]

def split_word(seq, maxlength):
	s = string.split(seq)
	return s

def split_word2(seq, maxlength, memberno):
	s = string.split(seq)
	j = 0
	k = 0
	buffer1 = ""
	out = []
	# j = s index
	# k = current length
	while j < len(s):
		if k == 0  and  len(s[j])  <= maxlength:
			buffer1 = buffer1 + s[j]
			k = k +  len(s[j])
			j = j + 1
		else:
			if (k + len(s[j]) + 1) <= maxlength:
				buffer1 = buffer1 + " " + s[j]
				k = k + 1 + len(s[j])
				j = j + 1
			else:
				# wont fit on current line
				out.append( buffer1)
				buffer1 = ""	
				k = 0
				if (k + len(s[j]))  <= maxlength:
					buffer1 = s[j]
					k = k + len(s[j])
					j = j + 1
				else:
					# the word is too long to fit on a line so split it
					buffer2 = s[j]
					s[j] = buffer2[maxlength:]
					buffer1 = buffer2[:maxlength]
					out.append( buffer1)
					buffer1 = ""	
					k = 0
	if memberno < 0:
		return out
	else:
		if memberno < len(out):
			return out[memberno]
		else:
			return ""

atest = "asjksa dfkjdf gfkimk fkkl  logkl kiggm ll1234567890fjkfgkl lfkb rl li lkgb lb l lk l kl"
print atest
#print split_word(atest, 10)
#print split_len(atest, 10)
print split_word2(atest, 10, -1)
print split_word2(atest, 10,10)
for i in  range(0,5) :
	print split_word2(atest, 10,i)
