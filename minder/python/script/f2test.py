import string
import re


def split_len(seq, maxlength):
	return [seq[i:i+maxlength] for i in range(0, len(seq), maxlength)]

def split_word(seq, maxlength):
	s = string.split(seq)
	return s

def split_word2(seq, maxlength, memberno):
	print "split_word2"
	s = string.split(seq)
	print s
	j = 0
	k = 0
	buffer1 = ""
	out = []
	# j = s index
	# k = current length
	print "len s",len(s)
	while j < len(s):
		print "loop j start"
		print "j:", j
		if k == 0  and  len(s[j])  <= maxlength:
			print "k=0 and len s < max"
			buffer1 = buffer1 + s[j]
			k = k +  len(s[j])
			j = j + 1
			print "buffer1:", buffer1
			print "k:", k
			print "j:", j
		else:
			if (k + len(s[j]) + 1) <= maxlength:
				print "k>0 and len s < max"
				buffer1 = buffer1 + " " + s[j]
				k = k + 1 + len(s[j])
				j = j + 1
				print "buffer1:", buffer1
				print "k:", k
				print "j:", j
			else:
				print "wont fit"
				# wont fit on current line
				out.append( buffer1)
				buffer1 = ""	
				k = 0
				print "out:", out
				if (k + len(s[j]))  <= maxlength:
					print "k + len s < max"
					buffer1 = s[j]
					k = k + len(s[j])
					j = j + 1
					print "buffer1:", buffer1
					print "k:", k
					print "j:", j
				else:
					print "wont fit so split it"
					# the word is too long to fit on a line so split it
					buffer2 = s[j]
					s[j] = buffer2[maxlength:]
					buffer1 = buffer2[:maxlength]
					out.append( buffer1)
					buffer1 = ""	
					k = 0
					print "buffer1:", buffer1
					print "k:", k
					print "j:", j
					print "out:", out
	out.append(buffer1)
	if memberno < 0:
		return out
	else:
		if memberno < len(out):
			return out[memberno]
		else:
			return ""

atest = "asjksa dfkjdf gfkimk fkkl   34834 435i854 45984589 98 98 498 498 4984689568957y906509 0 465 04659046 90 099046904690459045  40490t649059045665490 4-04 4 4 4 4495490549065gfrkjfgkjgfrkjfgkjrkjkrtkjrt5954905490546905490659064905690569056906590    bvkfggflkgfklfgklfgklfgklfgklgflfgklfglkfgflg   logkl kiggm ll1234567890fjkfgkl lfkb rl li lkgb lb l lk l kl"
#atest = "asjksa dfkjdf" 
print atest
#print split_word(atest, 10)
#print split_len(atest, 10)
#print split_word2(atest, 10, -1)
#print split_word2(atest, 10,10)
#print split_word2(atest, 26, -1)
#print split_word2(atest, 26,0)
#print split_word2(atest, 26,10)
for i in  range(0,5) :
	print i, split_word2(atest, 26,i)
